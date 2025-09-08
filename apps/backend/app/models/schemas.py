from typing import List, Optional
from pydantic import BaseModel, Field

class ChatRequest(BaseModel):
    question: str = Field(..., description="User question")
    user_id: Optional[str] = Field(default=None, description="Optional user identifier")
    top_k: int = Field(default=5, ge=1, le=50, description="Number of chunks to retrieve")

class ChatResponse(BaseModel):
    answer: str
    sources: List[str] = []

class UploadResponse(BaseModel):
    added: int = 0
    detail: str = ""
