"""
Pydantic schemas untuk chat functionality
"""

from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
from datetime import datetime
from enum import Enum

class MessageRole(str, Enum):
    """Message role enum"""
    USER = "user"
    ASSISTANT = "assistant"
    SYSTEM = "system"

class ChatMessage(BaseModel):
    """Single chat message"""
    role: MessageRole
    content: str = Field(..., min_length=1, max_length=4000)
    timestamp: Optional[datetime] = None
    metadata: Optional[Dict[str, Any]] = None
    
    class Config:
        use_enum_values = True

class ChatRequest(BaseModel):
    """Request untuk chat endpoint"""
    message: str = Field(..., min_length=1, max_length=4000, description="User message/question")
    conversation_id: Optional[str] = Field(None, description="Optional conversation ID untuk continuity")
    user_context: Optional[Dict[str, Any]] = Field(None, description="Additional user context")
    
    class Config:
        schema_extra = {
            "example": {
                "message": "Bagaimana cara memulai agensi kliping dari nol?",
                "conversation_id": "conv_123",
                "user_context": {
                    "experience_level": "beginner",
                    "focus_area": "tiktok_reels"
                }
            }
        }

class SourceDocument(BaseModel):
    """Source document reference"""
    title: str = Field(..., description="Document title/name")
    type: str = Field(..., description="Document type (pdf, md, txt)")
    snippet: str = Field(..., description="Relevant snippet from document")
    relevance_score: Optional[float] = Field(None, description="Relevance score 0-1")

class ChatResponse(BaseModel):
    """Response dari chat endpoint"""
    message: str = Field(..., description="AI generated response")
    sources: List[SourceDocument] = Field(default_factory=list, description="Source documents used")
    confidence: float = Field(default=0.0, ge=0.0, le=1.0, description="Confidence score")
    suggestions: List[str] = Field(default_factory=list, description="Follow-up suggestions")
    conversation_id: Optional[str] = Field(None, description="Conversation ID")
    timestamp: datetime = Field(default_factory=datetime.now)
    
    class Config:
        schema_extra = {
            "example": {
                "message": "Untuk memulai agensi kliping dari nol, ikuti langkah-langkah berikut:\n\n1. **Kuasai Mentalitas The Everything** - Pelajari semua aspek bisnis kliping...",
                "sources": [
                    {
                        "title": "Mentalitas_The_Everything.pdf",
                        "type": "pdf",
                        "snippet": "The Everything mindset adalah fondasi untuk membangun agensi yang sukses...",
                        "relevance_score": 0.95
                    }
                ],
                "confidence": 0.9,
                "suggestions": [
                    "Bagaimana cara menentukan niche kliping yang profitable?",
                    "Tools editing terbaik untuk pemula?",
                    "Strategi pricing untuk jasa kliping?"
                ],
                "conversation_id": "conv_123",
                "timestamp": "2025-01-11T10:30:00Z"
            }
        }

class ConversationHistory(BaseModel):
    """Conversation history"""
    conversation_id: str
    messages: List[ChatMessage]
    created_at: datetime
    updated_at: datetime
    user_id: Optional[str] = None
    title: Optional[str] = None  # Auto-generated from first message
    
class ConversationSummary(BaseModel):
    """Summary of conversation for listing"""
    conversation_id: str
    title: str
    last_message: str
    message_count: int
    created_at: datetime
    updated_at: datetime

class ChatMetrics(BaseModel):
    """Chat metrics for analytics"""
    total_conversations: int
    total_messages: int
    average_confidence: float
    top_topics: List[Dict[str, Any]]
    user_satisfaction: Optional[float] = None

class FeedbackRequest(BaseModel):
    """User feedback on chat response"""
    conversation_id: str
    message_id: Optional[str] = None
    rating: int = Field(..., ge=1, le=5, description="Rating 1-5")
    feedback: Optional[str] = Field(None, max_length=1000)
    helpful: bool = Field(..., description="Was the response helpful?")
    
class StreamChatRequest(BaseModel):
    """Request for streaming chat"""
    message: str = Field(..., min_length=1, max_length=4000)
    conversation_id: Optional[str] = None
    stream: bool = Field(True, description="Enable streaming response")

class StreamChatChunk(BaseModel):
    """Streaming chat response chunk"""
    chunk: str = Field(..., description="Text chunk")
    is_complete: bool = Field(False, description="Is this the final chunk?")
    sources: Optional[List[SourceDocument]] = None
    suggestions: Optional[List[str]] = None