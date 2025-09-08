# ClipAI Advisor - Production Deployment Guide

## 🌐 Deployment Options

### 1. Vercel + Heroku (Recommended for MVP)
- **Frontend**: Vercel (Next.js optimized)
- **Backend**: Heroku (Easy scaling)
- **Database**: Supabase (Managed PostgreSQL)
- **Vector DB**: Pinecone (Managed vector store)

### 2. AWS Full Stack
- **Frontend**: AWS Amplify / CloudFront + S3
- **Backend**: ECS Fargate / Lambda
- **Database**: RDS PostgreSQL
- **Vector DB**: OpenSearch / Self-hosted

### 3. Google Cloud Platform
- **Frontend**: Firebase Hosting
- **Backend**: Cloud Run
- **Database**: Cloud SQL
- **Vector DB**: Vertex AI Vector Search

### 4. Self-Hosted (VPS/Dedicated)
- **Server**: DigitalOcean Droplet / Linode
- **Reverse Proxy**: Nginx
- **SSL**: Let's Encrypt
- **Orchestration**: Docker Compose

## 🚀 Option 1: Vercel + Heroku Deployment

### Prerequisites
- Vercel account
- Heroku account
- Supabase account
- Pinecone account (optional)
- Domain name (optional)

### Step 1: Database Setup (Supabase)

1. **Create Supabase Project**:
   ```bash
   # Visit https://supabase.com/dashboard
   # Create new project
   # Note down the connection string
   ```

2. **Database Schema**:
   ```sql
   -- Run in Supabase SQL editor
   CREATE TABLE conversations (
       id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
       title TEXT,
       user_id UUID,
       created_at TIMESTAMP DEFAULT NOW(),
       updated_at TIMESTAMP DEFAULT NOW()
   );

   CREATE TABLE messages (
       id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
       conversation_id UUID REFERENCES conversations(id) ON DELETE CASCADE,
       role TEXT NOT NULL,
       content TEXT NOT NULL,
       metadata JSONB,
       created_at TIMESTAMP DEFAULT NOW()
   );

   CREATE INDEX idx_messages_conversation ON messages(conversation_id);
   CREATE INDEX idx_conversations_user ON conversations(user_id);
   ```

### Step 2: Vector Database (Pinecone)

1. **Create Pinecone Index**:
   ```bash
   # Visit https://app.pinecone.io/
   # Create index with:
   # - Name: clipai-knowledge
   # - Dimensions: 384 (for sentence-transformers/all-MiniLM-L6-v2)
   # - Metric: cosine
   ```

2. **Get API Keys**:
   ```bash
   # Note down:
   # - API Key
   # - Environment
   # - Index Name
   ```

### Step 3: Backend Deployment (Heroku)

1. **Prepare Backend**:
   ```bash
   cd backend
   
   # Create Procfile
   echo "web: uvicorn app.main:app --host 0.0.0.0 --port \$PORT" > Procfile
   
   # Create runtime.txt
   echo "python-3.11.0" > runtime.txt
   ```

2. **Deploy to Heroku**:
   ```bash
   # Install Heroku CLI
   # Login to Heroku
   heroku login
   
   # Create app
   heroku create clipai-advisor-backend
   
   # Set environment variables
   heroku config:set DATABASE_URL="your_supabase_connection_string"
   heroku config:set OPENAI_API_KEY="your_openai_key"
   heroku config:set PINECONE_API_KEY="your_pinecone_key"
   heroku config:set PINECONE_ENVIRONMENT="your_pinecone_env"
   heroku config:set SECRET_KEY="your_production_secret_key"
   heroku config:set ALLOWED_ORIGINS='["https://clipai-advisor.vercel.app"]'
   
   # Deploy
   git add .
   git commit -m "Deploy to Heroku"
   git push heroku main
   ```

3. **Initialize Knowledge Base**:
   ```bash
   # Upload knowledge base via API or admin panel
   curl -X POST https://clipai-advisor-backend.herokuapp.com/api/v1/knowledge/upload \
     -F "file=@knowledge-base/mentalitas/mentalitas_the_everything.md"
   ```

### Step 4: Frontend Deployment (Vercel)

1. **Prepare Frontend**:
   ```bash
   cd frontend
   
   # Update environment variables
   echo "BACKEND_URL=https://clipai-advisor-backend.herokuapp.com" > .env.production
   ```

2. **Deploy to Vercel**:
   ```bash
   # Install Vercel CLI
   npm i -g vercel
   
   # Login and deploy
   vercel login
   vercel --prod
   
   # Or connect GitHub repo at vercel.com
   ```

3. **Configure Environment Variables**:
   ```bash
   # In Vercel dashboard, set:
   BACKEND_URL=https://clipai-advisor-backend.herokuapp.com
   NEXT_PUBLIC_APP_NAME=ClipAI Advisor
   ```

### Step 5: Custom Domain (Optional)

1. **Configure Domain in Vercel**:
   - Add domain in Vercel dashboard
   - Update DNS records as instructed

2. **Update CORS Settings**:
   ```bash
   heroku config:set ALLOWED_ORIGINS='["https://your-domain.com","https://clipai-advisor.vercel.app"]'
   ```

## 🔧 Option 2: AWS Deployment

### Architecture Overview
```
CloudFront (CDN) → S3 (Static Assets)
                ↓
API Gateway → Lambda (Backend) → RDS (Database)
                ↓
OpenSearch (Vector DB)
```

### Step 1: Backend (Lambda + API Gateway)

1. **Prepare Lambda Function**:
   ```bash
   cd backend
   
   # Install AWS Lambda adapter
   pip install mangum
   
   # Create lambda_handler.py
   cat > lambda_handler.py << EOF
   from mangum import Mangum
   from app.main import app
   
   handler = Mangum(app)
   EOF
   ```

2. **Deploy with AWS SAM**:
   ```yaml
   # template.yaml
   AWSTemplateFormatVersion: '2010-09-09'
   Transform: AWS::Serverless-2016-10-31
   
   Resources:
     ClipAIFunction:
       Type: AWS::Serverless::Function
       Properties:
         CodeUri: .
         Handler: lambda_handler.handler
         Runtime: python3.11
         Environment:
           Variables:
             DATABASE_URL: !Ref DatabaseURL
             OPENAI_API_KEY: !Ref OpenAIKey
   ```

### Step 2: Frontend (S3 + CloudFront)

1. **Build and Upload**:
   ```bash
   cd frontend
   npm run build
   aws s3 sync out/ s3://clipai-advisor-frontend
   ```

2. **CloudFront Distribution**:
   - Create distribution pointing to S3 bucket
   - Configure custom error pages for SPA routing
   - Set up SSL certificate

### Step 3: Database (RDS)

1. **Create RDS Instance**:
   ```bash
   aws rds create-db-instance \
     --db-instance-identifier clipai-advisor-db \
     --db-instance-class db.t3.micro \
     --engine postgres \
     --master-username postgres \
     --master-user-password your-password \
     --allocated-storage 20
   ```

## 🐳 Option 3: Docker Swarm / Kubernetes

### Docker Swarm

1. **Prepare docker-compose.prod.yml**:
   ```yaml
   version: '3.8'
   services:
     frontend:
       image: clipai/frontend:latest
       ports:
         - "80:3000"
       environment:
         - BACKEND_URL=http://backend:8000
       deploy:
         replicas: 2
   
     backend:
       image: clipai/backend:latest
       ports:
         - "8000:8000"
       environment:
         - DATABASE_URL=postgresql://postgres:password@postgres:5432/clipai_advisor
       deploy:
         replicas: 2
   
     postgres:
       image: postgres:15-alpine
       volumes:
         - postgres_data:/var/lib/postgresql/data
       deploy:
         placement:
           constraints:
             - node.role == manager
   ```

2. **Deploy**:
   ```bash
   docker swarm init
   docker stack deploy -c docker-compose.prod.yml clipai
   ```

### Kubernetes

1. **Create Kubernetes Manifests**:
   ```yaml
   # k8s/namespace.yaml
   apiVersion: v1
   kind: Namespace
   metadata:
     name: clipai-advisor
   
   ---
   # k8s/backend-deployment.yaml
   apiVersion: apps/v1
   kind: Deployment
   metadata:
     name: backend
     namespace: clipai-advisor
   spec:
     replicas: 3
     selector:
       matchLabels:
         app: backend
     template:
       metadata:
         labels:
           app: backend
       spec:
         containers:
         - name: backend
           image: clipai/backend:latest
           ports:
           - containerPort: 8000
           env:
           - name: DATABASE_URL
             valueFrom:
               secretKeyRef:
                 name: app-secrets
                 key: database-url
   ```

2. **Deploy**:
   ```bash
   kubectl apply -f k8s/
   ```

## 🔍 Monitoring & Analytics

### Application Monitoring

1. **Sentry Integration**:
   ```bash
   # Backend
   pip install sentry-sdk[fastapi]
   
   # Frontend
   npm install @sentry/nextjs
   ```

2. **Logging**:
   ```python
   # Backend logging
   import logging
   from pythonjsonlogger import jsonlogger
   
   logger = logging.getLogger()
   handler = logging.StreamHandler()
   formatter = jsonlogger.JsonFormatter()
   handler.setFormatter(formatter)
   logger.addHandler(handler)
   ```

### Performance Monitoring

1. **Backend Metrics**:
   - Response time tracking
   - RAG query performance
   - Database query optimization
   - Memory usage monitoring

2. **Frontend Metrics**:
   - Core Web Vitals
   - User interaction tracking
   - Error rate monitoring
   - Performance budgets

### Business Analytics

1. **User Tracking**:
   ```typescript
   // Google Analytics 4
   import { gtag } from '@/lib/gtag'
   
   gtag('event', 'chat_message', {
     category: 'engagement',
     label: 'user_question'
   })
   ```

2. **Conversion Tracking**:
   - Chat completion rates
   - Knowledge base usage
   - User retention metrics
   - Feature adoption rates

## 🔐 Security Hardening

### Backend Security

1. **API Security**:
   ```python
   # Rate limiting
   from slowapi import Limiter, _rate_limit_exceeded_handler
   from slowapi.util import get_remote_address
   
   limiter = Limiter(key_func=get_remote_address)
   app.state.limiter = limiter
   app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)
   
   @app.post("/api/v1/chat/")
   @limiter.limit("10/minute")
   async def chat_endpoint(request: Request, ...):
       pass
   ```

2. **Input Validation**:
   ```python
   from pydantic import validator
   
   class ChatRequest(BaseModel):
       message: str
       
       @validator('message')
       def validate_message(cls, v):
           if len(v) > 4000:
               raise ValueError('Message too long')
           return v.strip()
   ```

### Infrastructure Security

1. **SSL/TLS**:
   ```bash
   # Let's Encrypt with Certbot
   certbot --nginx -d clipai-advisor.com
   ```

2. **Firewall Rules**:
   ```bash
   # UFW configuration
   ufw allow 22/tcp
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw enable
   ```

3. **Environment Secrets**:
   ```bash
   # Use secret management
   # AWS Secrets Manager
   # Azure Key Vault
   # HashiCorp Vault
   ```

## 📊 Cost Optimization

### Estimated Monthly Costs (MVP Scale)

#### Vercel + Heroku Stack:
- **Vercel Pro**: $20/month
- **Heroku Dyno**: $25/month
- **Supabase Pro**: $25/month
- **Pinecone Starter**: $70/month
- **OpenAI API**: $50-200/month (usage-based)
- **Total**: ~$190-315/month

#### AWS Stack:
- **Lambda**: $0-20/month (based on requests)
- **API Gateway**: $3-10/month
- **RDS t3.micro**: $15/month
- **S3 + CloudFront**: $5-15/month
- **Total**: ~$25-60/month + AI API costs

### Cost Optimization Strategies

1. **Caching**:
   - Implement Redis for frequent queries
   - Use CDN for static assets
   - Cache RAG responses for common questions

2. **Resource Optimization**:
   - Use smaller embedding models
   - Implement query batching
   - Optimize database queries
   - Use compression for responses

3. **Scaling Strategy**:
   - Start with free tiers
   - Monitor usage patterns
   - Scale incrementally
   - Use auto-scaling where available

## 🚨 Disaster Recovery

### Backup Strategy

1. **Database Backups**:
   ```bash
   # Automated daily backups
   pg_dump $DATABASE_URL > backup_$(date +%Y%m%d).sql
   aws s3 cp backup_$(date +%Y%m%d).sql s3://clipai-backups/
   ```

2. **Knowledge Base Backup**:
   ```bash
   # Version control knowledge base
   git add knowledge-base/
   git commit -m "Knowledge base update $(date)"
   git push origin main
   ```

### Recovery Procedures

1. **Database Recovery**:
   ```bash
   # Restore from backup
   psql $DATABASE_URL < backup_20250111.sql
   ```

2. **Application Recovery**:
   ```bash
   # Rollback deployment
   heroku rollback
   vercel --prod --force
   ```

## 📈 Scaling Considerations

### Horizontal Scaling

1. **Load Balancing**:
   - Multiple backend instances
   - Database read replicas
   - CDN for static content

2. **Microservices**:
   - Separate RAG service
   - Dedicated auth service
   - Analytics service

### Performance Optimization

1. **Database Optimization**:
   - Connection pooling
   - Query optimization
   - Indexing strategy
   - Partitioning for large datasets

2. **Caching Strategy**:
   - Application-level caching
   - Database query caching
   - CDN for static assets
   - Vector search result caching

---

## ✅ Post-Deployment Checklist

- [ ] All services are running and healthy
- [ ] SSL certificates are valid
- [ ] Monitoring is configured
- [ ] Backups are working
- [ ] Error tracking is active
- [ ] Performance monitoring is set up
- [ ] Security headers are configured
- [ ] CORS settings are correct
- [ ] Environment variables are secure
- [ ] Documentation is updated
- [ ] Team has access to admin panels
- [ ] Incident response plan is ready

Need help with deployment? Check the troubleshooting section or reach out to the development team!