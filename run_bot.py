#!/usr/bin/env python3
"""
Monad Faucet Bot Launcher
Simple script to run the bot with different configurations
"""

import sys
import os
from pathlib import Path

def check_setup():
    """Check if the bot is properly set up"""
    required_files = ["monad_faucet_bot.py", "config.py"]
    config_files = ["data.json", "proxy.txt"]
    
    missing_required = []
    missing_config = []
    
    for file in required_files:
        if not Path(file).exists():
            missing_required.append(file)
            
    for file in config_files:
        if not Path(file).exists():
            missing_config.append(file)
    
    if missing_required:
        print("❌ Missing required files:")
        for file in missing_required:
            print(f"   - {file}")
        return False
    
    if missing_config:
        print("⚠️  Missing configuration files:")
        for file in missing_config:
            print(f"   - {file}")
        print("\nPlease create these files before running the bot.")
        print("See README.md for instructions.")
        return False
    
    return True

def run_test():
    """Run the test script"""
    if Path("test_setup.py").exists():
        print("🧪 Running setup validation...")
        os.system(f"{sys.executable} test_setup.py")
    else:
        print("❌ test_setup.py not found")

def run_bot():
    """Run the main bot"""
    print("🚀 Starting Monad Faucet Bot...")
    os.system(f"{sys.executable} monad_faucet_bot.py")

def show_help():
    """Show help information"""
    print("""
🚀 MONAD FAUCET BOT LAUNCHER

Usage:
  python run_bot.py [option]

Options:
  run     - Run the bot (default)
  test    - Run setup validation
  help    - Show this help message

Examples:
  python run_bot.py        # Run the bot
  python run_bot.py run    # Run the bot
  python run_bot.py test   # Run setup validation
  python run_bot.py help   # Show help

Before running the bot:
1. Install dependencies: pip install -r requirements.txt
2. Create data.json with your account information
3. Create proxy.txt with your proxy list
4. Run: python run_bot.py test (to validate setup)
5. Run: python run_bot.py (to start the bot)

For more information, see README.md
""")

def main():
    """Main launcher function"""
    print("🚀 MONAD FAUCET BOT LAUNCHER")
    print("=" * 40)
    
    # Check command line arguments
    if len(sys.argv) > 1:
        command = sys.argv[1].lower()
    else:
        command = "run"
    
    # Handle commands
    if command in ["help", "-h", "--help"]:
        show_help()
        return
    
    elif command == "test":
        if check_setup():
            run_test()
        return
    
    elif command == "run":
        if not check_setup():
            print("\n❌ Setup check failed. Please fix the issues above.")
            return
        
        # Ask user if they want to run tests first
        print("\nWould you like to run setup validation first? (y/n): ", end="")
        try:
            response = input().lower().strip()
            if response in ["y", "yes"]:
                run_test()
                print("\nPress Enter to continue to bot launch, or Ctrl+C to exit...")
                input()
        except KeyboardInterrupt:
            print("\n\n🛑 Cancelled by user.")
            return
        
        run_bot()
        return
    
    else:
        print(f"❌ Unknown command: {command}")
        show_help()
        return

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n🛑 Launcher stopped by user.")
    except Exception as e:
        print(f"\n❌ Error: {e}")
        print("Please check your setup and try again.")