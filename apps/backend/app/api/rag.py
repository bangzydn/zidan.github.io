from fastapi import APIRouter, UploadFile, File
from typing import List
from ..models.schemas import ChatRequest, ChatResponse, UploadResponse

router = APIRouter(prefix="/rag", tags=["rag"])

@router.post("/chat", response_model=ChatResponse)
async def rag_chat(body: ChatRequest) -> ChatResponse:
    # TODO: Implement retrieval + generation
    return ChatResponse(answer="Stub: implement RAG pipeline.", sources=[])

@router.post("/upload", response_model=UploadResponse)
async def rag_upload(files: List[UploadFile] = File(...)) -> UploadResponse:
    # TODO: Parse and ingest into vector store
    total = len(files)
    return UploadResponse(added=total, detail="Stub: ingestion not implemented yet.")
