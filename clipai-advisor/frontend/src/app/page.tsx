import ChatInterface from '@/components/ChatInterface'

export default function Home() {
  return (
    <main className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8">
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            ClipAI Advisor 🎬
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            AI Assistant untuk Agensi Kliping - Dapatkan insights dari pengetahuan kvmedia 
            tentang mentalitas, proyek, affiliate marketing, dan strategi bisnis kliping.
          </p>
        </div>
        
        <ChatInterface />
        
        <div className="mt-12 text-center">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div className="bg-white p-6 rounded-lg shadow-md">
              <div className="text-2xl mb-3">🧠</div>
              <h3 className="font-semibold text-gray-900 mb-2">Mentalitas</h3>
              <p className="text-gray-600 text-sm">
                The Everything, The Doctor, dan mindset sukses untuk agensi kliping
              </p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-md">
              <div className="text-2xl mb-3">💼</div>
              <h3 className="font-semibold text-gray-900 mb-2">Bisnis</h3>
              <p className="text-gray-600 text-sm">
                Strategi proyek, pitching, networking, dan client management
              </p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-md">
              <div className="text-2xl mb-3">💰</div>
              <h3 className="font-semibold text-gray-900 mb-2">Monetisasi</h3>
              <p className="text-gray-600 text-sm">
                Affiliate marketing, passive income, dan scaling strategies
              </p>
            </div>
          </div>
          
          <div className="mt-8 text-sm text-gray-500">
            Powered by RAG AI • Knowledge base dari kvmedia • 
            <a href="#" className="text-blue-600 hover:underline ml-1">
              Konsultasi Agensi
            </a>
          </div>
        </div>
      </div>
    </main>
  )
}