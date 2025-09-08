"""
Chat API endpoints untuk ClipAI Advisor
Handles RAG-based conversations tentang agensi kliping
"""

from fastapi import APIRouter, HTTPException, Depends, BackgroundTasks
from fastapi.responses import StreamingResponse
from typing import List, Optional
import logging
import json
import asyncio
from datetime import datetime
import uuid

from app.schemas.chat import (
    ChatRequest, ChatResponse, ConversationHistory, 
    ConversationSummary, FeedbackRequest, StreamChatRequest, StreamChatChunk
)
from app.services.rag_service import RAGService
from app.core.database import get_db
from app.models.conversation import Conversation, Message
from sqlalchemy.orm import Session

logger = logging.getLogger(__name__)
router = APIRouter()

def get_rag_service() -> RAGService:
    """Dependency untuk mendapatkan RAG service"""
    from app.main import app
    if not hasattr(app.state, 'rag_service'):
        raise HTTPException(status_code=503, detail="RAG service not available")
    return app.state.rag_service

@router.post("/", response_model=ChatResponse)
async def chat(
    request: ChatRequest,
    background_tasks: BackgroundTasks,
    rag_service: RAGService = Depends(get_rag_service),
    db: Session = Depends(get_db)
):
    """
    Main chat endpoint - Handle user questions dengan RAG
    
    Contoh usage:
    ```
    POST /api/v1/chat/
    {
        "message": "Bagaimana cara memulai agensi kliping?",
        "conversation_id": "optional_conv_id"
    }
    ```
    """
    try:
        logger.info(f"Processing chat request: {request.message[:100]}...")
        
        # Generate conversation ID if not provided
        conversation_id = request.conversation_id or str(uuid.uuid4())
        
        # Get response from RAG service
        response = await rag_service.chat(
            message=request.message,
            user_id=None  # TODO: Add user authentication
        )
        
        # Set conversation ID
        response.conversation_id = conversation_id
        
        # Save conversation in background
        background_tasks.add_task(
            save_conversation,
            db=db,
            conversation_id=conversation_id,
            user_message=request.message,
            ai_response=response.message,
            sources=response.sources
        )
        
        logger.info(f"Chat response generated with confidence: {response.confidence}")
        return response
        
    except Exception as e:
        logger.error(f"Error in chat endpoint: {e}")
        raise HTTPException(
            status_code=500,
            detail="Terjadi kesalahan saat memproses pertanyaan Anda. Silakan coba lagi."
        )

@router.post("/stream")
async def stream_chat(
    request: StreamChatRequest,
    rag_service: RAGService = Depends(get_rag_service)
):
    """
    Streaming chat endpoint untuk real-time responses
    
    Returns Server-Sent Events (SSE) stream
    """
    async def generate_stream():
        try:
            conversation_id = request.conversation_id or str(uuid.uuid4())
            
            # Get response from RAG service
            response = await rag_service.chat(message=request.message)
            
            # Split response into chunks for streaming effect
            words = response.message.split()
            chunk_size = 3  # Send 3 words at a time
            
            for i in range(0, len(words), chunk_size):
                chunk_words = words[i:i + chunk_size]
                chunk_text = " ".join(chunk_words)
                
                chunk_data = StreamChatChunk(
                    chunk=chunk_text + " ",
                    is_complete=False
                )
                
                yield f"data: {chunk_data.json()}\n\n"
                await asyncio.sleep(0.1)  # Small delay for streaming effect
            
            # Send final chunk with metadata
            final_chunk = StreamChatChunk(
                chunk="",
                is_complete=True,
                sources=response.sources,
                suggestions=response.suggestions
            )
            
            yield f"data: {final_chunk.json()}\n\n"
            yield "data: [DONE]\n\n"
            
        except Exception as e:
            logger.error(f"Error in stream chat: {e}")
            error_chunk = StreamChatChunk(
                chunk="Maaf, terjadi kesalahan. Silakan coba lagi.",
                is_complete=True
            )
            yield f"data: {error_chunk.json()}\n\n"
    
    return StreamingResponse(
        generate_stream(),
        media_type="text/plain",
        headers={
            "Cache-Control": "no-cache",
            "Connection": "keep-alive",
            "X-Accel-Buffering": "no"  # Disable nginx buffering
        }
    )

@router.get("/conversations", response_model=List[ConversationSummary])
async def get_conversations(
    limit: int = 20,
    offset: int = 0,
    db: Session = Depends(get_db)
):
    """Get list of user conversations"""
    try:
        # TODO: Add user authentication and filter by user_id
        conversations = db.query(Conversation)\
            .order_by(Conversation.updated_at.desc())\
            .offset(offset)\
            .limit(limit)\
            .all()
        
        summaries = []
        for conv in conversations:
            last_message = db.query(Message)\
                .filter(Message.conversation_id == conv.id)\
                .order_by(Message.created_at.desc())\
                .first()
            
            summary = ConversationSummary(
                conversation_id=conv.id,
                title=conv.title or "Untitled Conversation",
                last_message=last_message.content[:100] + "..." if last_message else "",
                message_count=len(conv.messages),
                created_at=conv.created_at,
                updated_at=conv.updated_at
            )
            summaries.append(summary)
        
        return summaries
        
    except Exception as e:
        logger.error(f"Error getting conversations: {e}")
        raise HTTPException(status_code=500, detail="Failed to retrieve conversations")

@router.get("/conversations/{conversation_id}", response_model=ConversationHistory)
async def get_conversation(
    conversation_id: str,
    db: Session = Depends(get_db)
):
    """Get specific conversation history"""
    try:
        conversation = db.query(Conversation)\
            .filter(Conversation.id == conversation_id)\
            .first()
        
        if not conversation:
            raise HTTPException(status_code=404, detail="Conversation not found")
        
        messages = db.query(Message)\
            .filter(Message.conversation_id == conversation_id)\
            .order_by(Message.created_at.asc())\
            .all()
        
        chat_messages = []
        for msg in messages:
            chat_messages.append({
                "role": msg.role,
                "content": msg.content,
                "timestamp": msg.created_at,
                "metadata": json.loads(msg.metadata) if msg.metadata else None
            })
        
        return ConversationHistory(
            conversation_id=conversation_id,
            messages=chat_messages,
            created_at=conversation.created_at,
            updated_at=conversation.updated_at,
            user_id=conversation.user_id,
            title=conversation.title
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting conversation {conversation_id}: {e}")
        raise HTTPException(status_code=500, detail="Failed to retrieve conversation")

@router.delete("/conversations/{conversation_id}")
async def delete_conversation(
    conversation_id: str,
    db: Session = Depends(get_db)
):
    """Delete a conversation"""
    try:
        conversation = db.query(Conversation)\
            .filter(Conversation.id == conversation_id)\
            .first()
        
        if not conversation:
            raise HTTPException(status_code=404, detail="Conversation not found")
        
        # Delete associated messages
        db.query(Message)\
            .filter(Message.conversation_id == conversation_id)\
            .delete()
        
        # Delete conversation
        db.delete(conversation)
        db.commit()
        
        return {"message": "Conversation deleted successfully"}
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error deleting conversation {conversation_id}: {e}")
        db.rollback()
        raise HTTPException(status_code=500, detail="Failed to delete conversation")

@router.post("/feedback")
async def submit_feedback(
    feedback: FeedbackRequest,
    db: Session = Depends(get_db)
):
    """Submit feedback untuk chat response"""
    try:
        # TODO: Save feedback to database
        # For now, just log it
        logger.info(f"Received feedback for conversation {feedback.conversation_id}: "
                   f"Rating {feedback.rating}, Helpful: {feedback.helpful}")
        
        return {"message": "Feedback submitted successfully"}
        
    except Exception as e:
        logger.error(f"Error submitting feedback: {e}")
        raise HTTPException(status_code=500, detail="Failed to submit feedback")

# Background task functions
async def save_conversation(
    db: Session,
    conversation_id: str,
    user_message: str,
    ai_response: str,
    sources: List[dict]
):
    """Save conversation to database"""
    try:
        # Check if conversation exists
        conversation = db.query(Conversation)\
            .filter(Conversation.id == conversation_id)\
            .first()
        
        if not conversation:
            # Create new conversation
            title = user_message[:50] + "..." if len(user_message) > 50 else user_message
            conversation = Conversation(
                id=conversation_id,
                title=title,
                user_id=None,  # TODO: Add user authentication
                created_at=datetime.now(),
                updated_at=datetime.now()
            )
            db.add(conversation)
        else:
            # Update existing conversation
            conversation.updated_at = datetime.now()
        
        # Save user message
        user_msg = Message(
            conversation_id=conversation_id,
            role="user",
            content=user_message,
            created_at=datetime.now()
        )
        db.add(user_msg)
        
        # Save AI response
        ai_msg = Message(
            conversation_id=conversation_id,
            role="assistant",
            content=ai_response,
            metadata=json.dumps({"sources": sources}) if sources else None,
            created_at=datetime.now()
        )
        db.add(ai_msg)
        
        db.commit()
        logger.info(f"Saved conversation {conversation_id}")
        
    except Exception as e:
        logger.error(f"Error saving conversation: {e}")
        db.rollback()