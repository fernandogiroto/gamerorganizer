<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-700/50 bg-dark-800/50">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="page-title">Meus Jogos</h1>
          <p class="text-gray-400 text-sm mt-1">Gerencie seus jogos e acompanhe métricas em todas as plataformas</p>
        </div>
        <button @click="showModal = true" class="btn-primary">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Adicionar Jogo
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex items-center justify-center flex-1">
      <div class="animate-spin w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="store.games.length === 0" class="flex flex-col items-center justify-center flex-1 text-center p-6">
      <div class="w-20 h-20 rounded-2xl bg-primary-600/20 flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
        </svg>
      </div>
      <p class="text-gray-300 text-lg font-medium">Nenhum jogo cadastrado</p>
      <p class="text-gray-500 text-sm mt-1 max-w-sm">Adicione seus jogos para acompanhar métricas da Steam, links de plataformas e muito mais</p>
      <button @click="showModal = true" class="btn-primary mt-4">Adicionar primeiro jogo</button>
    </div>

    <!-- Games Grid -->
    <div v-else class="flex-1 overflow-y-auto p-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="game in store.games"
          :key="game.id"
          class="card hover:border-gray-600 transition-all duration-200 cursor-pointer group"
          @click="selectedGame = game"
        >
          <!-- Cover -->
          <div class="relative mb-4 rounded-lg overflow-hidden bg-dark-700 aspect-video">
            <img
              v-if="steamHeaderImage(game)"
              :src="steamHeaderImage(game)"
              :alt="game.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-12 h-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
              </svg>
            </div>

            <!-- Status badge -->
            <div class="absolute top-2 right-2">
              <span class="badge text-xs" :class="statusColor(game.status)">{{ statusLabel(game.status) }}</span>
            </div>
          </div>

          <!-- Info -->
          <h3 class="font-bold text-white text-base mb-1 group-hover:text-primary-300 transition-colors">{{ game.name }}</h3>

          <div class="flex flex-wrap gap-2 mb-3">
            <span v-if="game.genre" class="badge bg-dark-600 text-gray-300 text-xs">{{ game.genre }}</span>
            <span v-if="game.engine" class="badge bg-dark-600 text-gray-300 text-xs">{{ game.engine }}</span>
          </div>

          <!-- Steam Stats -->
          <div v-if="game.steam_data" class="grid grid-cols-3 gap-2 mb-3 bg-dark-700/50 rounded-lg p-2">
            <div class="text-center">
              <p class="text-lg font-bold text-white">{{ game.steam_data.recommendations?.total?.toLocaleString() || '—' }}</p>
              <p class="text-xs text-gray-400">Reviews</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold text-white">{{ game.steam_data.price_overview ? formatPrice(game.steam_data.price_overview.final_formatted) : 'F2P' }}</p>
              <p class="text-xs text-gray-400">Preço</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold" :class="scoreColor(game.steam_data.metacritic?.score)">
                {{ game.steam_data.metacritic?.score || '—' }}
              </p>
              <p class="text-xs text-gray-400">Metacritic</p>
            </div>
          </div>

          <!-- Platform Links -->
          <div class="flex flex-wrap gap-2">
            <a v-if="game.steam_url" :href="game.steam_url" target="_blank" @click.stop
              class="flex items-center gap-1.5 text-xs bg-blue-600/20 text-blue-400 border border-blue-600/20 px-2.5 py-1 rounded-full hover:bg-blue-600/30 transition-colors">
              Steam
            </a>
            <a v-if="game.itch_url" :href="game.itch_url" target="_blank" @click.stop
              class="flex items-center gap-1.5 text-xs bg-red-600/20 text-red-400 border border-red-600/20 px-2.5 py-1 rounded-full hover:bg-red-600/30 transition-colors">
              itch.io
            </a>
            <a v-if="game.epic_url" :href="game.epic_url" target="_blank" @click.stop
              class="flex items-center gap-1.5 text-xs bg-gray-600/20 text-gray-300 border border-gray-600/20 px-2.5 py-1 rounded-full hover:bg-gray-600/30 transition-colors">
              Epic
            </a>
            <a v-if="game.android_url" :href="game.android_url" target="_blank" @click.stop
              class="flex items-center gap-1.5 text-xs bg-green-600/20 text-green-400 border border-green-600/20 px-2.5 py-1 rounded-full hover:bg-green-600/30 transition-colors">
              Android
            </a>
          </div>

          <!-- Action buttons -->
          <div class="flex gap-2 mt-4 pt-3 border-t border-gray-700/50 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
            <button @click="openEditModal(game)" class="btn-ghost text-xs py-1 px-2 flex-1 justify-center">
              Editar
            </button>
            <button v-if="game.steam_app_id" @click="refreshSteam(game)" class="btn-ghost text-xs py-1 px-2 flex-1 justify-center" :class="[refreshing === game.id ? 'opacity-50' : '']" :disabled="refreshing === game.id">
              {{ refreshing === game.id ? 'Atualizando...' : '↻ Steam' }}
            </button>
            <button @click="handleDelete(game)" class="text-gray-500 hover:text-red-400 p-1.5 rounded-lg hover:bg-red-500/10">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Detail Modal -->
    <GameDetailModal
      v-if="selectedGame"
      :game="selectedGame"
      @close="selectedGame = null"
      @edit="openEditModal(selectedGame!)"
    />

    <!-- Create/Edit Modal -->
    <GameFormModal
      v-if="showModal"
      :game="editingGame"
      @close="showModal = false; editingGame = null"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useGameStore, type Game } from '@/stores/games'
import GameDetailModal from '@/components/games/GameDetailModal.vue'
import GameFormModal from '@/components/games/GameFormModal.vue'

const store = useGameStore()
const showModal = ref(false)
const editingGame = ref<Game | null>(null)
const selectedGame = ref<Game | null>(null)
const refreshing = ref<number | null>(null)

onMounted(() => store.fetchGames())

function steamHeaderImage(game: Game) {
  if (game.steam_data?.header_image) return game.steam_data.header_image
  if (game.steam_app_id) return `https://cdn.akamai.steamstatic.com/steam/apps/${game.steam_app_id}/header.jpg`
  return null
}

function statusColor(status: string) {
  const map: Record<string, string> = {
    development: 'bg-yellow-600/30 text-yellow-400',
    beta: 'bg-blue-600/30 text-blue-400',
    released: 'bg-green-600/30 text-green-400',
    abandoned: 'bg-gray-600/30 text-gray-400',
  }
  return map[status] || 'bg-gray-600/30 text-gray-400'
}

function statusLabel(status: string) {
  const map: Record<string, string> = {
    development: 'Em dev', beta: 'Beta', released: 'Lançado', abandoned: 'Abandonado'
  }
  return map[status] || status
}

function scoreColor(score: number) {
  if (!score) return 'text-gray-400'
  if (score >= 75) return 'text-green-400'
  if (score >= 50) return 'text-yellow-400'
  return 'text-red-400'
}

function formatPrice(price: string) {
  return price
}

function openEditModal(game: Game) {
  selectedGame.value = null
  editingGame.value = game
  showModal.value = true
}

async function refreshSteam(game: Game) {
  refreshing.value = game.id
  try {
    await store.refreshSteamData(game.id)
  } finally {
    refreshing.value = null
  }
}

async function handleDelete(game: Game) {
  if (confirm(`Deletar o jogo "${game.name}"?`)) {
    await store.deleteGame(game.id)
  }
}

async function handleSaved() {
  showModal.value = false
  editingGame.value = null
  await store.fetchGames()
}
</script>
