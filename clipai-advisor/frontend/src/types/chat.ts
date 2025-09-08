export interface SourceDocument {
  title: string
  type: string
  snippet: string
  relevance_score?: number
}

export interface ChatMessage {
  id: string
  role: 'user' | 'assistant' | 'system'
  content: string
  timestamp: Date
  sources?: SourceDocument[]
  suggestions?: string[]
  confidence?: number
  metadata?: Record<string, any>
}

export interface ChatRequest {
  message: string
  conversation_id?: string | null
  user_context?: Record<string, any>
}

export interface ChatResponse {
  message: string
  sources: SourceDocument[]
  confidence: number
  suggestions: string[]
  conversation_id?: string | null
  timestamp: string
}

export interface ConversationSummary {
  conversation_id: string
  title: string
  last_message: string
  message_count: number
  created_at: string
  updated_at: string
}

export interface ConversationHistory {
  conversation_id: string
  messages: ChatMessage[]
  created_at: string
  updated_at: string
  user_id?: string | null
  title?: string | null
}