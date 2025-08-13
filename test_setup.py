#!/usr/bin/env python3
"""
Test script to validate Monad Faucet Bot setup
Run this before using the main bot to ensure everything is configured correctly
"""

import json
import os
from pathlib import Path
from eth_account import Account
import requests

def test_dependencies():
    """Test if all required dependencies are installed"""
    print("🔍 Testing dependencies...")
    
    try:
        import requests
        print("✅ requests library - OK")
    except ImportError:
        print("❌ requests library - MISSING")
        return False
        
    try:
        from eth_account import Account
        print("✅ eth-account library - OK")
    except ImportError:
        print("❌ eth-account library - MISSING")
        return False
        
    try:
        from eth_account.messages import encode_defunct
        print("✅ eth-account.messages - OK")
    except ImportError:
        print("❌ eth-account.messages - MISSING")
        return False
        
    return True

def test_files():
    """Test if required files exist"""
    print("\n📁 Testing required files...")
    
    files_to_check = [
        ("data.json", "Account data file"),
        ("proxy.txt", "Proxy list file"),
        ("monad_faucet_bot.py", "Main bot script"),
        ("config.py", "Configuration file")
    ]
    
    all_exist = True
    for filename, description in files_to_check:
        if Path(filename).exists():
            print(f"✅ {filename} - {description} - FOUND")
        else:
            print(f"❌ {filename} - {description} - MISSING")
            all_exist = False
            
    return all_exist

def test_data_json():
    """Test if data.json has valid format"""
    print("\n📋 Testing data.json format...")
    
    try:
        with open("data.json", "r") as f:
            data = json.load(f)
            
        if not isinstance(data, list):
            print("❌ data.json should contain a list of accounts")
            return False
            
        if len(data) == 0:
            print("❌ data.json is empty - add at least one account")
            return False
            
        print(f"✅ Found {len(data)} accounts in data.json")
        
        # Validate each account
        for i, account in enumerate(data):
            print(f"  Checking account {i+1}...")
            
            # Check required fields
            required_fields = ["wallet_address", "fid", "private_key"]
            for field in required_fields:
                if field not in account:
                    print(f"    ❌ Missing required field: {field}")
                    return False
                    
            # Validate wallet address format
            wallet = account["wallet_address"]
            if not wallet.startswith("0x") or len(wallet) != 42:
                print(f"    ❌ Invalid wallet address format: {wallet}")
                return False
                
            # Validate FID
            try:
                fid = int(account["fid"])
                if fid <= 0:
                    print(f"    ❌ Invalid FID (must be positive): {fid}")
                    return False
            except ValueError:
                print(f"    ❌ Invalid FID format: {account['fid']}")
                return False
                
            # Validate private key
            try:
                pk = account["private_key"]
                if not pk.startswith("0x"):
                    pk = "0x" + pk
                    
                # Test if private key is valid
                account_obj = Account.from_key(pk)
                derived_address = account_obj.address
                
                if derived_address.lower() != wallet.lower():
                    print(f"    ❌ Private key doesn't match wallet address")
                    print(f"      Expected: {wallet}")
                    print(f"      Derived:  {derived_address}")
                    return False
                    
                print(f"    ✅ Account {i+1} - Valid")
                
            except Exception as e:
                print(f"    ❌ Invalid private key: {e}")
                return False
                
        return True
        
    except json.JSONDecodeError as e:
        print(f"❌ Invalid JSON format in data.json: {e}")
        return False
    except Exception as e:
        print(f"❌ Error reading data.json: {e}")
        return False

def test_proxy_txt():
    """Test if proxy.txt has valid format"""
    print("\n🌐 Testing proxy.txt format...")
    
    try:
        with open("proxy.txt", "r") as f:
            lines = f.readlines()
            
        proxies = []
        for i, line in enumerate(lines, 1):
            line = line.strip()
            if not line or line.startswith("#"):
                continue
                
            # Basic proxy format validation
            if ":" not in line:
                print(f"  ❌ Invalid proxy format at line {i}: {line}")
                return False
                
            proxies.append(line)
            
        if len(proxies) == 0:
            print("❌ No valid proxies found in proxy.txt")
            return False
            
        print(f"✅ Found {len(proxies)} proxies in proxy.txt")
        return True
        
    except Exception as e:
        print(f"❌ Error reading proxy.txt: {e}")
        return False

def test_network_connectivity():
    """Test basic network connectivity"""
    print("\n🌍 Testing network connectivity...")
    
    try:
        # Test basic internet connectivity
        response = requests.get("https://httpbin.org/ip", timeout=10)
        if response.status_code == 200:
            print("✅ Basic internet connectivity - OK")
        else:
            print("❌ Basic internet connectivity - FAILED")
            return False
            
        # Test Monad faucet domain
        response = requests.get("https://faucet-miniapp.monad.xyz", timeout=10)
        if response.status_code == 200:
            print("✅ Monad faucet domain accessible - OK")
        else:
            print(f"⚠️  Monad faucet domain returned status {response.status_code}")
            
        return True
        
    except requests.exceptions.RequestException as e:
        print(f"❌ Network connectivity test failed: {e}")
        return False

def main():
    """Run all tests"""
    print("🧪 MONAD FAUCET BOT - SETUP VALIDATION")
    print("=" * 50)
    
    tests = [
        ("Dependencies", test_dependencies),
        ("Required Files", test_files),
        ("Data JSON Format", test_data_json),
        ("Proxy List Format", test_proxy_txt),
        ("Network Connectivity", test_network_connectivity)
    ]
    
    results = []
    for test_name, test_func in tests:
        try:
            result = test_func()
            results.append((test_name, result))
        except Exception as e:
            print(f"❌ {test_name} test failed with exception: {e}")
            results.append((test_name, False))
    
    # Summary
    print("\n" + "=" * 50)
    print("📊 TEST SUMMARY")
    print("=" * 50)
    
    passed = 0
    total = len(results)
    
    for test_name, result in results:
        status = "✅ PASS" if result else "❌ FAIL"
        print(f"{test_name}: {status}")
        if result:
            passed += 1
    
    print(f"\nResults: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n🎉 All tests passed! Your bot is ready to run.")
        print("Run: python monad_faucet_bot.py")
    else:
        print("\n⚠️  Some tests failed. Please fix the issues before running the bot.")
        print("Check the error messages above for details.")

if __name__ == "__main__":
    main()