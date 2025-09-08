"""
Logging configuration untuk ClipAI Advisor
"""

import logging
import sys
from typing import Dict, Any
from pathlib import Path

from app.core.config import settings

def setup_logging():
    """
    Setup logging configuration
    """
    # Create logs directory
    logs_dir = Path("logs")
    logs_dir.mkdir(exist_ok=True)
    
    # Configure root logger
    logging.basicConfig(
        level=getattr(logging, settings.LOG_LEVEL.upper()),
        format=settings.LOG_FORMAT,
        handlers=[
            # Console handler
            logging.StreamHandler(sys.stdout),
            # File handler
            logging.FileHandler(logs_dir / "clipai-advisor.log"),
        ]
    )
    
    # Configure specific loggers
    configure_logger("app", settings.LOG_LEVEL)
    configure_logger("uvicorn", "INFO")
    configure_logger("sqlalchemy", "WARNING")
    configure_logger("httpx", "WARNING")
    
    # Log startup message
    logger = logging.getLogger("app.startup")
    logger.info("Logging configured successfully")
    logger.info(f"Log level: {settings.LOG_LEVEL}")

def configure_logger(name: str, level: str):
    """
    Configure specific logger
    """
    logger = logging.getLogger(name)
    logger.setLevel(getattr(logging, level.upper()))

def get_logger(name: str) -> logging.Logger:
    """
    Get logger instance
    """
    return logging.getLogger(f"app.{name}")

class LoggerMixin:
    """
    Mixin untuk menambahkan logger ke class
    """
    @property
    def logger(self) -> logging.Logger:
        return get_logger(self.__class__.__name__.lower())