"""
Text processing utilities untuk RAG system
"""

import re
from typing import List, Dict, Any
import html

def clean_text(text: str) -> str:
    """
    Clean dan normalize text untuk display
    """
    if not text:
        return ""
    
    # Remove excessive whitespace
    text = re.sub(r'\s+', ' ', text)
    
    # Remove HTML entities
    text = html.unescape(text)
    
    # Clean up common formatting issues
    text = text.replace('\n\n\n', '\n\n')  # Max 2 line breaks
    text = text.replace('  ', ' ')  # Remove double spaces
    
    # Strip leading/trailing whitespace
    text = text.strip()
    
    return text

def extract_citations(text: str) -> List[str]:
    """
    Extract citations atau references dari text
    """
    # Pattern untuk mencari citations seperti [1], (Source: document.pdf), dll
    citation_patterns = [
        r'\[(\d+)\]',  # [1], [2], etc.
        r'\(Source:\s*([^)]+)\)',  # (Source: document.pdf)
        r'\*\*Source\*\*:\s*([^\n]+)',  # **Source**: document.pdf
    ]
    
    citations = []
    for pattern in citation_patterns:
        matches = re.findall(pattern, text, re.IGNORECASE)
        citations.extend(matches)
    
    return list(set(citations))  # Remove duplicates

def chunk_text(text: str, chunk_size: int = 1000, overlap: int = 200) -> List[str]:
    """
    Split text into overlapping chunks
    """
    if len(text) <= chunk_size:
        return [text]
    
    chunks = []
    start = 0
    
    while start < len(text):
        end = start + chunk_size
        
        # Try to break at sentence boundary
        if end < len(text):
            # Look for sentence ending within last 100 chars
            sentence_end = text.rfind('.', start + chunk_size - 100, end)
            if sentence_end > start:
                end = sentence_end + 1
        
        chunk = text[start:end].strip()
        if chunk:
            chunks.append(chunk)
        
        start = end - overlap
        if start >= len(text):
            break
    
    return chunks

def extract_keywords(text: str, max_keywords: int = 10) -> List[str]:
    """
    Extract keywords dari text (simple implementation)
    """
    # Remove common stop words
    stop_words = {
        'dan', 'atau', 'yang', 'untuk', 'dengan', 'dari', 'ke', 'di', 'pada', 'dalam',
        'adalah', 'akan', 'dapat', 'bisa', 'harus', 'sudah', 'belum', 'tidak', 'juga',
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of',
        'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had'
    }
    
    # Extract words
    words = re.findall(r'\b[a-zA-Z]{3,}\b', text.lower())
    
    # Filter out stop words and count frequency
    word_freq = {}
    for word in words:
        if word not in stop_words:
            word_freq[word] = word_freq.get(word, 0) + 1
    
    # Sort by frequency and return top keywords
    keywords = sorted(word_freq.items(), key=lambda x: x[1], reverse=True)
    return [word for word, freq in keywords[:max_keywords]]

def format_for_display(text: str, max_length: int = None) -> str:
    """
    Format text untuk display di UI
    """
    text = clean_text(text)
    
    if max_length and len(text) > max_length:
        # Truncate at word boundary
        truncated = text[:max_length]
        last_space = truncated.rfind(' ')
        if last_space > max_length * 0.8:  # Don't cut too much
            truncated = truncated[:last_space]
        text = truncated + "..."
    
    return text

def extract_questions(text: str) -> List[str]:
    """
    Extract questions dari text untuk suggestions
    """
    # Pattern untuk mencari kalimat tanya
    question_patterns = [
        r'[A-Z][^?]*\?',  # Sentences ending with ?
        r'Bagaimana [^?]*\?',  # How questions
        r'Apa [^?]*\?',  # What questions
        r'Mengapa [^?]*\?',  # Why questions
        r'Kapan [^?]*\?',  # When questions
        r'Dimana [^?]*\?',  # Where questions
    ]
    
    questions = []
    for pattern in question_patterns:
        matches = re.findall(pattern, text, re.IGNORECASE)
        questions.extend(matches)
    
    # Clean dan limit questions
    cleaned_questions = []
    for q in questions:
        q = clean_text(q)
        if len(q) > 10 and len(q) < 200:  # Reasonable length
            cleaned_questions.append(q)
    
    return cleaned_questions[:5]  # Limit to 5 questions

def calculate_text_similarity(text1: str, text2: str) -> float:
    """
    Simple text similarity calculation (Jaccard similarity)
    """
    if not text1 or not text2:
        return 0.0
    
    # Convert to sets of words
    words1 = set(text1.lower().split())
    words2 = set(text2.lower().split())
    
    # Calculate Jaccard similarity
    intersection = words1.intersection(words2)
    union = words1.union(words2)
    
    if not union:
        return 0.0
    
    return len(intersection) / len(union)

def highlight_keywords(text: str, keywords: List[str]) -> str:
    """
    Highlight keywords dalam text untuk display
    """
    if not keywords:
        return text
    
    highlighted_text = text
    for keyword in keywords:
        pattern = re.compile(re.escape(keyword), re.IGNORECASE)
        highlighted_text = pattern.sub(f"**{keyword}**", highlighted_text)
    
    return highlighted_text