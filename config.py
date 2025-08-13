#!/usr/bin/env python3
"""
Configuration file for Monad Faucet Bot
Modify these settings according to your needs
"""

from dataclasses import dataclass

@dataclass
class BotConfig:
    """Configuration class for the Monad Faucet Bot"""
    
    # API Configuration
    domain: str = "faucet-miniapp.monad.xyz"
    chain_id: int = 10
    
    # Bot Behavior
    max_attempts: int = 3  # Maximum attempts per account
    accounts_per_batch: int = 10  # Number of accounts to process simultaneously
    cycle_wait_hours: float = 0.5  # Wait time between cycles (in hours)
    
    # Network Settings
    request_timeout: int = 30  # Request timeout in seconds
    retry_delay: int = 2  # Delay between retries in seconds
    
    # File Paths
    proxy_file: str = "proxy.txt"  # File containing proxy list
    accounts_file: str = "data.json"  # File containing account data
    log_file: str = "faucet_bot.log"  # Log file path
    
    # Logging
    log_level: str = "INFO"  # Logging level (DEBUG, INFO, WARNING, ERROR)
    
    # Advanced Settings
    dashboard_refresh_rate: float = 0.5  # Dashboard refresh rate in seconds
    thread_delay: float = 0.1  # Delay between starting threads to avoid overwhelming

# Create default configuration instance
default_config = BotConfig()

# You can create custom configurations here
def get_custom_config():
    """Example of creating a custom configuration"""
    config = BotConfig()
    
    # Modify settings as needed
    config.max_attempts = 5
    config.accounts_per_batch = 5
    config.cycle_wait_hours = 1.0
    config.log_level = "DEBUG"
    
    return config