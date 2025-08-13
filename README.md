# Monad Faucet Bot

An automated bot for claiming tokens from the Monad blockchain faucet. This bot supports multiple accounts, proxy rotation, and provides real-time monitoring with a beautiful dashboard.

## Features

- 🚀 **Multi-account support** - Process multiple accounts simultaneously
- 🔄 **Proxy rotation** - Automatic proxy switching for better success rates
- 📊 **Real-time dashboard** - Beautiful console-based monitoring
- 📝 **Comprehensive logging** - Detailed logs for debugging and monitoring
- 🔒 **Account validation** - Validates private keys and wallet addresses
- ⚡ **Parallel processing** - Batch processing for faster execution
- 🛡️ **Error handling** - Robust error handling and retry mechanisms
- 📈 **Statistics tracking** - Track successful claims, failures, and errors

## Installation

1. **Clone or download the bot files**
2. **Install Python dependencies:**
   ```bash
   pip install -r requirements.txt
   ```

## Configuration

### 1. Account Data (`data.json`)

Create a `data.json` file with your account information:

```json
[
  {
    "wallet_address": "0x1234567890123456789012345678901234567890",
    "fid": "12345",
    "private_key": "0xabcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890"
  }
]
```

**Required fields:**
- `wallet_address`: Your Ethereum wallet address
- `fid`: Your Farcaster ID
- `private_key`: Your wallet's private key (with or without 0x prefix)

### 2. Proxy List (`proxy.txt`)

Create a `proxy.txt` file with your proxy servers:

```
http://proxy1.example.com:8080
http://proxy2.example.com:8080
192.168.1.100:8080
```

**Format:**
- One proxy per line
- Can include `http://` or `https://` prefix
- Can be just `IP:PORT` format

### 3. Bot Configuration

Modify `config.py` to customize bot behavior:

```python
@dataclass
class BotConfig:
    max_attempts: int = 3  # Maximum attempts per account
    accounts_per_batch: int = 10  # Accounts processed simultaneously
    cycle_wait_hours: float = 0.5  # Wait time between cycles
    request_timeout: int = 30  # Request timeout in seconds
```

## Usage

### Basic Usage

```bash
python monad_faucet_bot.py
```

### Advanced Usage

```python
from monad_faucet_bot import MonadFaucetBot
from config import get_custom_config

# Use custom configuration
config = get_custom_config()
bot = MonadFaucetBot(config)
bot.main_loop()
```

## Dashboard

The bot provides a real-time dashboard showing:

- 📊 **Statistics**: Total accounts, successful claims, failures, proxy errors
- 📦 **Batch Status**: Current status of each account being processed
- ⏳ **Countdown Timer**: Time until next cycle
- 🔄 **Real-time Updates**: Live status updates for each wallet

## Logging

The bot creates detailed logs in `faucet_bot.log` including:

- Account processing status
- API responses and errors
- Proxy connection issues
- Authentication results
- Claim attempts and results

## Error Handling

The bot handles various error scenarios:

- **Proxy Errors**: Automatically switches to different proxies
- **API Errors**: Retries with exponential backoff
- **Network Timeouts**: Configurable timeout and retry settings
- **Invalid Accounts**: Validates private keys and skips invalid accounts
- **Rate Limiting**: Built-in delays to avoid overwhelming the API

## Troubleshooting

### Common Issues

1. **"No accounts or proxies loaded"**
   - Check that `data.json` and `proxy.txt` files exist
   - Verify file formats are correct

2. **"Private key doesn't match wallet address"**
   - Ensure private key corresponds to the wallet address
   - Check for extra spaces or formatting issues

3. **"Proxy error" messages**
   - Verify proxy servers are working
   - Check proxy format in `proxy.txt`
   - Try different proxy servers

4. **"Authentication failed"**
   - Verify FID is correct
   - Check if account has been used before
   - Ensure private key is valid

### Performance Tips

- Use high-quality, fast proxy servers
- Adjust `accounts_per_batch` based on your system capabilities
- Monitor log files for performance issues
- Use appropriate `cycle_wait_hours` to avoid rate limiting

## Safety and Security

⚠️ **Important Security Notes:**

1. **Never share your private keys** - Keep them secure and private
2. **Use dedicated proxies** - Don't use shared/public proxies for sensitive operations
3. **Monitor your accounts** - Regularly check your wallet balances
4. **Follow terms of service** - Ensure you comply with Monad's faucet terms

## File Structure

```
monad-faucet-bot/
├── monad_faucet_bot.py    # Main bot script
├── config.py              # Configuration file
├── requirements.txt       # Python dependencies
├── data.json             # Account data (create this)
├── proxy.txt             # Proxy list (create this)
├── sample_data.json      # Sample account format
├── sample_proxy.txt      # Sample proxy format
├── README.md             # This file
└── faucet_bot.log        # Log file (created automatically)
```

## Contributing

Feel free to submit issues, feature requests, or pull requests to improve the bot.

## Disclaimer

This bot is provided for educational and personal use only. Users are responsible for:
- Complying with Monad's terms of service
- Managing their own private keys and accounts
- Using the bot responsibly and ethically
- Understanding the risks involved in automated cryptocurrency operations

## License

This project is open source and available under the MIT License.