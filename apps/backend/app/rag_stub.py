from __future__ import annotations
from typing import List, Optional, Tuple


class RagEngine:
    """Very small in-memory retrieval + templated answer.

    This is a placeholder to be replaced later with FAISS/Pinecone + embeddings.
    """

    def __init__(self) -> None:
        self.documents: List[str] = [
            "Mentalitas The Everything: konsistensi, iterasi cepat, dan pembelajaran berkelanjutan.",
            "Affiliate kliping: fokus pada produk viral, CTA jelas, dan optimasi hook 1-3 detik.",
            "Pitching email: singkat, relevan, cantumkan contoh klip dan impact (CTR/Watch time).",
        ]

    def add_document(self, text: str) -> None:
        if not text:
            return
        self.documents.append(text)

    def answer(self, query_text: str, user_id: Optional[str] = None) -> Tuple[str, List[str]]:
        if not query_text or not query_text.strip():
            return ("Silakan ajukan pertanyaan yang jelas.", [])

        lower_query = query_text.lower()
        matched: List[str] = [doc for doc in self.documents if any(token in doc.lower() for token in lower_query.split())]

        if not matched:
            matched = self.documents[:1]

        guidance = (
            "Berikut ringkasan berdasarkan basis pengetahuan kliping Anda: "
            + " ".join(matched[:2])
        )

        call_to_action = (
            "Jika butuh implementasi, Anda bisa book konsultasi agensi untuk strategi kliping dan monetisasi."
        )

        answer = f"{guidance}\n\nLangkah cepat: 1) Tentukan niche 2) Siapkan template hook 3) Uji 10 klip 4) Ukur CTR/Watch time\n\n{call_to_action}"
        citations = [f"doc://{i}" for i, _ in enumerate(matched[:2])]
        return answer, citations