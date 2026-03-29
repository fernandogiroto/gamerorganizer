<template>
  <div class="p-6 space-y-6">

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <StatCard label="Donos estimados" :value="data.owners || 'N/A'" icon="👥" />
      <StatCard label="Jogadores únicos" :value="fmtPlayers(data.players_forever)" icon="🎮" />
      <StatCard label="Jogadores (2 sem.)" :value="fmtPlayers(data.players_2weeks)" icon="📅" />
      <StatCard label="CCU Máximo" :value="fmtPlayers(data.ccu)" icon="⚡" />
    </div>

    <!-- Review Score -->
    <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-white">Avaliações</h3>
        <a v-if="data.url" :href="data.url" target="_blank" class="text-xs text-violet-400 hover:text-violet-300 flex items-center gap-1">
          Ver na Steam
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>
      <div v-if="data.review_score !== null" class="flex items-center gap-4">
        <div class="text-4xl font-bold" :style="scoreColor(data.review_score)">{{ data.review_score }}%</div>
        <div>
          <p class="text-sm font-medium text-white">{{ data.review_label }}</p>
          <p class="text-xs text-white/40 mt-0.5">{{ fmt(data.review_positive) }} positivas · {{ fmt(data.review_negative) }} negativas · {{ fmt(data.review_total) }} total</p>
        </div>
        <div class="flex-1 ml-4">
          <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.1);">
            <div class="h-full rounded-full transition-all duration-700" :style="`width:${data.review_score}%;background:${scoreBarColor(data.review_score)};`"></div>
          </div>
        </div>
      </div>
      <p v-else class="text-sm text-white/40">Sem avaliações ainda.</p>
    </div>

    <!-- Playtime + Price -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
        <p class="text-xs text-white/40 mb-1">Tempo médio de jogo (total)</p>
        <p class="text-xl font-bold text-white">{{ fmtTime(data.avg_playtime_forever) }}</p>
      </div>
      <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
        <p class="text-xs text-white/40 mb-1">Tempo médio (2 semanas)</p>
        <p class="text-xl font-bold text-white">{{ fmtTime(data.avg_playtime_2weeks) }}</p>
      </div>
      <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
        <p class="text-xs text-white/40 mb-1">Preço</p>
        <p class="text-xl font-bold text-white">{{ fmtPrice(data.price) }}</p>
        <p v-if="data.metacritic" class="text-xs mt-1" style="color:#66c0f4;">Metacritic: {{ data.metacritic.score }}</p>
      </div>
    </div>

    <!-- Country Distribution Chart -->
    <div v-if="data.country_distribution?.length" class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
      <h3 class="text-sm font-semibold text-white mb-4">Distribuição por país (baseado em avaliações)</h3>
      <div class="flex gap-6 items-center">
        <!-- Doughnut Chart -->
        <div class="flex-shrink-0" style="width:160px;height:160px;">
          <Doughnut :data="chartData" :options="chartOptions" />
        </div>
        <!-- Legend -->
        <div class="flex-1 space-y-1.5 min-w-0">
          <div
            v-for="(item, i) in topCountries"
            :key="item.label"
            class="flex items-center gap-2"
          >
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="`background:${chartColors[i % chartColors.length]};`"></div>
            <span class="text-sm flex-shrink-0">{{ item.flag }}</span>
            <span class="text-xs text-white/70 truncate flex-1">{{ item.label }}</span>
            <span class="text-xs font-semibold text-white flex-shrink-0">{{ item.percent }}%</span>
          </div>
        </div>
      </div>
      <p class="text-xs text-white/25 mt-3">* Estimativa baseada no idioma das últimas 100 avaliações Steam</p>
    </div>

    <!-- Tags -->
    <div v-if="topTags.length" class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
      <h3 class="text-sm font-semibold text-white mb-3">Tags populares</h3>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="[tag, votes] in topTags"
          :key="tag"
          class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs"
          style="background:rgba(124,58,237,0.12);border:1px solid rgba(124,58,237,0.2);color:#a78bfa;"
        >
          {{ tag }}
          <span style="color:rgba(167,139,250,0.5);">{{ votes }}</span>
        </div>
      </div>
    </div>

    <!-- Screenshots -->
    <div v-if="data.screenshots?.length">
      <h3 class="text-sm font-semibold text-white mb-3">Screenshots</h3>
      <div class="grid grid-cols-3 gap-2">
        <img
          v-for="(ss, i) in data.screenshots"
          :key="i"
          :src="ss"
          class="rounded-lg object-cover w-full aspect-video cursor-pointer hover:opacity-80 transition-opacity"
          @click="openScreenshot(ss)"
        />
      </div>
    </div>

    <!-- Recent Reviews -->
    <div v-if="data.recent_reviews?.length">
      <h3 class="text-sm font-semibold text-white mb-3">Avaliações recentes</h3>
      <div class="space-y-2">
        <div
          v-for="review in data.recent_reviews.slice(0, 8)"
          :key="review.id"
          class="rounded-xl p-3 flex gap-3"
          style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);"
        >
          <div class="flex-shrink-0 mt-0.5">
            <span v-if="review.voted_up" class="text-green-400" title="Recomendado">👍</span>
            <span v-else class="text-red-400" title="Não recomendado">👎</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs font-medium text-white/60">{{ review.author }}</span>
              <span class="text-xs text-white/25">·</span>
              <span class="text-xs text-white/35">{{ review.playtime_hours }}h jogadas</span>
              <span v-if="review.written_during_early_access" class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(251,146,60,0.15);color:#fb923c;">EA</span>
            </div>
            <p class="text-sm text-white/70 leading-relaxed line-clamp-2">{{ review.review }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- News -->
    <div v-if="data.news?.length">
      <h3 class="text-sm font-semibold text-white mb-3">Notícias recentes</h3>
      <div class="space-y-2">
        <a
          v-for="(item, i) in data.news"
          :key="i"
          :href="item.url"
          target="_blank"
          class="flex items-start gap-3 rounded-xl p-3 hover:bg-white/5 transition-all group"
          style="border:1px solid rgba(255,255,255,0.06);"
        >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white group-hover:text-violet-300 transition-colors line-clamp-2">{{ item.title }}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs text-white/30">{{ item.feed }}</span>
              <span v-if="item.date" class="text-xs text-white/25">· {{ fmtDate(item.date) }}</span>
            </div>
          </div>
          <svg class="w-4 h-4 text-white/25 group-hover:text-violet-400 flex-shrink-0 mt-0.5 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
        </a>
      </div>
    </div>

    <!-- Game Info -->
    <div v-if="data.description || data.genres?.length || data.categories?.length || data.developers?.length" class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
      <h3 class="text-sm font-semibold text-white mb-3">Informações do jogo</h3>
      <p v-if="data.description" class="text-sm text-white/60 leading-relaxed mb-3">{{ data.description }}</p>
      <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
        <div v-if="data.developers?.length">
          <span class="text-white/35">Desenvolvedores: </span>
          <span class="text-white/70">{{ data.developers.join(', ') }}</span>
        </div>
        <div v-if="data.publishers?.length">
          <span class="text-white/35">Publicadores: </span>
          <span class="text-white/70">{{ data.publishers.join(', ') }}</span>
        </div>
        <div v-if="data.genres?.length">
          <span class="text-white/35">Gêneros: </span>
          <span class="text-white/70">{{ data.genres.join(', ') }}</span>
        </div>
        <div v-if="data.release_date">
          <span class="text-white/35">Lançamento: </span>
          <span class="text-white/70">{{ data.release_date }}</span>
        </div>
      </div>
      <div v-if="data.categories?.length" class="flex flex-wrap gap-1.5 mt-3">
        <span
          v-for="cat in data.categories"
          :key="cat"
          class="text-xs px-2 py-0.5 rounded"
          style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.45);"
        >{{ cat }}</span>
      </div>
    </div>

    <!-- Screenshot lightbox -->
    <div v-if="lightboxUrl" class="fixed inset-0 z-[60] flex items-center justify-center p-8" @click="lightboxUrl = ''" style="background:rgba(0,0,0,0.88);">
      <img :src="lightboxUrl" class="max-w-full max-h-full rounded-xl shadow-2xl" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import StatCard from './StatCard.vue'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps<{ data: any }>()
const lightboxUrl = ref('')

const chartColors = [
  '#7c3aed', '#a855f7', '#ec4899', '#3b82f6', '#06b6d4',
  '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6',
]

const topCountries = computed(() => {
  const dist = props.data.country_distribution ?? []
  return dist.slice(0, 8)
})

const chartData = computed(() => ({
  labels: topCountries.value.map((c: any) => `${c.flag} ${c.label}`),
  datasets: [{
    data: topCountries.value.map((c: any) => c.percent),
    backgroundColor: chartColors.slice(0, topCountries.value.length),
    borderColor: 'rgba(13,13,26,0.8)',
    borderWidth: 2,
    hoverOffset: 6,
  }],
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  cutout: '65%',
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (ctx: any) => ` ${ctx.parsed}%`,
      },
    },
  },
}

const topTags = computed(() => {
  if (!props.data.tags || typeof props.data.tags !== 'object') return []
  return Object.entries(props.data.tags as Record<string, number>)
    .sort((a, b) => (b[1] as number) - (a[1] as number))
    .slice(0, 12)
})

function fmt(n: number | null | undefined) {
  if (!n) return '0'
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M'
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K'
  return String(n)
}

function fmtPlayers(n: number | null | undefined) {
  if (!n || n === 0) return 'N/D'
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M'
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K'
  return String(n)
}

function fmtTime(minutes: number) {
  if (!minutes) return 'N/D'
  if (minutes < 60) return `${minutes} min`
  return `${Math.floor(minutes / 60)}h ${minutes % 60}min`
}

function fmtPrice(price: any) {
  if (!price) return 'Grátis'
  if (price.is_free) return 'Grátis'
  return price.final_formatted || 'N/A'
}

function fmtDate(ts: number) {
  return new Date(ts * 1000).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function scoreColor(score: number) {
  if (score >= 80) return 'color:#4ade80;'
  if (score >= 60) return 'color:#facc15;'
  return 'color:#f87171;'
}

function scoreBarColor(score: number) {
  if (score >= 80) return '#22c55e'
  if (score >= 60) return '#eab308'
  return '#ef4444'
}

function openScreenshot(url: string) {
  lightboxUrl.value = url
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
