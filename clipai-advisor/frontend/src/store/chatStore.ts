import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import { ChatMessage } from '@/types/chat'

interface ChatState {
  messages: ChatMessage[]
  currentConversationId: string | null
  isLoading: boolean
  addMessage: (message: ChatMessage) => void
  clearMessages: () => void
  setLoading: (loading: boolean) => void
  setConversationId: (id: string | null) => void
}

export const useChatStore = create<ChatState>()(
  persist(
    (set, get) => ({
      messages: [],
      currentConversationId: null,
      isLoading: false,

      addMessage: (message: ChatMessage) => {
        set((state) => ({
          messages: [...state.messages, message]
        }))
      },

      clearMessages: () => {
        set({
          messages: [],
          currentConversationId: null
        })
      },

      setLoading: (loading: boolean) => {
        set({ isLoading: loading })
      },

      setConversationId: (id: string | null) => {
        set({ currentConversationId: id })
      }
    }),
    {
      name: 'clipai-chat-storage',
      partialize: (state) => ({
        messages: state.messages,
        currentConversationId: state.currentConversationId
      })
    }
  )
)