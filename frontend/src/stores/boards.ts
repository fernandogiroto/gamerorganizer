import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export interface TaskCard {
  id: number
  column_id: number
  user_id: number
  title: string
  description: string | null
  category: string | null
  priority: 'low' | 'medium' | 'high' | 'urgent'
  due_date: string | null
  attachments: { path: string; name: string; url: string }[] | null
  labels: string[] | null
  position: number
  is_completed: boolean
  created_at: string
}

export interface Column {
  id: number
  board_id: number
  name: string
  color: string
  position: number
  task_cards: TaskCard[]
}

export interface Board {
  id: number
  user_id: number
  name: string
  description: string | null
  color: string
  columns: Column[]
}

export const useBoardStore = defineStore('boards', () => {
  const boards = ref<Board[]>([])
  const currentBoard = ref<Board | null>(null)
  const loading = ref(false)

  async function fetchBoards() {
    loading.value = true
    const { data } = await api.get('/boards')
    boards.value = data
    loading.value = false
  }

  async function fetchBoard(id: number) {
    loading.value = true
    const { data } = await api.get(`/boards/${id}`)
    currentBoard.value = data
    loading.value = false
    return data
  }

  async function createBoard(payload: Partial<Board>) {
    const { data } = await api.post('/boards', payload)
    boards.value.push(data)
    return data
  }

  async function updateBoard(id: number, payload: Partial<Board>) {
    const { data } = await api.put(`/boards/${id}`, payload)
    const idx = boards.value.findIndex(b => b.id === id)
    if (idx !== -1) boards.value[idx] = data
    if (currentBoard.value?.id === id) currentBoard.value = data
    return data
  }

  async function deleteBoard(id: number) {
    await api.delete(`/boards/${id}`)
    boards.value = boards.value.filter(b => b.id !== id)
    if (currentBoard.value?.id === id) currentBoard.value = null
  }

  async function createColumn(boardId: number, payload: Partial<Column>) {
    const { data } = await api.post(`/boards/${boardId}/columns`, payload)
    if (currentBoard.value?.id === boardId) {
      currentBoard.value.columns.push({ ...data, task_cards: [] })
    }
    return data
  }

  async function updateColumn(columnId: number, payload: Partial<Column>) {
    const { data } = await api.put(`/columns/${columnId}`, payload)
    if (currentBoard.value) {
      const idx = currentBoard.value.columns.findIndex(c => c.id === columnId)
      if (idx !== -1) currentBoard.value.columns[idx] = { ...currentBoard.value.columns[idx], ...data }
    }
    return data
  }

  async function deleteColumn(columnId: number) {
    await api.delete(`/columns/${columnId}`)
    if (currentBoard.value) {
      currentBoard.value.columns = currentBoard.value.columns.filter(c => c.id !== columnId)
    }
  }

  async function createCard(columnId: number, payload: Partial<TaskCard>) {
    const { data } = await api.post(`/columns/${columnId}/cards`, payload)
    if (currentBoard.value) {
      const col = currentBoard.value.columns.find(c => c.id === columnId)
      if (col) col.task_cards.push(data)
    }
    return data
  }

  async function updateCard(cardId: number, payload: Partial<TaskCard>) {
    const { data } = await api.put(`/cards/${cardId}`, payload)
    if (currentBoard.value) {
      for (const col of currentBoard.value.columns) {
        const idx = col.task_cards.findIndex(c => c.id === cardId)
        if (idx !== -1) {
          col.task_cards[idx] = data
          break
        }
      }
    }
    return data
  }

  async function deleteCard(cardId: number) {
    await api.delete(`/cards/${cardId}`)
    if (currentBoard.value) {
      for (const col of currentBoard.value.columns) {
        col.task_cards = col.task_cards.filter(c => c.id !== cardId)
      }
    }
  }

  async function moveCard(cardId: number, targetColumnId: number, position: number) {
    const { data } = await api.post(`/cards/${cardId}/move`, {
      column_id: targetColumnId,
      position,
    })
    return data
  }

  return {
    boards, currentBoard, loading,
    fetchBoards, fetchBoard, createBoard, updateBoard, deleteBoard,
    createColumn, updateColumn, deleteColumn,
    createCard, updateCard, deleteCard, moveCard,
  }
})
