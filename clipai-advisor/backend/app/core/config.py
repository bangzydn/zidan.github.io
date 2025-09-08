"""
Configuration settings untuk ClipAI Advisor backend
Menggunakan Pydantic Settings untuk type-safe configuration
"""

from pydantic_settings import BaseSettings
from typing import List, Optional
import os
from pathlib import Path

class Settings(BaseSettings):
    """Application settings"""
    
    # App settings
    APP_NAME: str = "ClipAI Advisor"
    VERSION: str = "1.0.0"
    DEBUG: bool = False
    
    # API settings
    API_V1_STR: str = "/api/v1"
    SECRET_KEY: str = "your-super-secret-key-change-in-production"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60 * 24 * 8  # 8 days
    
    # CORS settings
    ALLOWED_ORIGINS: List[str] = [
        "http://localhost:3000",  # Next.js dev server
        "http://localhost:3001",
        "https://clipai-advisor.vercel.app"  # Production frontend
    ]
    
    # Database settings
    DATABASE_URL: Optional[str] = None
    POSTGRES_SERVER: str = "localhost"
    POSTGRES_USER: str = "postgres"
    POSTGRES_PASSWORD: str = "password"
    POSTGRES_DB: str = "clipai_advisor"
    POSTGRES_PORT: str = "5432"
    
    # Vector Database settings
    PINECONE_API_KEY: Optional[str] = None
    PINECONE_ENVIRONMENT: str = "us-west1-gcp-free"
    PINECONE_INDEX_NAME: str = "clipai-knowledge"
    
    # AI/LLM settings
    OPENAI_API_KEY: Optional[str] = None
    GROK_API_KEY: Optional[str] = None  # xAI Grok API
    HUGGINGFACE_API_KEY: Optional[str] = None
    
    # Default LLM provider: "openai", "grok", "huggingface"
    DEFAULT_LLM_PROVIDER: str = "openai"
    DEFAULT_MODEL: str = "gpt-4"
    
    # Embedding settings
    EMBEDDING_MODEL: str = "sentence-transformers/all-MiniLM-L6-v2"
    EMBEDDING_DIMENSION: int = 384
    
    # RAG settings
    MAX_CONTEXT_LENGTH: int = 4000
    TOP_K_RETRIEVAL: int = 5
    SIMILARITY_THRESHOLD: float = 0.7
    
    # File upload settings
    MAX_FILE_SIZE: int = 10 * 1024 * 1024  # 10MB
    ALLOWED_FILE_TYPES: List[str] = [".pdf", ".docx", ".txt", ".md"]
    UPLOAD_DIR: str = "uploads"
    
    # Knowledge base settings
    KNOWLEDGE_BASE_DIR: str = "knowledge-base"
    
    # Logging settings
    LOG_LEVEL: str = "INFO"
    LOG_FORMAT: str = "%(asctime)s - %(name)s - %(levelname)s - %(message)s"
    
    # Rate limiting
    RATE_LIMIT_PER_MINUTE: int = 60
    
    # Monitoring
    SENTRY_DSN: Optional[str] = None
    
    @property
    def database_url(self) -> str:
        """Get database URL"""
        if self.DATABASE_URL:
            return self.DATABASE_URL
        
        return (
            f"postgresql://{self.POSTGRES_USER}:{self.POSTGRES_PASSWORD}"
            f"@{self.POSTGRES_SERVER}:{self.POSTGRES_PORT}/{self.POSTGRES_DB}"
        )
    
    @property
    def knowledge_base_path(self) -> Path:
        """Get knowledge base directory path"""
        return Path(__file__).parent.parent.parent / self.KNOWLEDGE_BASE_DIR
    
    @property
    def upload_path(self) -> Path:
        """Get upload directory path"""
        upload_dir = Path(__file__).parent.parent.parent / self.UPLOAD_DIR
        upload_dir.mkdir(exist_ok=True)
        return upload_dir
    
    class Config:
        env_file = ".env"
        case_sensitive = True

# Create global settings instance
settings = Settings()

# Validate required settings
def validate_settings():
    """Validate critical settings"""
    errors = []
    
    if not settings.SECRET_KEY or settings.SECRET_KEY == "your-super-secret-key-change-in-production":
        errors.append("SECRET_KEY must be set to a secure value")
    
    if not settings.OPENAI_API_KEY and not settings.GROK_API_KEY:
        errors.append("At least one AI API key (OPENAI_API_KEY or GROK_API_KEY) must be set")
    
    if settings.DEFAULT_LLM_PROVIDER == "pinecone" and not settings.PINECONE_API_KEY:
        errors.append("PINECONE_API_KEY is required when using Pinecone")
    
    if errors:
        raise ValueError(f"Configuration errors: {'; '.join(errors)}")

# Auto-validate on import (only in production)
if not settings.DEBUG:
    try:
        validate_settings()
    except ValueError as e:
        print(f"⚠️  Configuration warning: {e}")
        print("Some features may not work properly. Please check your .env file.")