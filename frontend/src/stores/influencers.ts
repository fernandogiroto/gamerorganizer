import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export interface Influencer {
  id: number
  name: string
  type: string
  channel_url: string | null
  email: string | null
  instagram: string | null
  twitter: string | null
  discord: string | null
  country: string
  language: string
  niche: string | null
  subscribers: number
  status: 'prospect' | 'contacted' | 'negotiating' | 'active' | 'inactive'
  notes: string | null
  avg_views: number | null
  engagement_rate: number | null
  last_contact_date: string | null
  games_covered: string[] | null
  tags: string[] | null
}

export const useInfluencerStore = defineStore('influencers', () => {
  const influencers = ref<Influencer[]>([])
  const pagination = ref<any>(null)
  const stats = ref<any>(null)
  const loading = ref(false)

  async function fetchInfluencers(filters: Record<string, any> = {}) {
    loading.value = true
    const { data } = await api.get('/influencers', { params: filters })
    influencers.value = data.data
    pagination.value = data
    loading.value = false
  }

  async function fetchStats() {
    const { data } = await api.get('/influencers/stats')
    stats.value = data
    return data
  }

  async function createInfluencer(payload: Partial<Influencer>) {
    const { data } = await api.post('/influencers', payload)
    influencers.value.unshift(data)
    return data
  }

  async function updateInfluencer(id: number, payload: Partial<Influencer>) {
    const { data } = await api.put(`/influencers/${id}`, payload)
    const idx = influencers.value.findIndex(i => i.id === id)
    if (idx !== -1) influencers.value[idx] = data
    return data
  }

  async function deleteInfluencer(id: number) {
    await api.delete(`/influencers/${id}`)
    influencers.value = influencers.value.filter(i => i.id !== id)
  }

  return { influencers, pagination, stats, loading, fetchInfluencers, fetchStats, createInfluencer, updateInfluencer, deleteInfluencer }
})
