<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-700/50 bg-dark-800/50">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="page-title">Marketing</h1>
          <p class="text-gray-400 text-sm mt-1">Gerencie youtubers, influenciadores e parcerias</p>
        </div>
        <button @click="openModal()" class="btn-primary">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Influenciador
        </button>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4" v-if="store.stats">
        <div class="bg-dark-700/50 rounded-xl p-3 border border-gray-700/50">
          <p class="text-xs text-gray-400">Total</p>
          <p class="text-xl font-bold text-white">{{ store.stats.total }}</p>
        </div>
        <div class="bg-dark-700/50 rounded-xl p-3 border border-gray-700/50">
          <p class="text-xs text-gray-400">Alcance total</p>
          <p class="text-xl font-bold text-primary-400">{{ formatNumber(store.stats.total_reach) }}</p>
        </div>
        <div class="bg-dark-700/50 rounded-xl p-3 border border-gray-700/50">
          <p class="text-xs text-gray-400">Ativos</p>
          <p class="text-xl font-bold text-green-400">{{ store.stats.by_status?.active || 0 }}</p>
        </div>
        <div class="bg-dark-700/50 rounded-xl p-3 border border-gray-700/50">
          <p class="text-xs text-gray-400">Em negociação</p>
          <p class="text-xl font-bold text-yellow-400">{{ store.stats.by_status?.negotiating || 0 }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-3 border-b border-gray-700/50 flex flex-wrap gap-3 bg-dark-800/30">
      <input v-model="filters.search" type="text" class="input text-sm w-56" placeholder="Buscar por nome, nicho..." @input="debounceSearch" />

      <select v-model="filters.type" class="input text-sm w-36" @change="fetchData">
        <option value="">Todos os tipos</option>
        <option value="youtuber">YouTube</option>
        <option value="streamer">Streamer</option>
        <option value="instagram">Instagram</option>
        <option value="tiktok">TikTok</option>
        <option value="blogger">Blog</option>
        <option value="podcast">Podcast</option>
      </select>

      <select v-model="filters.country" class="input text-sm w-44" @change="fetchData">
        <option value="">Todos os países</option>
        <option value="Brazil">🇧🇷 Brasil</option>
        <option value="Portugal">🇵🇹 Portugal</option>
        <option value="United States">🇺🇸 Estados Unidos</option>
        <option value="Argentina">🇦🇷 Argentina</option>
        <option value="Japan">🇯🇵 Japão</option>
        <option value="South Korea">🇰🇷 Coreia do Sul</option>
        <option value="China">🇨🇳 China</option>
        <option value="France">🇫🇷 França</option>
      </select>

      <select v-model="filters.status" class="input text-sm w-40" @change="fetchData">
        <option value="">Todos os status</option>
        <option value="prospect">Prospect</option>
        <option value="contacted">Contactado</option>
        <option value="negotiating">Negociando</option>
        <option value="active">Ativo</option>
        <option value="inactive">Inativo</option>
      </select>

      <button @click="clearFilters" class="btn-ghost text-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Limpar
      </button>
    </div>

    <!-- Table -->
    <div class="flex-1 overflow-auto px-6 py-4">
      <div v-if="store.loading" class="flex items-center justify-center h-48">
        <div class="animate-spin w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full"></div>
      </div>

      <div v-else-if="store.influencers.length === 0" class="flex flex-col items-center justify-center h-48 text-center">
        <p class="text-gray-400">Nenhum influenciador encontrado</p>
        <button @click="openModal()" class="btn-primary mt-3">Adicionar primeiro</button>
      </div>

      <div v-else>
        <table class="w-full">
          <thead>
            <tr class="text-left border-b border-gray-700/50">
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Nome</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Tipo</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Nicho</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Inscritos</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">País</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
              <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700/30">
            <tr
              v-for="inf in store.influencers"
              :key="inf.id"
              class="hover:bg-white/3 transition-colors group"
            >
              <td class="py-3 pr-4">
                <div class="flex items-center gap-3">
                  <div class="relative w-8 h-8 flex-shrink-0">
                    <img
                      v-if="avatarUrl(inf)"
                      :src="avatarUrl(inf)"
                      :alt="inf.name"
                      class="w-8 h-8 rounded-full object-cover"
                      @error="(e) => { (e.target as HTMLImageElement).style.display = 'none'; (e.target as HTMLImageElement).nextElementSibling?.removeAttribute('style') }"
                    />
                    <div
                      class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 flex items-center justify-center text-white text-xs font-bold absolute inset-0"
                      :style="avatarUrl(inf) ? 'display:none' : ''"
                    >
                      {{ inf.name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-white">{{ inf.name }}</p>
                    <p v-if="inf.email" class="text-xs text-gray-500">{{ inf.email }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 pr-4">
                <span class="badge" :class="typeColor(inf.type)">{{ inf.type }}</span>
              </td>
              <td class="py-3 pr-4 text-sm text-gray-300">{{ inf.niche || '-' }}</td>
              <td class="py-3 pr-4 text-sm text-gray-300">{{ formatNumber(inf.subscribers) }}</td>
              <td class="py-3 pr-4 text-sm text-gray-300">{{ inf.country }}</td>
              <td class="py-3 pr-4">
                <span class="badge" :class="statusColor(inf.status)">{{ statusLabel(inf.status) }}</span>
              </td>
              <td class="py-3">
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openModal(inf)" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="handleDelete(inf)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                  <a v-if="inf.channel_url" :href="inf.channel_url" target="_blank" class="p-1.5 rounded-lg text-gray-400 hover:text-primary-400 hover:bg-primary-500/10">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1" class="flex items-center justify-between mt-4 pt-4 border-t border-gray-700/50">
          <p class="text-sm text-gray-400">
            {{ store.pagination.from }}–{{ store.pagination.to }} de {{ store.pagination.total }}
          </p>
          <div class="flex gap-2">
            <button
              :disabled="store.pagination.current_page === 1"
              @click="goToPage(store.pagination.current_page - 1)"
              class="btn-secondary text-sm py-1.5 px-3 disabled:opacity-40"
            >Anterior</button>
            <button
              :disabled="store.pagination.current_page === store.pagination.last_page"
              @click="goToPage(store.pagination.current_page + 1)"
              class="btn-secondary text-sm py-1.5 px-3 disabled:opacity-40"
            >Próximo</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <InfluencerModal
      v-if="showModal"
      :influencer="editingInfluencer"
      @close="showModal = false"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useInfluencerStore, type Influencer } from '@/stores/influencers'
import InfluencerModal from '@/components/marketing/InfluencerModal.vue'

const store = useInfluencerStore()
const showModal = ref(false)
const editingInfluencer = ref<Influencer | null>(null)
let searchTimeout: ReturnType<typeof setTimeout>

const filters = ref({ search: '', type: '', country: '', status: '', page: 1 })

onMounted(async () => {
  await Promise.all([store.fetchInfluencers(), store.fetchStats()])
})

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchData, 400)
}

async function fetchData() {
  filters.value.page = 1
  await store.fetchInfluencers(filters.value)
}

async function goToPage(page: number) {
  filters.value.page = page
  await store.fetchInfluencers(filters.value)
}

function clearFilters() {
  filters.value = { search: '', type: '', country: '', status: '', page: 1 }
  store.fetchInfluencers()
}

function openModal(influencer?: Influencer) {
  editingInfluencer.value = influencer || null
  showModal.value = true
}

async function handleSaved() {
  showModal.value = false
  await store.fetchInfluencers(filters.value)
  await store.fetchStats()
}

async function handleDelete(inf: Influencer) {
  if (confirm(`Deletar "${inf.name}"?`)) {
    await store.deleteInfluencer(inf.id)
    await store.fetchStats()
  }
}

function avatarUrl(inf: Influencer): string | null {
  // Extract handle from channel_url for YouTube
  if (inf.type === 'youtuber' && inf.channel_url) {
    const match = inf.channel_url.match(/youtube\.com\/@?([^/?]+)/)
    if (match) return `https://unavatar.io/youtube/${match[1]}`
  }
  if (inf.instagram) {
    const handle = inf.instagram.replace(/^@/, '').replace(/.*instagram\.com\//, '')
    if (handle) return `https://unavatar.io/instagram/${handle}`
  }
  if (inf.twitter) {
    const handle = inf.twitter.replace(/^@/, '').replace(/.*twitter\.com\//, '').replace(/.*x\.com\//, '')
    if (handle) return `https://unavatar.io/twitter/${handle}`
  }
  if (inf.type === 'youtuber' && inf.channel_url) {
    const match = inf.channel_url.match(/youtube\.com\/(?:c\/|channel\/|user\/)?([^/?]+)/)
    if (match) return `https://unavatar.io/youtube/${match[1]}`
  }
  return null
}

function formatNumber(n: number) {
  if (!n) return '0'
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M'
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K'
  return n.toString()
}

function typeColor(type: string) {
  const map: Record<string, string> = {
    youtuber: 'bg-red-600/20 text-red-400',
    streamer: 'bg-purple-600/20 text-purple-400',
    instagram: 'bg-pink-600/20 text-pink-400',
    tiktok: 'bg-cyan-600/20 text-cyan-400',
    blogger: 'bg-yellow-600/20 text-yellow-400',
    podcast: 'bg-green-600/20 text-green-400',
  }
  return map[type] || 'bg-gray-600/20 text-gray-400'
}

function statusColor(status: string) {
  const map: Record<string, string> = {
    prospect: 'bg-gray-600/20 text-gray-400',
    contacted: 'bg-blue-600/20 text-blue-400',
    negotiating: 'bg-yellow-600/20 text-yellow-400',
    active: 'bg-green-600/20 text-green-400',
    inactive: 'bg-red-600/20 text-red-400',
  }
  return map[status] || 'bg-gray-600/20 text-gray-400'
}

function statusLabel(status: string) {
  const map: Record<string, string> = {
    prospect: 'Prospect', contacted: 'Contactado', negotiating: 'Negociando',
    active: 'Ativo', inactive: 'Inativo',
  }
  return map[status] || status
}
</script>
