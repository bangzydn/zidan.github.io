# ClipAI Advisor - Setup & Development Guide

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ dan npm
- Python 3.9+ dan pip
- Docker dan Docker Compose (optional)
- Git

### 1. Clone dan Setup Project
```bash
git clone <repository-url>
cd clipai-advisor

# Install backend dependencies
cd backend
pip install -r requirements.txt

# Install frontend dependencies
cd ../frontend
npm install
```

### 2. Environment Configuration

#### Backend (.env)
```bash
# Backend environment variables
cd backend
cp .env.example .env

# Edit .env file:
DATABASE_URL=postgresql://postgres:password@localhost:5432/clipai_advisor
OPENAI_API_KEY=your_openai_api_key_here
GROK_API_KEY=your_grok_api_key_here  # Optional
PINECONE_API_KEY=your_pinecone_key   # Optional
SECRET_KEY=your-super-secret-key-change-in-production
```

#### Frontend (.env.local)
```bash
# Frontend environment variables
cd frontend
cp .env.local.example .env.local

# Edit .env.local:
BACKEND_URL=http://localhost:8000
NEXT_PUBLIC_APP_NAME="ClipAI Advisor"
```

### 3. Database Setup

#### Option A: Local PostgreSQL
```bash
# Install PostgreSQL
sudo apt-get install postgresql postgresql-contrib  # Ubuntu
brew install postgresql  # macOS

# Create database
createdb clipai_advisor
```

#### Option B: Docker PostgreSQL
```bash
cd docker
docker-compose up postgres -d
```

### 4. Knowledge Base Setup
```bash
# Copy template knowledge base
cp -r knowledge-base-template/* knowledge-base/

# Add your own documents (PDF, MD, TXT)
# Place files in appropriate folders:
# - knowledge-base/mentalitas/
# - knowledge-base/perkerjaan/
# - knowledge-base/proyek/
# - knowledge-base/affiliate/
```

### 5. Run Development Servers

#### Option A: Manual
```bash
# Terminal 1: Backend
cd backend
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000

# Terminal 2: Frontend
cd frontend
npm run dev
```

#### Option B: Docker Compose
```bash
cd docker
docker-compose up --build
```

## 🔧 Development Workflow

### Backend Development
```bash
cd backend

# Run with hot reload
uvicorn app.main:app --reload

# Run tests
pytest

# Format code
black app/
flake8 app/

# Database migrations
alembic revision --autogenerate -m "description"
alembic upgrade head
```

### Frontend Development
```bash
cd frontend

# Development server
npm run dev

# Build for production
npm run build
npm start

# Linting
npm run lint
npm run lint:fix

# Type checking
npm run type-check
```

### Adding New Knowledge Base Documents

1. **Create Document**: Place file in appropriate folder
   ```
   knowledge-base/
   ├── mentalitas/new_mindset.md
   ├── proyek/new_strategy.pdf
   └── tools/new_software.txt
   ```

2. **Format Requirements**:
   - Use clear headers and sections
   - Include keywords relevant to kliping
   - Follow template format for consistency

3. **Test Retrieval**:
   ```bash
   # Test via API
   curl -X POST http://localhost:8000/api/v1/chat/ \
     -H "Content-Type: application/json" \
     -d '{"message": "Test question about new document"}'
   ```

## 🐳 Docker Development

### Full Stack with Docker
```bash
cd docker
docker-compose up --build
```

### Individual Services
```bash
# Backend only
docker-compose up backend postgres

# Frontend only
docker-compose up frontend

# Database only
docker-compose up postgres
```

### Docker Volumes
- `postgres_data`: Database persistence
- `chromadb_data`: Vector database storage
- `../knowledge-base`: Knowledge base documents

## 🔍 Testing

### Backend Testing
```bash
cd backend

# Unit tests
pytest tests/

# Integration tests
pytest tests/integration/

# Coverage
pytest --cov=app tests/
```

### Frontend Testing
```bash
cd frontend

# Unit tests
npm test

# E2E tests
npm run test:e2e

# Component tests
npm run test:components
```

### Manual Testing Checklist
- [ ] Chat interface loads correctly
- [ ] Messages send and receive properly
- [ ] Knowledge base retrieval works
- [ ] Sources are displayed correctly
- [ ] Suggestions appear for follow-up questions
- [ ] Mobile responsiveness works
- [ ] Error handling functions properly

## 📊 Monitoring & Debugging

### Logs
```bash
# Backend logs
docker-compose logs backend -f

# Frontend logs
docker-compose logs frontend -f

# Database logs
docker-compose logs postgres -f
```

### Health Checks
```bash
# Backend health
curl http://localhost:8000/health

# Database connection
curl http://localhost:8000/api/v1/health/db

# RAG service status
curl http://localhost:8000/api/v1/health/rag
```

### Performance Monitoring
- Use browser dev tools for frontend performance
- Monitor API response times in backend logs
- Check database query performance with `EXPLAIN ANALYZE`

## 🔧 Troubleshooting

### Common Issues

#### "RAG service not available"
```bash
# Check if knowledge base is loaded
ls -la knowledge-base/

# Verify API keys in .env
grep API_KEY backend/.env

# Restart backend service
docker-compose restart backend
```

#### "Database connection failed"
```bash
# Check PostgreSQL status
docker-compose ps postgres

# Verify connection string
docker-compose exec backend python -c "from app.core.database import engine; print(engine.url)"
```

#### "Frontend can't connect to backend"
```bash
# Verify backend is running
curl http://localhost:8000/health

# Check CORS settings
grep ALLOWED_ORIGINS backend/app/core/config.py

# Verify environment variables
cat frontend/.env.local
```

#### "Knowledge base not loading"
```bash
# Check file permissions
ls -la knowledge-base/

# Verify file formats are supported
grep ALLOWED_FILE_TYPES backend/app/core/config.py

# Test document loading
docker-compose exec backend python -c "from app.services.rag_service import RAGService; rag = RAGService(); rag.initialize()"
```

### Performance Issues

#### Slow RAG responses
- Reduce `TOP_K_RETRIEVAL` in config
- Optimize document chunking size
- Use faster embedding model
- Consider caching frequent queries

#### High memory usage
- Limit knowledge base size
- Use quantized embedding models
- Implement document pagination
- Optimize vector store configuration

## 🔐 Security Considerations

### API Keys
- Never commit API keys to version control
- Use environment variables for all secrets
- Rotate keys regularly
- Use different keys for development/production

### Database Security
- Use strong passwords
- Enable SSL/TLS connections
- Implement proper user permissions
- Regular security updates

### CORS Configuration
- Restrict allowed origins in production
- Use HTTPS in production
- Implement rate limiting
- Add request validation

## 📝 Next Steps

1. **Add Authentication**: Implement user login/registration
2. **Improve RAG**: Fine-tune retrieval parameters
3. **Add Analytics**: Track user interactions
4. **Mobile App**: React Native version
5. **API Documentation**: OpenAPI/Swagger improvements
6. **Performance**: Implement caching layer
7. **Monitoring**: Add application monitoring
8. **Deployment**: Production deployment guide

---

Need help? Check [DEPLOYMENT.md](./DEPLOYMENT.md) for production deployment or [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) for detailed issue resolution.