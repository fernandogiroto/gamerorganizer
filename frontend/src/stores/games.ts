import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export interface Game {
  id: number
  name: string
  description: string | null
  cover_image: string | null
  genre: string | null
  engine: string | null
  status: 'development' | 'beta' | 'released' | 'abandoned'
  release_date: string | null
  steam_url: string | null
  steam_app_id: string | null
  itch_url: string | null
  epic_url: string | null
  gog_url: string | null
  android_url: string | null
  ios_url: string | null
  website_url: string | null
  steam_data: any | null
  steam_data_updated_at: string | null
  custom_links: { label: string; url: string }[] | null
  tags: string[] | null
}

export const useGameStore = defineStore('games', () => {
  const games = ref<Game[]>([])
  const loading = ref(false)

  async function fetchGames() {
    loading.value = true
    const { data } = await api.get('/games')
    games.value = data
    loading.value = false
  }

  async function createGame(payload: Partial<Game>) {
    const { data } = await api.post('/games', payload)
    games.value.push(data)
    return data
  }

  async function updateGame(id: number, payload: Partial<Game>) {
    const { data } = await api.put(`/games/${id}`, payload)
    const idx = games.value.findIndex(g => g.id === id)
    if (idx !== -1) games.value[idx] = data
    return data
  }

  async function deleteGame(id: number) {
    await api.delete(`/games/${id}`)
    games.value = games.value.filter(g => g.id !== id)
  }

  async function refreshSteamData(id: number) {
    const { data } = await api.post(`/games/${id}/refresh-steam`)
    const idx = games.value.findIndex(g => g.id === id)
    if (idx !== -1) games.value[idx] = data
    return data
  }

  async function searchSteam(term: string) {
    const { data } = await api.get('/games/steam-search', { params: { term } })
    return data
  }

  return { games, loading, fetchGames, createGame, updateGame, deleteGame, refreshSteamData, searchSteam }
})
