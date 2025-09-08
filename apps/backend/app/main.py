from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import os
from .api.rag import router as rag_router

app = FastAPI(title="ClipAI Advisor API", version="0.1.0")

allowed_origins = os.getenv("CORS_ORIGINS", "*").split(",")
app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/health")
async def health():
    return {"status": "ok"}

app.include_router(rag_router)
