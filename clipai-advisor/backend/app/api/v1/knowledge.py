"""
Knowledge base management endpoints
"""

from fastapi import APIRouter, HTTPException, UploadFile, File, Depends
from typing import List, Optional
import logging
import shutil
from pathlib import Path

from app.services.rag_service import RAGService
from app.core.config import settings

logger = logging.getLogger(__name__)
router = APIRouter()

def get_rag_service() -> RAGService:
    """Dependency untuk mendapatkan RAG service"""
    from app.main import app
    if not hasattr(app.state, 'rag_service'):
        raise HTTPException(status_code=503, detail="RAG service not available")
    return app.state.rag_service

@router.post("/upload")
async def upload_document(
    file: UploadFile = File(...),
    category: Optional[str] = None,
    rag_service: RAGService = Depends(get_rag_service)
):
    """
    Upload document ke knowledge base
    
    Args:
        file: File to upload (PDF, TXT, MD)
        category: Optional category (mentalitas, perkerjaan, proyek, affiliate)
    """
    try:
        # Validate file type
        allowed_extensions = ['.pdf', '.txt', '.md', '.docx']
        file_extension = Path(file.filename).suffix.lower()
        
        if file_extension not in allowed_extensions:
            raise HTTPException(
                status_code=400,
                detail=f"File type {file_extension} not supported. Allowed: {allowed_extensions}"
            )
        
        # Validate file size
        if file.size > settings.MAX_FILE_SIZE:
            raise HTTPException(
                status_code=400,
                detail=f"File too large. Maximum size: {settings.MAX_FILE_SIZE} bytes"
            )
        
        # Determine save path
        if category:
            save_dir = settings.knowledge_base_path / category
        else:
            save_dir = settings.knowledge_base_path / "general"
        
        save_dir.mkdir(parents=True, exist_ok=True)
        file_path = save_dir / file.filename
        
        # Save file
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
        
        # Add to RAG service
        success = await rag_service.add_document(file_path)
        
        if not success:
            # Clean up file if processing failed
            file_path.unlink(missing_ok=True)
            raise HTTPException(
                status_code=500,
                detail="Failed to process document"
            )
        
        logger.info(f"Successfully uploaded and processed: {file.filename}")
        
        return {
            "message": "Document uploaded and processed successfully",
            "filename": file.filename,
            "category": category or "general",
            "file_path": str(file_path.relative_to(settings.knowledge_base_path))
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error uploading document: {e}")
        raise HTTPException(
            status_code=500,
            detail="Internal server error during file upload"
        )

@router.get("/documents")
async def list_documents():
    """
    List all documents dalam knowledge base
    """
    try:
        documents = []
        knowledge_base_path = settings.knowledge_base_path
        
        if not knowledge_base_path.exists():
            return {"documents": [], "total": 0}
        
        for file_path in knowledge_base_path.rglob("*"):
            if file_path.is_file() and file_path.suffix in ['.pdf', '.txt', '.md', '.docx']:
                relative_path = file_path.relative_to(knowledge_base_path)
                category = relative_path.parts[0] if len(relative_path.parts) > 1 else "general"
                
                documents.append({
                    "filename": file_path.name,
                    "category": category,
                    "path": str(relative_path),
                    "size": file_path.stat().st_size,
                    "modified": file_path.stat().st_mtime
                })
        
        return {
            "documents": documents,
            "total": len(documents)
        }
        
    except Exception as e:
        logger.error(f"Error listing documents: {e}")
        raise HTTPException(
            status_code=500,
            detail="Failed to list documents"
        )

@router.delete("/documents/{category}/{filename}")
async def delete_document(
    category: str,
    filename: str,
    rag_service: RAGService = Depends(get_rag_service)
):
    """
    Delete document dari knowledge base
    """
    try:
        file_path = settings.knowledge_base_path / category / filename
        
        if not file_path.exists():
            raise HTTPException(
                status_code=404,
                detail="Document not found"
            )
        
        # Delete file
        file_path.unlink()
        
        # TODO: Remove from vector store
        # This would require rebuilding the entire vector store
        # For now, we'll just delete the file
        
        logger.info(f"Deleted document: {category}/{filename}")
        
        return {
            "message": "Document deleted successfully",
            "filename": filename,
            "category": category
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error deleting document: {e}")
        raise HTTPException(
            status_code=500,
            detail="Failed to delete document"
        )

@router.get("/categories")
async def list_categories():
    """
    List available categories dalam knowledge base
    """
    try:
        categories = []
        knowledge_base_path = settings.knowledge_base_path
        
        if knowledge_base_path.exists():
            for item in knowledge_base_path.iterdir():
                if item.is_dir():
                    doc_count = len(list(item.rglob("*.[pdf|txt|md|docx]")))
                    categories.append({
                        "name": item.name,
                        "document_count": doc_count
                    })
        
        return {"categories": categories}
        
    except Exception as e:
        logger.error(f"Error listing categories: {e}")
        raise HTTPException(
            status_code=500,
            detail="Failed to list categories"
        )

@router.post("/rebuild-index")
async def rebuild_vector_index(
    rag_service: RAGService = Depends(get_rag_service)
):
    """
    Rebuild vector index dari knowledge base
    Useful setelah manual file changes atau cleanup
    """
    try:
        # Re-initialize RAG service to rebuild vector store
        await rag_service._load_knowledge_base()
        
        return {
            "message": "Vector index rebuilt successfully"
        }
        
    except Exception as e:
        logger.error(f"Error rebuilding index: {e}")
        raise HTTPException(
            status_code=500,
            detail="Failed to rebuild vector index"
        )