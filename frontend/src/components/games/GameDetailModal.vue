<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
      <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-3xl max-h-[92vh] flex flex-col">
        <!-- Header image -->
        <div class="relative h-44 rounded-t-2xl overflow-hidden bg-dark-700 flex-shrink-0">
          <img
            v-if="headerImage"
            :src="headerImage"
            :alt="game.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="w-16 h-16 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
            </svg>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
          <div class="absolute bottom-3 left-5 right-5 flex items-end justify-between">
            <div>
              <h2 class="text-xl font-bold text-white">{{ game.name }}</h2>
              <div class="flex gap-2 mt-1">
                <span v-if="game.genre" class="badge bg-dark-600/80 text-gray-300 text-xs">{{ game.genre }}</span>
                <span v-if="game.engine" class="badge bg-dark-600/80 text-gray-300 text-xs">{{ game.engine }}</span>
                <span class="badge text-xs" :class="statusColor(game.status)">{{ statusLabel(game.status) }}</span>
              </div>
            </div>
            <div class="flex gap-2">
              <button @click="$emit('edit')" class="btn-secondary text-xs py-1.5">Editar</button>
              <button @click="$emit('close')" class="bg-dark-700/80 rounded-lg p-1.5 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-5">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Left column -->
            <div class="md:col-span-2 space-y-5">
              <!-- Description -->
              <div v-if="game.description || game.steam_data?.detailed_description">
                <h4 class="text-sm font-semibold text-gray-300 mb-2">Sobre o jogo</h4>
                <p class="text-sm text-gray-400 leading-relaxed">
                  {{ game.description || cleanHtml(game.steam_data?.short_description) }}
                </p>
              </div>

              <!-- Steam Stats -->
              <div v-if="game.steam_data">
                <h4 class="text-sm font-semibold text-gray-300 mb-3 flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                  Dados da Steam
                  <span v-if="game.steam_data_updated_at" class="text-xs text-gray-500 font-normal">
                    (atualizado {{ formatRelative(game.steam_data_updated_at) }})
                  </span>
                </h4>

                <div class="grid grid-cols-3 gap-3 mb-4">
                  <div class="bg-dark-700 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ formatNum(game.steam_data.recommendations?.total) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Reviews</p>
                  </div>
                  <div class="bg-dark-700 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ game.steam_data.price_overview?.final_formatted || 'Grátis' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Preço (BR)</p>
                  </div>
                  <div class="bg-dark-700 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold" :class="scoreColor(game.steam_data.metacritic?.score)">
                      {{ game.steam_data.metacritic?.score || '—' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Metacritic</p>
                  </div>
                </div>

                <!-- Categories & Genres -->
                <div v-if="game.steam_data.genres?.length" class="flex flex-wrap gap-2 mb-2">
                  <span v-for="g in game.steam_data.genres" :key="g.id" class="badge bg-primary-600/15 text-primary-400 text-xs">{{ g.description }}</span>
                </div>

                <!-- Screenshots -->
                <div v-if="game.steam_data.screenshots?.length" class="mt-3">
                  <h5 class="text-xs font-medium text-gray-400 mb-2">Screenshots</h5>
                  <div class="flex gap-2 overflow-x-auto pb-2">
                    <img
                      v-for="ss in game.steam_data.screenshots.slice(0, 6)"
                      :key="ss.id"
                      :src="ss.path_thumbnail"
                      class="h-20 rounded-lg flex-shrink-0 object-cover cursor-pointer hover:opacity-80 transition-opacity"
                      @click="openScreenshot(ss.path_full)"
                    />
                  </div>
                </div>
              </div>

              <!-- No Steam Data -->
              <div v-else-if="game.steam_app_id" class="bg-dark-700/50 rounded-xl p-4 text-center border border-dashed border-gray-600">
                <p class="text-gray-400 text-sm">Dados da Steam ainda não carregados</p>
                <p class="text-gray-500 text-xs mt-1">App ID: {{ game.steam_app_id }}</p>
              </div>
            </div>

            <!-- Right column -->
            <div class="space-y-4">
              <!-- Platform Links -->
              <div>
                <h4 class="text-sm font-semibold text-gray-300 mb-3">Plataformas</h4>
                <div class="space-y-2">
                  <a v-if="game.steam_url" :href="game.steam_url" target="_blank"
                    class="flex items-center gap-3 p-2.5 rounded-lg bg-blue-600/10 border border-blue-600/20 hover:bg-blue-600/20 transition-colors">
                    <div class="w-6 h-6 rounded flex items-center justify-center bg-blue-500/20">
                      <span class="text-blue-400 text-xs font-bold">S</span>
                    </div>
                    <span class="text-sm text-blue-400">Steam</span>
                    <svg class="w-3 h-3 text-gray-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                  <a v-if="game.itch_url" :href="game.itch_url" target="_blank"
                    class="flex items-center gap-3 p-2.5 rounded-lg bg-red-600/10 border border-red-600/20 hover:bg-red-600/20 transition-colors">
                    <div class="w-6 h-6 rounded flex items-center justify-center bg-red-500/20">
                      <span class="text-red-400 text-xs font-bold">i</span>
                    </div>
                    <span class="text-sm text-red-400">itch.io</span>
                    <svg class="w-3 h-3 text-gray-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                  <a v-if="game.android_url" :href="game.android_url" target="_blank"
                    class="flex items-center gap-3 p-2.5 rounded-lg bg-green-600/10 border border-green-600/20 hover:bg-green-600/20 transition-colors">
                    <div class="w-6 h-6 rounded flex items-center justify-center bg-green-500/20">
                      <span class="text-green-400 text-xs font-bold">G</span>
                    </div>
                    <span class="text-sm text-green-400">Google Play</span>
                    <svg class="w-3 h-3 text-gray-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                  <a v-if="game.website_url" :href="game.website_url" target="_blank"
                    class="flex items-center gap-3 p-2.5 rounded-lg bg-gray-600/10 border border-gray-600/20 hover:bg-gray-600/20 transition-colors">
                    <div class="w-6 h-6 rounded flex items-center justify-center bg-gray-500/20">
                      <span class="text-gray-400 text-xs font-bold">W</span>
                    </div>
                    <span class="text-sm text-gray-300">Website</span>
                    <svg class="w-3 h-3 text-gray-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>

                  <!-- Custom links -->
                  <a
                    v-for="link in game.custom_links"
                    :key="link.url"
                    :href="link.url"
                    target="_blank"
                    class="flex items-center gap-3 p-2.5 rounded-lg bg-primary-600/10 border border-primary-600/20 hover:bg-primary-600/20 transition-colors"
                  >
                    <span class="text-sm text-primary-300">{{ link.label }}</span>
                    <svg class="w-3 h-3 text-gray-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>

              <!-- Tags -->
              <div v-if="game.tags?.length">
                <h4 class="text-sm font-semibold text-gray-300 mb-2">Tags</h4>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="tag in game.tags" :key="tag" class="badge bg-dark-600 text-gray-400 text-xs">{{ tag }}</span>
                </div>
              </div>

              <!-- Release date -->
              <div v-if="game.release_date">
                <h4 class="text-sm font-semibold text-gray-300 mb-1">Lançamento</h4>
                <p class="text-sm text-gray-400">{{ formatDate(game.release_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Screenshot viewer -->
        <div v-if="screenshotUrl" class="fixed inset-0 bg-black/90 flex items-center justify-center z-[60] p-4 cursor-zoom-out" @click="screenshotUrl = null">
          <img :src="screenshotUrl" class="max-w-full max-h-full rounded-lg" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Game } from '@/stores/games'

const props = defineProps<{ game: Game }>()
defineEmits<{ close: []; edit: [] }>()

const screenshotUrl = ref<string | null>(null)

const headerImage = computed(() => {
  if (props.game.steam_data?.header_image) return props.game.steam_data.header_image
  if (props.game.steam_app_id) return `https://cdn.akamai.steamstatic.com/steam/apps/${props.game.steam_app_id}/header.jpg`
  return null
})

function openScreenshot(url: string) {
  screenshotUrl.value = url
}

function cleanHtml(html: string) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}

function formatNum(n: number) {
  if (!n) return '0'
  if (n >= 1000) return (n / 1000).toFixed(1) + 'K'
  return n.toString()
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

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' })
}

function formatRelative(d: string) {
  const diff = Date.now() - new Date(d).getTime()
  const hours = Math.floor(diff / 3_600_000)
  if (hours < 1) return 'agora mesmo'
  if (hours < 24) return `há ${hours}h`
  return `há ${Math.floor(hours / 24)} dias`
}
</script>
