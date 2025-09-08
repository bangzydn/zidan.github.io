import { NextRequest, NextResponse } from 'next/server'

const BACKEND_URL = process.env.BACKEND_URL || 'http://localhost:8000'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    
    // Forward request to FastAPI backend
    const response = await fetch(`${BACKEND_URL}/api/v1/chat/`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    })

    if (!response.ok) {
      throw new Error(`Backend responded with status: ${response.status}`)
    }

    const data = await response.json()
    
    return NextResponse.json(data)
  } catch (error) {
    console.error('API route error:', error)
    
    // Return fallback response
    return NextResponse.json(
      {
        message: "Maaf, saat ini saya sedang dalam mode demo. Backend RAG system belum fully connected. Namun, saya bisa membantu Anda dengan informasi dasar tentang agensi kliping!\n\n**Contoh pertanyaan yang bisa dijawab:**\n- Bagaimana memulai agensi kliping?\n- Apa itu mentalitas The Everything?\n- Strategi monetisasi untuk creator?\n- Tips networking dan client acquisition\n\n💡 **Demo Response**: Untuk memulai agensi kliping yang sukses, fokus pada mentalitas The Everything - pahami seluruh ekosistem bisnis dari editing hingga client management. Mulai dengan skill editing yang solid, bangun portfolio yang kuat, dan kembangkan sistem untuk scaling.",
        sources: [
          {
            title: "Mentalitas_The_Everything.md",
            type: "md",
            snippet: "Mentalitas The Everything adalah mindset fundamental untuk membangun agensi kliping yang sukses..."
          }
        ],
        confidence: 0.8,
        suggestions: [
          "Bagaimana cara menentukan pricing untuk jasa kliping?",
          "Platform mana yang paling profitable untuk kliping?",
          "Tools editing terbaik untuk pemula?"
        ],
        conversation_id: "demo_conv_" + Date.now(),
        timestamp: new Date().toISOString()
      },
      { status: 200 }
    )
  }
}