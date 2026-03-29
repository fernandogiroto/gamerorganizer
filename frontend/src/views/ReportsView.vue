<template>
  <div class="flex flex-col h-full" style="background:#09090f;">

    <!-- Header -->
    <div class="px-6 py-5 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.06);">
      <h1 class="page-title">Reports</h1>
      <p class="page-subtitle">Relatórios detalhados dos teus jogos por plataforma</p>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto px-6 py-6">

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center h-48">
        <div class="flex items-center gap-3" style="color:rgba(255,255,255,0.4);">
          <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          A carregar jogos...
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="games.length === 0" class="flex flex-col items-center justify-center h-48 gap-3">
        <div class="flex items-center justify-center w-16 h-16 rounded-2xl"
          style="background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.2);">
          <svg class="w-8 h-8" style="color:#7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <p class="text-white font-semibold">Nenhum jogo registado</p>
        <p style="color:rgba(255,255,255,0.4);" class="text-sm text-center">
          Adiciona jogos na página <RouterLink to="/games" class="underline" style="color:#a78bfa;">Games</RouterLink> para ver os relatórios aqui.
        </p>
      </div>

      <!-- Game cards grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="game in games"
          :key="game.id"
          @click="openReport(game)"
          class="group cursor-pointer rounded-2xl overflow-hidden transition-all duration-200"
          style="background:#0d0d1a;border:1px solid rgba(255,255,255,0.07);"
          @mouseenter="e => (e.currentTarget as HTMLElement).style.borderColor='rgba(124,58,237,0.3)'"
          @mouseleave="e => (e.currentTarget as HTMLElement).style.borderColor='rgba(255,255,255,0.07)'"
        >
          <!-- Cover -->
          <div class="relative h-40 overflow-hidden" style="background:#111127;">
            <img
              v-if="game.cover_image"
              :src="game.cover_image"
              :alt="game.name"
              class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-12 h-12" style="color:rgba(255,255,255,0.1);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                  d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
              </svg>
            </div>
            <!-- Status badge -->
            <div class="absolute top-2 right-2">
              <span class="badge text-xs font-medium px-2 py-0.5 rounded-full"
                :style="statusStyle(game.status)">
                {{ statusLabel(game.status) }}
              </span>
            </div>
          </div>

          <!-- Info -->
          <div class="p-4">
            <h3 class="font-semibold text-white text-sm truncate">{{ game.name }}</h3>
            <p v-if="game.genre" class="text-xs mt-0.5 truncate" style="color:rgba(255,255,255,0.4);">{{ game.genre }}</p>

            <!-- Platform icons -->
            <div class="flex items-center gap-1.5 mt-3 flex-wrap">
              <span
                v-for="platform in game.platforms"
                :key="platform"
                class="flex items-center gap-1 text-xs px-2 py-1 rounded-lg font-medium"
                :style="platformStyle(platform)"
              >
                <span v-html="platformIcon(platform)" class="flex-shrink-0"></span>
                {{ platformLabel(platform) }}
              </span>
              <span v-if="game.platforms.length === 0" class="text-xs" style="color:rgba(255,255,255,0.25);">
                Sem plataformas
              </span>
            </div>

            <!-- View report button -->
            <div class="mt-3 flex items-center justify-between">
              <span class="text-xs" style="color:rgba(255,255,255,0.35);">
                {{ game.platforms.length }} plataforma{{ game.platforms.length !== 1 ? 's' : '' }}
              </span>
              <span class="text-xs font-medium flex items-center gap-1 transition-all"
                style="color:#a78bfa;">
                Ver relatório
                <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <GameReportModal v-if="selectedGame" :game="selectedGame" @close="selectedGame = null" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import GameReportModal from '@/components/reports/GameReportModal.vue'

interface GameSummary {
  id: number
  name: string
  cover_image: string | null
  status: string
  genre: string | null
  platforms: string[]
}

const games = ref<GameSummary[]>([])
const loading = ref(false)
const selectedGame = ref<GameSummary | null>(null)

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/reports')
    games.value = data
  } finally {
    loading.value = false
  }
})

function openReport(game: GameSummary) {
  selectedGame.value = game
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    development: 'Em Dev', beta: 'Beta', released: 'Lançado', abandoned: 'Abandonado',
  }
  return map[s] ?? s
}

function statusStyle(s: string) {
  const map: Record<string, string> = {
    development: 'background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.25)',
    beta:        'background:rgba(59,130,246,0.15);color:#60a5fa;border:1px solid rgba(59,130,246,0.25)',
    released:    'background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.25)',
    abandoned:   'background:rgba(107,114,128,0.15);color:#9ca3af;border:1px solid rgba(107,114,128,0.25)',
  }
  return map[s] ?? ''
}

function platformLabel(p: string) {
  const map: Record<string, string> = {
    steam: 'Steam', itchio: 'itch.io', epic: 'Epic', gog: 'GOG',
    android: 'Android', ios: 'iOS', website: 'Web',
  }
  return map[p] ?? p
}

function platformStyle(p: string) {
  const map: Record<string, string> = {
    steam:   'background:rgba(27,40,56,0.8);color:#c7d5e0;border:1px solid rgba(99,143,170,0.2)',
    itchio:  'background:rgba(250,95,93,0.12);color:#fa5f5d;border:1px solid rgba(250,95,93,0.2)',
    epic:    'background:rgba(255,255,255,0.06);color:#e5e7eb;border:1px solid rgba(255,255,255,0.1)',
    gog:     'background:rgba(187,0,222,0.12);color:#d946ef;border:1px solid rgba(187,0,222,0.2)',
    android: 'background:rgba(61,220,132,0.12);color:#3ddc84;border:1px solid rgba(61,220,132,0.2)',
    ios:     'background:rgba(255,255,255,0.06);color:#e5e7eb;border:1px solid rgba(255,255,255,0.1)',
    website: 'background:rgba(124,58,237,0.12);color:#a78bfa;border:1px solid rgba(124,58,237,0.2)',
  }
  return map[p] ?? 'background:rgba(255,255,255,0.06);color:#e5e7eb;'
}

function platformIcon(p: string) {
  const icons: Record<string, string> = {
    steam:   `<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M11.979 0C5.678 0 .511 4.86.022 11.037l6.432 2.658c.545-.371 1.203-.59 1.912-.59.063 0 .125.004.188.006l2.861-4.142V8.91c0-2.495 2.028-4.524 4.524-4.524 2.494 0 4.524 2.031 4.524 4.527s-2.03 4.525-4.524 4.525h-.105l-4.076 2.911c0 .052.004.105.004.159 0 1.875-1.515 3.396-3.39 3.396-1.635 0-3.016-1.173-3.331-2.727L.436 15.27C1.862 20.307 6.486 24 11.979 24c6.627 0 11.999-5.373 11.999-12S18.606 0 11.979 0zM7.54 18.21l-1.473-.61c.262.543.714.999 1.314 1.25 1.297.539 2.793-.076 3.332-1.375.263-.63.264-1.319.005-1.949s-.75-1.121-1.377-1.383c-.624-.26-1.29-.249-1.878-.03l1.523.63c.956.4 1.409 1.5 1.009 2.455-.397.957-1.497 1.41-2.455 1.012H7.54zm11.415-9.303c0-1.662-1.353-3.015-3.015-3.015-1.665 0-3.015 1.353-3.015 3.015 0 1.665 1.35 3.015 3.015 3.015 1.663 0 3.015-1.35 3.015-3.015zm-5.273-.005c0-1.252 1.013-2.266 2.265-2.266 1.249 0 2.266 1.014 2.266 2.266 0 1.251-1.017 2.265-2.266 2.265-1.252 0-2.265-1.014-2.265-2.265z"/></svg>`,
    itchio:  `<svg width="12" height="12" fill="currentColor" viewBox="0 0 245 220"><path d="M31.99 0C21.313 0 .066 29.324 0 36v12c0 12.856 11.313 24 22.625 24H24v120c0 6.627 5.373 12 12 12h1c6.627 0 12-5.373 12-12V72h145.64v120c0 6.627 5.373 12 12 12h1c6.627 0 12-5.373 12-12V72h1.375C232.328 72 244 60.856 244 48V36C243.934 29.324 222.687 0 212.01 0H31.99zm0 12h180.02C222.277 12 232 30.664 232 36v12c0 6.84-5.16 12-11.36 12H24.36C18.16 60 13 54.84 13 48V36C13 30.664 22.723 12 31.99 12z"/></svg>`,
    epic:    `<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M22.433 0H1.567C.69 0 0 .689 0 1.567v20.866C0 23.31.689 24 1.567 24h20.866c.878 0 1.567-.689 1.567-1.567V1.567C24 .69 23.311 0 22.433 0zM18 17H6v-1.5h12V17zm0-3.5H6V12h12v1.5zm0-3.5H6V8.5h12V10z"/></svg>`,
    android: `<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.341l1.505-2.606A.864.864 0 0 0 18.274 11.5l-1.505 2.606a.864.864 0 0 0 .754 1.235zm-11.046 0a.864.864 0 0 0 .754-1.235L5.726 11.5a.864.864 0 0 0-.754 1.235l1.505 2.606zM15.5 9.5h-7a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h1v2.5a1 1 0 0 0 2 0V17.5h1V20a1 1 0 0 0 2 0v-2.5h1a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5zM8.5 8h7l1.23-2.133A4.994 4.994 0 0 0 14 5H10a4.994 4.994 0 0 0-2.73.867L8.5 8z"/></svg>`,
    ios:     `<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>`,
    website: `<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>`,
    gog:     `<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 4.5c4.142 0 7.5 3.358 7.5 7.5s-3.358 7.5-7.5 7.5S4.5 16.142 4.5 12 7.858 4.5 12 4.5zm0 2.5c-2.761 0-5 2.239-5 5s2.239 5 5 5 5-2.239 5-5-2.239-5-5-5z"/></svg>`,
  }
  return icons[p] ?? ''
}
</script>
