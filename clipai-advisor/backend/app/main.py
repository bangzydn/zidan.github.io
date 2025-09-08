"""
ClipAI Advisor - FastAPI Backend
Main application entry point untuk RAG chatbot agensi kliping
"""

from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from contextlib import asynccontextmanager
import uvicorn
from typing import List, Optional
import logging

from app.core.config import settings
from app.api.v1 import chat, auth, knowledge, users
from app.core.database import init_db
from app.services.rag_service import RAGService
from app.core.logging_config import setup_logging

# Setup logging
setup_logging()
logger = logging.getLogger(__name__)

# Global RAG service instance
rag_service: Optional[RAGService] = None

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan events"""
    global rag_service
    
    # Startup
    logger.info("Starting ClipAI Advisor backend...")
    
    # Initialize database
    await init_db()
    
    # Initialize RAG service
    rag_service = RAGService()
    await rag_service.initialize()
    
    # Store in app state
    app.state.rag_service = rag_service
    
    logger.info("Backend initialized successfully!")
    
    yield
    
    # Shutdown
    logger.info("Shutting down ClipAI Advisor backend...")
    if rag_service:
        await rag_service.cleanup()

# Create FastAPI app
app = FastAPI(
    title="ClipAI Advisor API",
    description="AI Assistant untuk Agensi Kliping - RAG Chatbot Backend",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    lifespan=lifespan
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allow_headers=["*"],
)

# Health check endpoint
@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "service": "ClipAI Advisor Backend",
        "version": "1.0.0"
    }

@app.get("/")
async def root():
    """Root endpoint with welcome message"""
    return {
        "message": "Welcome to ClipAI Advisor API! 🚀",
        "description": "AI Assistant untuk Agensi Kliping",
        "docs": "/docs",
        "version": "1.0.0"
    }

# Include API routers
app.include_router(
    chat.router,
    prefix="/api/v1/chat",
    tags=["chat"]
)

app.include_router(
    auth.router,
    prefix="/api/v1/auth",
    tags=["authentication"]
)

app.include_router(
    knowledge.router,
    prefix="/api/v1/knowledge",
    tags=["knowledge-base"]
)

app.include_router(
    users.router,
    prefix="/api/v1/users",
    tags=["users"]
)

# Error handlers
@app.exception_handler(404)
async def not_found_handler(request, exc):
    return {
        "error": "Endpoint not found",
        "message": "The requested endpoint does not exist",
        "status_code": 404
    }

@app.exception_handler(500)
async def internal_error_handler(request, exc):
    logger.error(f"Internal server error: {exc}")
    return {
        "error": "Internal server error",
        "message": "Something went wrong on our end",
        "status_code": 500
    }

if __name__ == "__main__":
    uvicorn.run(
        "app.main:app",
        host="0.0.0.0",
        port=8000,
        reload=True,
        log_level="info"
    )