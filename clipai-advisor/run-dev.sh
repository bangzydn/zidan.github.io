#!/bin/bash

# ClipAI Advisor - Development Runner Script
# This script starts both frontend and backend in development mode

set -e

echo "🚀 Starting ClipAI Advisor Development Environment..."

# Check if required tools are installed
check_requirements() {
    echo "📋 Checking requirements..."
    
    if ! command -v python3 &> /dev/null; then
        echo "❌ Python 3 is not installed"
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        echo "❌ Node.js is not installed"
        exit 1
    fi
    
    if ! command -v npm &> /dev/null; then
        echo "❌ npm is not installed"
        exit 1
    fi
    
    echo "✅ All requirements satisfied"
}

# Setup backend
setup_backend() {
    echo "🔧 Setting up backend..."
    
    cd backend
    
    # Create virtual environment if it doesn't exist
    if [ ! -d "venv" ]; then
        echo "📦 Creating Python virtual environment..."
        python3 -m venv venv
    fi
    
    # Activate virtual environment
    source venv/bin/activate
    
    # Install dependencies
    echo "📦 Installing Python dependencies..."
    pip install -r requirements.txt
    
    # Create .env if it doesn't exist
    if [ ! -f ".env" ]; then
        echo "📝 Creating backend .env file..."
        cp ../.env.example .env
        echo "⚠️  Please edit backend/.env with your API keys"
    fi
    
    cd ..
}

# Setup frontend
setup_frontend() {
    echo "🔧 Setting up frontend..."
    
    cd frontend
    
    # Install dependencies
    echo "📦 Installing Node.js dependencies..."
    npm install
    
    # Create .env.local if it doesn't exist
    if [ ! -f ".env.local" ]; then
        echo "📝 Creating frontend .env.local file..."
        cp .env.local .env.local.bak 2>/dev/null || true
        echo "BACKEND_URL=http://localhost:8000" > .env.local
        echo "NEXT_PUBLIC_APP_NAME=ClipAI Advisor" >> .env.local
    fi
    
    cd ..
}

# Start services
start_services() {
    echo "🚀 Starting development servers..."
    
    # Start backend in background
    echo "🐍 Starting FastAPI backend..."
    cd backend
    source venv/bin/activate
    uvicorn app.main:app --reload --host 0.0.0.0 --port 8000 &
    BACKEND_PID=$!
    cd ..
    
    # Wait for backend to start
    echo "⏳ Waiting for backend to start..."
    sleep 5
    
    # Start frontend
    echo "⚛️  Starting Next.js frontend..."
    cd frontend
    npm run dev &
    FRONTEND_PID=$!
    cd ..
    
    # Store PIDs for cleanup
    echo $BACKEND_PID > .backend.pid
    echo $FRONTEND_PID > .frontend.pid
    
    echo ""
    echo "🎉 Development servers started successfully!"
    echo ""
    echo "📱 Frontend: http://localhost:3000"
    echo "🔧 Backend:  http://localhost:8000"
    echo "📖 API Docs: http://localhost:8000/docs"
    echo ""
    echo "Press Ctrl+C to stop all servers"
    
    # Wait for user interrupt
    wait
}

# Cleanup function
cleanup() {
    echo ""
    echo "🛑 Stopping development servers..."
    
    # Kill background processes
    if [ -f ".backend.pid" ]; then
        kill $(cat .backend.pid) 2>/dev/null || true
        rm .backend.pid
    fi
    
    if [ -f ".frontend.pid" ]; then
        kill $(cat .frontend.pid) 2>/dev/null || true
        rm .frontend.pid
    fi
    
    # Kill any remaining processes
    pkill -f "uvicorn app.main:app" 2>/dev/null || true
    pkill -f "next-server" 2>/dev/null || true
    
    echo "✅ All servers stopped"
    exit 0
}

# Handle interrupts
trap cleanup SIGINT SIGTERM

# Main execution
main() {
    check_requirements
    setup_backend
    setup_frontend
    start_services
}

# Run main function
main