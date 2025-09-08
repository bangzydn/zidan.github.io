"""
RAG Service - Core Retrieval-Augmented Generation logic
Handles knowledge base, vector search, dan response generation
"""

import asyncio
import logging
from typing import List, Dict, Any, Optional, Tuple
from pathlib import Path
import json
from datetime import datetime

from langchain.embeddings import HuggingFaceEmbeddings
from langchain.vectorstores import FAISS
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.document_loaders import (
    PyPDFLoader, 
    TextLoader, 
    UnstructuredMarkdownLoader
)
from langchain.schema import Document
from langchain.llms import OpenAI
from langchain.chat_models import ChatOpenAI
from langchain.chains import ConversationalRetrievalChain
from langchain.memory import ConversationBufferWindowMemory

from app.core.config import settings
from app.schemas.chat import ChatMessage, ChatResponse
from app.utils.text_processing import clean_text, extract_citations

logger = logging.getLogger(__name__)

class RAGService:
    """
    Service untuk menangani RAG pipeline:
    1. Load dan process knowledge base (dokumen kvmedia)
    2. Create embeddings dan vector store
    3. Handle retrieval dan generation
    4. Manage conversation memory
    """
    
    def __init__(self):
        self.embeddings = None
        self.vectorstore = None
        self.llm = None
        self.qa_chain = None
        self.memory = None
        self.is_initialized = False
        
    async def initialize(self):
        """Initialize RAG service components"""
        try:
            logger.info("Initializing RAG service...")
            
            # Initialize embeddings
            await self._init_embeddings()
            
            # Initialize LLM
            await self._init_llm()
            
            # Load knowledge base
            await self._load_knowledge_base()
            
            # Initialize conversation chain
            await self._init_conversation_chain()
            
            self.is_initialized = True
            logger.info("RAG service initialized successfully!")
            
        except Exception as e:
            logger.error(f"Failed to initialize RAG service: {e}")
            raise
    
    async def _init_embeddings(self):
        """Initialize embedding model"""
        logger.info(f"Loading embedding model: {settings.EMBEDDING_MODEL}")
        
        self.embeddings = HuggingFaceEmbeddings(
            model_name=settings.EMBEDDING_MODEL,
            model_kwargs={'device': 'cpu'},
            encode_kwargs={'normalize_embeddings': False}
        )
        
    async def _init_llm(self):
        """Initialize LLM based on configuration"""
        logger.info(f"Initializing LLM: {settings.DEFAULT_LLM_PROVIDER}")
        
        if settings.DEFAULT_LLM_PROVIDER == "openai" and settings.OPENAI_API_KEY:
            self.llm = ChatOpenAI(
                model=settings.DEFAULT_MODEL,
                temperature=0.7,
                openai_api_key=settings.OPENAI_API_KEY,
                max_tokens=1000
            )
        elif settings.DEFAULT_LLM_PROVIDER == "grok" and settings.GROK_API_KEY:
            # Note: Grok API integration would go here
            # For now, fallback to OpenAI format
            logger.warning("Grok API not yet implemented, falling back to OpenAI")
            self.llm = ChatOpenAI(
                model="gpt-3.5-turbo",
                temperature=0.7,
                openai_api_key=settings.OPENAI_API_KEY or "dummy-key",
                max_tokens=1000
            )
        else:
            raise ValueError("No valid LLM configuration found")
    
    async def _load_knowledge_base(self):
        """Load dan process knowledge base documents"""
        logger.info("Loading knowledge base...")
        
        knowledge_path = settings.knowledge_base_path
        if not knowledge_path.exists():
            logger.warning(f"Knowledge base directory not found: {knowledge_path}")
            # Create empty vectorstore
            self.vectorstore = FAISS.from_texts(
                ["Selamat datang di ClipAI Advisor! Basis pengetahuan sedang dimuat."],
                self.embeddings,
                metadatas=[{"source": "welcome", "type": "system"}]
            )
            return
        
        documents = []
        
        # Load different file types
        for file_path in knowledge_path.rglob("*"):
            if file_path.is_file() and file_path.suffix in ['.pdf', '.txt', '.md']:
                try:
                    docs = await self._load_document(file_path)
                    documents.extend(docs)
                    logger.info(f"Loaded {len(docs)} chunks from {file_path.name}")
                except Exception as e:
                    logger.error(f"Failed to load {file_path}: {e}")
        
        if not documents:
            logger.warning("No documents found in knowledge base")
            # Create empty vectorstore with welcome message
            self.vectorstore = FAISS.from_texts(
                ["Selamat datang di ClipAI Advisor! Silakan upload dokumen ke knowledge base."],
                self.embeddings,
                metadatas=[{"source": "welcome", "type": "system"}]
            )
            return
        
        # Split documents into chunks
        text_splitter = RecursiveCharacterTextSplitter(
            chunk_size=1000,
            chunk_overlap=200,
            length_function=len,
            separators=["\n\n", "\n", " ", ""]
        )
        
        split_docs = text_splitter.split_documents(documents)
        logger.info(f"Split {len(documents)} documents into {len(split_docs)} chunks")
        
        # Create vector store
        self.vectorstore = FAISS.from_documents(split_docs, self.embeddings)
        logger.info("Vector store created successfully!")
    
    async def _load_document(self, file_path: Path) -> List[Document]:
        """Load single document based on file type"""
        try:
            if file_path.suffix == '.pdf':
                loader = PyPDFLoader(str(file_path))
            elif file_path.suffix == '.md':
                loader = UnstructuredMarkdownLoader(str(file_path))
            else:  # .txt and others
                loader = TextLoader(str(file_path), encoding='utf-8')
            
            documents = loader.load()
            
            # Add metadata
            for doc in documents:
                doc.metadata.update({
                    "source": file_path.name,
                    "file_type": file_path.suffix,
                    "loaded_at": datetime.now().isoformat()
                })
            
            return documents
            
        except Exception as e:
            logger.error(f"Error loading {file_path}: {e}")
            return []
    
    async def _init_conversation_chain(self):
        """Initialize conversation chain with memory"""
        if not self.vectorstore or not self.llm:
            raise ValueError("Vectorstore and LLM must be initialized first")
        
        # Initialize memory
        self.memory = ConversationBufferWindowMemory(
            k=5,  # Remember last 5 exchanges
            memory_key="chat_history",
            return_messages=True,
            output_key="answer"
        )
        
        # Create retrieval chain
        self.qa_chain = ConversationalRetrievalChain.from_llm(
            llm=self.llm,
            retriever=self.vectorstore.as_retriever(
                search_type="similarity",
                search_kwargs={"k": settings.TOP_K_RETRIEVAL}
            ),
            memory=self.memory,
            return_source_documents=True,
            verbose=True
        )
    
    async def chat(self, message: str, user_id: str = None) -> ChatResponse:
        """
        Handle chat message dengan RAG
        
        Args:
            message: User message/question
            user_id: Optional user ID for personalization
            
        Returns:
            ChatResponse with answer and sources
        """
        if not self.is_initialized:
            raise ValueError("RAG service not initialized")
        
        try:
            logger.info(f"Processing chat message: {message[:100]}...")
            
            # Add context about kliping/kvmedia domain
            enhanced_message = self._enhance_message_context(message)
            
            # Get response from chain
            result = await asyncio.to_thread(
                self.qa_chain,
                {"question": enhanced_message}
            )
            
            answer = result["answer"]
            source_docs = result.get("source_documents", [])
            
            # Extract citations
            sources = self._extract_sources(source_docs)
            
            # Clean and format answer
            formatted_answer = self._format_answer(answer, message)
            
            return ChatResponse(
                message=formatted_answer,
                sources=sources,
                confidence=self._calculate_confidence(source_docs),
                suggestions=self._generate_suggestions(message, source_docs)
            )
            
        except Exception as e:
            logger.error(f"Error in chat processing: {e}")
            return ChatResponse(
                message="Maaf, terjadi kesalahan saat memproses pertanyaan Anda. Silakan coba lagi.",
                sources=[],
                confidence=0.0,
                suggestions=["Coba pertanyaan yang lebih spesifik tentang kliping atau agensi"]
            )
    
    def _enhance_message_context(self, message: str) -> str:
        """Add context about kliping domain to user message"""
        context = """
        Konteks: Anda adalah AI assistant untuk agensi kliping yang membantu mengubah video panjang menjadi klip pendek untuk TikTok, Instagram Reels, dan YouTube Shorts. 
        Berikan jawaban berdasarkan pengetahuan kvmedia tentang:
        - Mentalitas (The Everything, The Doctor, dll.)
        - Perkerjaan dan proyek kliping
        - Money scheme dan affiliate marketing
        - CV building dan networking
        - Strategi pitching dan leads generation
        
        Pertanyaan user: """
        
        return context + message
    
    def _extract_sources(self, source_docs: List[Document]) -> List[Dict[str, Any]]:
        """Extract source information from documents"""
        sources = []
        seen_sources = set()
        
        for doc in source_docs:
            source_name = doc.metadata.get("source", "Unknown")
            if source_name not in seen_sources:
                sources.append({
                    "title": source_name,
                    "type": doc.metadata.get("file_type", "document"),
                    "snippet": doc.page_content[:200] + "..." if len(doc.page_content) > 200 else doc.page_content
                })
                seen_sources.add(source_name)
        
        return sources[:3]  # Limit to top 3 sources
    
    def _format_answer(self, answer: str, original_question: str) -> str:
        """Format and enhance the answer"""
        # Clean the answer
        formatted = clean_text(answer)
        
        # Add helpful formatting for kliping context
        if any(keyword in original_question.lower() for keyword in ["mentalitas", "mindset"]):
            formatted += "\n\n💡 **Tip**: Terapkan mentalitas ini secara konsisten dalam proyek kliping Anda."
        
        elif any(keyword in original_question.lower() for keyword in ["money", "uang", "income"]):
            formatted += "\n\n💰 **Action**: Pertimbangkan untuk booking konsultasi agensi untuk strategi monetisasi yang lebih personal."
        
        elif any(keyword in original_question.lower() for keyword in ["cv", "resume", "portfolio"]):
            formatted += "\n\n📄 **Next Step**: Gunakan template ATS-friendly untuk meningkatkan peluang diterima."
        
        return formatted
    
    def _calculate_confidence(self, source_docs: List[Document]) -> float:
        """Calculate confidence score based on retrieval quality"""
        if not source_docs:
            return 0.0
        
        # Simple confidence calculation based on number of relevant sources
        base_confidence = min(len(source_docs) * 0.2, 1.0)
        
        # Boost confidence if sources are from kvmedia knowledge base
        kvmedia_sources = sum(1 for doc in source_docs if "kvmedia" in doc.metadata.get("source", "").lower())
        confidence_boost = kvmedia_sources * 0.1
        
        return min(base_confidence + confidence_boost, 1.0)
    
    def _generate_suggestions(self, message: str, source_docs: List[Document]) -> List[str]:
        """Generate follow-up suggestions"""
        suggestions = []
        
        # Context-based suggestions
        if "mentalitas" in message.lower():
            suggestions.extend([
                "Bagaimana cara menerapkan mentalitas The Doctor dalam proyek kliping?",
                "Apa perbedaan mentalitas The Everything vs The Doctor?",
                "Contoh penerapan mentalitas dalam pitching ke klien?"
            ])
        
        elif "money" in message.lower() or "income" in message.lower():
            suggestions.extend([
                "Strategi affiliate marketing untuk creator kliping?",
                "Cara menentukan tarif jasa kliping yang kompetitif?",
                "Platform terbaik untuk monetisasi konten klip?"
            ])
        
        elif "cv" in message.lower() or "portfolio" in message.lower():
            suggestions.extend([
                "Template CV ATS-friendly untuk freelancer kliping?",
                "Cara showcase portfolio kliping yang menarik?",
                "Skills wajib untuk dicantumkan di CV editor video?"
            ])
        
        else:
            # Default suggestions
            suggestions.extend([
                "Bagaimana memulai agensi kliping dari nol?",
                "Tools terbaik untuk editing klip di 2025?",
                "Strategi networking untuk mendapat klien kliping?"
            ])
        
        return suggestions[:3]  # Limit to 3 suggestions
    
    async def add_document(self, file_path: Path) -> bool:
        """Add new document to knowledge base"""
        try:
            documents = await self._load_document(file_path)
            if not documents:
                return False
            
            # Split into chunks
            text_splitter = RecursiveCharacterTextSplitter(
                chunk_size=1000,
                chunk_overlap=200
            )
            split_docs = text_splitter.split_documents(documents)
            
            # Add to existing vectorstore
            if self.vectorstore:
                self.vectorstore.add_documents(split_docs)
            else:
                self.vectorstore = FAISS.from_documents(split_docs, self.embeddings)
            
            logger.info(f"Added {len(split_docs)} chunks from {file_path.name}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to add document {file_path}: {e}")
            return False
    
    async def cleanup(self):
        """Cleanup resources"""
        logger.info("Cleaning up RAG service resources...")
        self.memory = None
        self.qa_chain = None
        self.is_initialized = False