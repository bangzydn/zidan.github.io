from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware

from .schemas import ChatRequest, ChatResponse, HealthResponse
from .rag_stub import RagEngine

app = FastAPI(title="ClipAI Advisor Backend", version="0.1.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

rag_engine = RagEngine()


@app.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    return HealthResponse(status="ok")


@app.post("/chat", response_model=ChatResponse)
async def chat(request: ChatRequest) -> ChatResponse:
    try:
        answer, citations = rag_engine.answer(query_text=request.message, user_id=request.user_id)
        return ChatResponse(answer=answer, citations=citations)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc))