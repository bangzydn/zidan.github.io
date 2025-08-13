#!/usr/bin/env python3
"""
Monad Faucet Bot - Automated faucet claiming for Monad blockchain
Improved version with better error handling, logging, and configuration management
"""

import requests
import time
import random
import threading
import logging
from eth_account import Account
from eth_account.messages import encode_defunct
from datetime import datetime, timezone
import os
import json
import sys
from typing import Dict, List, Optional, Tuple, Any
from dataclasses import dataclass
from pathlib import Path

# Configuration
@dataclass
class Config:
    """Configuration class for the bot"""
    domain: str = "faucet-miniapp.monad.xyz"
    chain_id: int = 10
    max_attempts: int = 3
    accounts_per_batch: int = 10
    cycle_wait_hours: float = 0.5
    request_timeout: int = 30
    retry_delay: int = 2
    proxy_file: str = "proxy.txt"
    accounts_file: str = "data.json"
    log_file: str = "faucet_bot.log"
    log_level: str = "INFO"

class MonadFaucetBot:
    """Main bot class for Monad faucet claiming"""
    
    def __init__(self, config: Config):
        self.config = config
        self.base_url = f"https://{config.domain}"
        self.endpoints = {
            'get_nonce': f"{self.base_url}/api/auth",
            'post_auth': f"{self.base_url}/api/auth", 
            'post_claim': f"{self.base_url}/api/claim"
        }
        
        # Threading and status management
        self.dashboard_status: Dict[str, str] = {}
        self.dashboard_lock = threading.Lock()
        self.stop_render_thread = threading.Event()
        
        # Setup logging
        self._setup_logging()
        
        # Statistics
        self.stats = {
            'total_accounts': 0,
            'successful_claims': 0,
            'already_claimed': 0,
            'failed_attempts': 0,
            'proxy_errors': 0
        }
        
    def _setup_logging(self):
        """Setup logging configuration"""
        logging.basicConfig(
            level=getattr(logging, self.config.log_level),
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(self.config.log_file),
                logging.StreamHandler(sys.stdout)
            ]
        )
        self.logger = logging.getLogger(__name__)
        
    def clear_console(self):
        """Clear console screen"""
        os.system('cls' if os.name == 'nt' else 'clear')
        
    def update_dashboard(self, wallet_address: str, status_message: str):
        """Update dashboard status for a wallet"""
        with self.dashboard_lock:
            self.dashboard_status[wallet_address] = status_message
            
    def render_dashboard(self, countdown_msg: str = ""):
        """Render the real-time dashboard"""
        with self.dashboard_lock:
            self.clear_console()
            print("="*60)
            print("🚀 MONAD FAUCET BOT - REAL-TIME DASHBOARD 🚀")
            print("="*60)
            
            # Statistics
            print(f"\n📊 Statistics:")
            print(f"  Total Accounts: {self.stats['total_accounts']}")
            print(f"  Successful Claims: {self.stats['successful_claims']}")
            print(f"  Already Claimed: {self.stats['already_claimed']}")
            print(f"  Failed Attempts: {self.stats['failed_attempts']}")
            print(f"  Proxy Errors: {self.stats['proxy_errors']}")
            
            print(f"\n📦 Current Batch Status:")
            for wallet, status in self.dashboard_status.items():
                if wallet == "__countdown__":
                    continue
                print(f"  {wallet[:10]}...: {status}")
                
            if "__countdown__" in self.dashboard_status:
                print(f"\n{self.dashboard_status['__countdown__']}")
            elif countdown_msg:
                print(f"\n{countdown_msg}")
            else:
                print("\n🔄 Processing batch...")
                
            print("\n" + "="*60)
            sys.stdout.flush()
            
    def dashboard_renderer_loop(self):
        """Dashboard renderer thread loop"""
        while not self.stop_render_thread.is_set():
            self.render_dashboard()
            time.sleep(0.5)
            
    def countdown_timer(self, duration_hours: float):
        """Display countdown timer"""
        duration_seconds = int(duration_hours * 3600)
        for i in range(duration_seconds, 0, -1):
            if self.stop_render_thread.is_set():
                break
            hours, remainder = divmod(i, 3600)
            minutes, seconds = divmod(remainder, 60)
            countdown_msg = f"⏳ Next cycle in: {int(hours):02}:{int(minutes):02}:{int(seconds):02}..."
            with self.dashboard_lock:
                self.dashboard_status["__countdown__"] = countdown_msg
            time.sleep(1)
        with self.dashboard_lock:
            if "__countdown__" in self.dashboard_status:
                del self.dashboard_status["__countdown__"]
                
    def parse_proxy_line(self, line: str) -> Optional[Dict[str, str]]:
        """Parse a proxy line from file"""
        line = line.strip()
        if not line:
            return None
        if not line.startswith("http://") and not line.startswith("https://"):
            line = "http://" + line
        return {"http": line, "https": line}
        
    def load_proxies(self) -> List[Dict[str, str]]:
        """Load proxies from file"""
        proxy_file = Path(self.config.proxy_file)
        if not proxy_file.exists():
            self.logger.error(f"❌ Proxy file '{self.config.proxy_file}' not found.")
            return []
            
        try:
            with open(proxy_file, "r") as f:
                proxies = []
                for line_num, line in enumerate(f, 1):
                    parsed = self.parse_proxy_line(line)
                    if parsed:
                        proxies.append(parsed)
                    else:
                        self.logger.warning(f"Invalid proxy format at line {line_num}: {line.strip()}")
                        
            self.logger.info(f"✅ Loaded {len(proxies)} proxies from {self.config.proxy_file}")
            return proxies
        except Exception as e:
            self.logger.error(f"❌ Error loading proxies: {e}")
            return []
            
    def load_accounts_from_json(self) -> List[Dict[str, str]]:
        """Load accounts from JSON file"""
        accounts_file = Path(self.config.accounts_file)
        if not accounts_file.exists():
            self.logger.error(f"❌ Accounts file '{self.config.accounts_file}' not found.")
            return []
            
        try:
            with open(accounts_file, "r") as f:
                accounts = json.load(f)
                
            # Validate account format
            valid_accounts = []
            for i, acc in enumerate(accounts):
                if all(key in acc for key in ["wallet_address", "fid", "private_key"]):
                    valid_accounts.append(acc)
                else:
                    self.logger.warning(f"Invalid account format at index {i}: missing required fields")
                    
            self.logger.info(f"✅ Loaded {len(valid_accounts)} valid accounts from {self.config.accounts_file}")
            return valid_accounts
        except json.JSONDecodeError as e:
            self.logger.error(f"❌ Invalid JSON format in accounts file: {e}")
            return []
        except Exception as e:
            self.logger.error(f"❌ Error loading accounts: {e}")
            return []
            
    def fetch_nonce(self, fid: str, proxy: Dict[str, str], wallet_address: str) -> Optional[str]:
        """Fetch nonce for authentication"""
        self.update_dashboard(wallet_address, f"🔑 Requesting nonce for FID: {fid}...")
        
        try:
            response = requests.get(
                f"{self.endpoints['get_nonce']}?fid={fid}",
                proxies=proxy,
                timeout=self.config.request_timeout
            )
            
            if response.status_code != 200:
                self.update_dashboard(wallet_address, f"❌ Failed to get nonce: status {response.status_code}")
                self.logger.warning(f"Nonce request failed for {wallet_address}: {response.status_code}")
                return None
                
            try:
                data = response.json()
                nonce = data.get("nonce")
            except json.JSONDecodeError:
                self.update_dashboard(wallet_address, f"❌ Nonce response not JSON: {response.text[:100]}")
                self.logger.error(f"Invalid JSON response for nonce: {response.text}")
                return None
                
            if nonce:
                self.update_dashboard(wallet_address, "✅ Nonce received")
                return nonce
            else:
                self.update_dashboard(wallet_address, f"❌ No nonce in response: {response.text}")
                return None
                
        except requests.exceptions.RequestException as e:
            self.update_dashboard(wallet_address, f"❌ Proxy error during nonce request: {e}")
            self.stats['proxy_errors'] += 1
            return "proxy_error"
            
    def build_siwe_message(self, wallet: str, fid: str, nonce: str, issued_at: str) -> str:
        """Build SIWE (Sign-In with Ethereum) message"""
        return (
            f"{self.config.domain} wants you to sign in with your Ethereum account:\n"
            f"{wallet}\n\n"
            f"Farcaster Auth\n\n"
            f"URI: https://{self.config.domain}/\n"
            f"Version: 1\n"
            f"Chain ID: {self.config.chain_id}\n"
            f"Nonce: {nonce}\n"
            f"Issued At: {issued_at}\n"
            f"Resources:\n"
            f"- farcaster://fid/{fid}"
        )
        
    def sign_message(self, message: str, private_key: str) -> str:
        """Sign message with private key"""
        try:
            encoded = encode_defunct(text=message)
            signed = Account.sign_message(encoded, private_key)
            return "0x" + signed.signature.hex()
        except Exception as e:
            self.logger.error(f"Error signing message: {e}")
            raise
            
    def authenticate(self, wallet_address: str, fid: str, private_key: str, proxy: Dict[str, str]) -> Optional[str]:
        """Authenticate with the faucet service"""
        nonce = self.fetch_nonce(fid, proxy, wallet_address)
        if nonce == "proxy_error":
            return "proxy_error"
        if not nonce:
            return None
            
        self.update_dashboard(wallet_address, "✍️ Signing authentication message...")
        
        try:
            issued_at = datetime.now(timezone.utc).isoformat(timespec="milliseconds").replace("+00:00", "Z")
            message = self.build_siwe_message(wallet_address, fid, nonce, issued_at)
            signature = self.sign_message(message, private_key)
            
            payload = {
                "message": message,
                "signature": signature,
                "nonce": nonce,
                "fid": fid
            }
            
            headers = {"Content-Type": "application/json"}
            
            self.update_dashboard(wallet_address, "🔐 Sending authentication request...")
            
            response = requests.post(
                self.endpoints['post_auth'],
                headers=headers,
                json=payload,
                proxies=proxy,
                timeout=self.config.request_timeout
            )
            
            if response.status_code != 200:
                self.update_dashboard(wallet_address, f"❌ Authentication failed: status {response.status_code}")
                self.logger.warning(f"Authentication failed for {wallet_address}: {response.status_code}")
                return None
                
            try:
                data = response.json()
                token = data.get("token")
            except json.JSONDecodeError:
                self.update_dashboard(wallet_address, f"❌ Auth response not JSON: {response.text[:100]}")
                return None
                
            if token:
                self.update_dashboard(wallet_address, "✅ Authentication successful, token received")
                return token
            else:
                self.update_dashboard(wallet_address, f"❌ No token in response: {response.text}")
                return None
                
        except requests.exceptions.RequestException as e:
            self.update_dashboard(wallet_address, f"❌ Proxy error during authentication: {e}")
            self.stats['proxy_errors'] += 1
            return "proxy_error"
            
    def claim_faucet(self, token: str, wallet_address: str, proxy: Dict[str, str]) -> Tuple[str, Any]:
        """Claim from faucet"""
        payload = {"address": wallet_address}
        headers = {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json"
        }
        
        try:
            self.update_dashboard(wallet_address, "💰 Sending faucet claim request...")
            
            response = requests.post(
                self.endpoints['post_claim'],
                headers=headers,
                json=payload,
                proxies=proxy,
                timeout=self.config.request_timeout
            )
            
            try:
                result = response.json()
            except json.JSONDecodeError:
                return "api_error", f"❌ Claim response not JSON: {response.text[:100]}"
                
            # Check for successful claim
            if "txHash" in result:
                self.stats['successful_claims'] += 1
                return "claimed", result
                
            # Check for already claimed
            err_msg = str(result.get("error", "")).lower()
            msg_msg = str(result.get("message", "")).lower()
            
            if any(keyword in err_msg or keyword in msg_msg for keyword in ["already", "sent"]):
                self.stats['already_claimed'] += 1
                return "already_claimed", result
                
            if result.get("success") is True:
                self.stats['already_claimed'] += 1
                return "already_claimed", result
                
            return "api_error", result
            
        except requests.exceptions.RequestException as e:
            self.stats['proxy_errors'] += 1
            return "proxy_error", str(e)
            
    def validate_account(self, account: Dict[str, str]) -> bool:
        """Validate account format and private key"""
        try:
            wallet = account["wallet_address"]
            private_key = account["private_key"]
            
            signer = Account.from_key(private_key)
            if signer.address.lower() != wallet.lower():
                self.logger.error(f"❌ Private key doesn't match wallet address for {wallet}")
                return False
            return True
        except Exception as e:
            self.logger.error(f"❌ Invalid private key format for {account.get('wallet_address', 'unknown')}: {e}")
            return False
            
    def run_account(self, account: Dict[str, str], proxies: List[Dict[str, str]]):
        """Run faucet claiming for a single account"""
        wallet = account["wallet_address"]
        fid = account["fid"]
        private_key = account["private_key"]
        
        self.update_dashboard(wallet, "🚀 Starting process...")
        
        # Validate account
        if not self.validate_account(account):
            self.update_dashboard(wallet, "❌ Invalid account format or private key. Skipping.")
            return
            
        for attempt in range(1, self.config.max_attempts + 1):
            self.update_dashboard(wallet, f"🔄 Attempt {attempt}/{self.config.max_attempts}...")
            proxy = random.choice(proxies)
            
            # Authenticate
            token = self.authenticate(wallet, fid, private_key, proxy)
            if token == "proxy_error":
                self.update_dashboard(wallet, f"❌ Proxy error during authentication. Trying different proxy...")
                time.sleep(self.config.retry_delay)
                continue
            if not token:
                time.sleep(self.config.retry_delay)
                continue
                
            # Claim faucet
            result, response = self.claim_faucet(token, wallet, proxy)
            
            if result == "proxy_error":
                self.update_dashboard(wallet, f"❌ Proxy error during claim: {response}. Trying different proxy...")
                time.sleep(self.config.retry_delay)
                continue
            elif result == "api_error":
                self.update_dashboard(wallet, f"❌ Claim failed, API response: {response}")
                self.stats['failed_attempts'] += 1
                time.sleep(5)
                continue
            elif result in ["claimed", "already_claimed"]:
                status = "✅ Successfully claimed!" if result == "claimed" else "✅ Already claimed"
                self.update_dashboard(wallet, f"{status}\nResponse: {response}")
                return
                
        self.update_dashboard(wallet, f"❌ Failed after {self.config.max_attempts} attempts.")
        self.stats['failed_attempts'] += 1
        
    def main_loop(self):
        """Main bot loop"""
        render_thread = None
        
        try:
            while True:
                # Reset status and stats
                self.dashboard_status = {}
                self.stats = {
                    'total_accounts': 0,
                    'successful_claims': 0,
                    'already_claimed': 0,
                    'failed_attempts': 0,
                    'proxy_errors': 0
                }
                
                # Load accounts and proxies
                all_accounts = self.load_accounts_from_json()
                proxies = self.load_proxies()
                
                if not all_accounts or not proxies:
                    self.clear_console()
                    self.logger.error("❌ No accounts or proxies loaded. Bot stopping.")
                    break
                    
                self.stats['total_accounts'] = len(all_accounts)
                
                self.clear_console()
                print("="*60)
                print(f"ℹ️  Found {len(all_accounts)} accounts and {len(proxies)} proxies.")
                print("="*60)
                time.sleep(2)
                
                # Start dashboard renderer
                self.stop_render_thread.clear()
                render_thread = threading.Thread(target=self.dashboard_renderer_loop)
                render_thread.daemon = True
                render_thread.start()
                
                # Process accounts in batches
                batch_num = 1
                total = len(all_accounts)
                
                for i in range(0, total, self.config.accounts_per_batch):
                    batch = all_accounts[i:i + self.config.accounts_per_batch]
                    
                    with self.dashboard_lock:
                        self.dashboard_status = {acc["wallet_address"]: "⏳ Waiting..." for acc in batch}
                        if "__countdown__" in self.dashboard_status:
                            del self.dashboard_status["__countdown__"]
                            
                    # Process batch in parallel
                    threads = []
                    for acc in batch:
                        t = threading.Thread(target=self.run_account, args=(acc, proxies))
                        t.start()
                        threads.append(t)
                        time.sleep(0.1)  # Small delay to avoid overwhelming
                        
                    # Wait for all threads to complete
                    for t in threads:
                        t.join()
                        
                    self.render_dashboard()
                    time.sleep(3)
                    batch_num += 1
                    
                # Wait before next cycle
                self.countdown_timer(self.config.cycle_wait_hours)
                
        except KeyboardInterrupt:
            self.clear_console()
            print("\n\n🛑 Program stopped by user (Ctrl+C). Goodbye!")
            self.logger.info("Bot stopped by user")
        finally:
            self.stop_render_thread.set()
            if render_thread and render_thread.is_alive():
                render_thread.join(timeout=1)

def main():
    """Main entry point"""
    # Load configuration
    config = Config()
    
    # Create and run bot
    bot = MonadFaucetBot(config)
    bot.main_loop()

if __name__ == "__main__":
    main()