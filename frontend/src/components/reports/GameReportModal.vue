<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
      <!-- Backdrop -->
      <div class="absolute inset-0" style="background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);"></div>

      <!-- Modal -->
      <div
        class="relative w-full max-w-5xl max-h-[90vh] flex flex-col rounded-2xl overflow-hidden"
        style="background:#0d0d1a;border:1px solid rgba(255,255,255,0.08);box-shadow:0 24px 80px rgba(0,0,0,0.6);"
      >
        <!-- Header -->
        <div class="flex items-center gap-4 px-6 py-4 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.07);">
          <div v-if="coverImage" class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 bg-white/5">
            <img :src="coverImage" :alt="game.name" class="w-full h-full object-cover" />
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-white truncate">{{ game.name }}</h2>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :style="statusStyle(game.status)">{{ game.status }}</span>
              <span v-if="game.genre" class="text-xs text-white/40">{{ game.genre }}</span>
              <span v-if="game.engine" class="text-xs text-white/30">· {{ game.engine }}</span>
            </div>
          </div>
          <button @click="$emit('close')" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex-1 flex items-center justify-center py-20">
          <div class="flex flex-col items-center gap-3">
            <div class="w-10 h-10 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white/40 text-sm">Carregando relatório...</p>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="flex-1 flex items-center justify-center py-20">
          <p class="text-red-400 text-sm">{{ error }}</p>
        </div>

        <template v-else-if="report">
          <!-- Platform Tabs -->
          <div class="flex-shrink-0 px-6 pt-4" style="border-bottom:1px solid rgba(255,255,255,0.07);">
            <div class="flex gap-1 overflow-x-auto pb-0.5">
              <button
                v-for="tab in availableTabs"
                :key="tab.key"
                @click="activeTab = tab.key"
                class="flex items-center gap-1.5 px-4 py-2 rounded-t-lg text-sm font-medium transition-all flex-shrink-0"
                :style="activeTab === tab.key
                  ? 'background:rgba(124,58,237,0.15);color:#a78bfa;border:1px solid rgba(124,58,237,0.25);border-bottom:none;'
                  : 'color:rgba(255,255,255,0.4);border:1px solid transparent;'"
              >
                <span v-html="tab.icon" style="width:14px;height:14px;display:inline-flex;align-items:center;"></span>
                {{ tab.label }}
              </button>
            </div>
          </div>

          <!-- Tab Content -->
          <div class="flex-1 overflow-y-auto">
            <!-- STEAM TAB -->
            <template v-if="activeTab === 'steam' && report.platforms.steam">
              <SteamReport :data="report.platforms.steam" />
            </template>

            <!-- OTHER PLATFORMS -->
            <template v-else-if="activeTab !== 'steam'">
              <SimplePlatformReport :data="report.platforms[activeTab]" />
            </template>
          </div>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import SteamReport from './SteamReport.vue'
import SimplePlatformReport from './SimplePlatformReport.vue'

const props = defineProps<{
  game: { id: number; name: string; status: string; genre?: string; engine?: string; cover_image?: string; steam_data?: any }
}>()
defineEmits(['close'])

const loading = ref(true)
const error = ref('')
const report = ref<any>(null)
const activeTab = ref('')

const coverImage = computed(() =>
  props.game.cover_image || props.game.steam_data?.header_image || null
)

const tabIcons: Record<string, string> = {
  steam:   `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.7 3.98 10.49 9.35 11.67L12.5 17a3.5 3.5 0 004.9-4.29l4.45-3.21C21.93 8.7 21.25 0 12 0zm-3.5 17.5a2 2 0 110-4 2 2 0 010 4zm7-5.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>`,
  itchio:  `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M3.13 1.34C2.04 1.97 0 4.17 0 4.58v1.07c0 1.33 1.24 2.5 2.38 2.5 1.37 0 2.5-1.14 2.5-2.5 0 1.36 1.12 2.5 2.49 2.5 1.37 0 2.5-1.14 2.5-2.5 0 1.36 1.13 2.5 2.5 2.5s2.5-1.14 2.5-2.5c0 1.36 1.12 2.5 2.49 2.5 1.37 0 2.5-1.14 2.5-2.5 0 1.36 1.12 2.5 2.49 2.5 1.14 0 2.38-1.17 2.38-2.5V4.58c0-.41-2.04-2.61-3.13-3.24-3.39-.2-6.5-.28-9.5-.28-3 0-5.44.08-7.87.28zM8.5 9.5c-.42.54-.67 1.22-.67 1.97v7.78c-1.78.35-3.28.73-4.42 1.25C1.77 21.17 1 21.86 1 22.5 1 23.33 2.87 24 8.5 24s7.5-.67 7.5-1.5c0-.64-.77-1.33-2.41-2-1.14-.52-2.64-.9-4.42-1.25v-7.78c0-.75-.25-1.43-.67-1.97z"/></svg>`,
  epic:    `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M0 0v24h24V0H0zm5.76 5.32h12.48v2.88H13.2v10.48h-2.4V8.2H5.76V5.32z"/></svg>`,
  gog:     `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm4.5 16.5h-9v-9h9v9z"/></svg>`,
  android: `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M17.523 15.341A6.5 6.5 0 0012 9a6.5 6.5 0 00-5.523 6.341L3 16.5v1h18v-1l-3.477-1.159zM8.5 7.5L7 5.5m9 2L14.5 5.5M8.5 9a1 1 0 110-2 1 1 0 010 2zm7 0a1 1 0 110-2 1 1 0 010 2z"/></svg>`,
  ios:     `<svg fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>`,
  website: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>`,
}

const tabLabels: Record<string, string> = {
  steam: 'Steam', itchio: 'itch.io', epic: 'Epic Games', gog: 'GOG',
  android: 'Google Play', ios: 'App Store', website: 'Website',
}

const availableTabs = computed(() => {
  if (!report.value) return []
  return Object.keys(report.value.platforms).map(k => ({
    key: k,
    label: tabLabels[k] || k,
    icon: tabIcons[k] || '',
  }))
})

function statusStyle(status: string) {
  const map: Record<string, string> = {
    released:    'background:rgba(34,197,94,0.15);color:#4ade80;',
    'in-development': 'background:rgba(234,179,8,0.15);color:#facc15;',
    alpha:       'background:rgba(249,115,22,0.15);color:#fb923c;',
    beta:        'background:rgba(59,130,246,0.15);color:#60a5fa;',
    cancelled:   'background:rgba(239,68,68,0.15);color:#f87171;',
  }
  return map[status] || 'background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.5);'
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/reports/${props.game.id}`)
    report.value = data
    const platforms = Object.keys(data.platforms)
    if (platforms.length > 0) activeTab.value = platforms[0]
  } catch (e: any) {
    error.value = 'Erro ao carregar relatório.'
  } finally {
    loading.value = false
  }
})
</script>
