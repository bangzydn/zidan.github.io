# ClipAI Advisor Backend

FastAPI backend for the ClipAI Advisor MVP.

## Quickstart

1. Ensure Python 3.10+ is available.
2. Create a virtual environment (Debian PEP668-safe):
   - If `python3-venv` is missing, install it or use `--break-system-packages` at your own risk.
3. Install deps:
```
python3 -m venv .venv
source .venv/bin/activate
pip install --upgrade pip setuptools wheel
pip install -r requirements.txt
```
4. Run server:
```
uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
```

## Endpoints
- GET /health
- POST /chat { message, user_id? }

## Next steps
- Replace `rag_stub.py` with FAISS/Pinecone + embeddings
- Add JWT auth and Supabase integration