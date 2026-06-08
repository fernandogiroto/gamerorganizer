<template>
  <div class="flex flex-col h-full" style="background:#09090f;">

    <!-- Header -->
    <div class="px-6 py-5 flex-shrink-0 flex items-center justify-between gap-4 flex-wrap"
      style="border-bottom:1px solid rgba(255,255,255,0.06);">
      <div>
        <h1 class="page-title">Djerba, Tunísia 🇹🇳</h1>
        <p class="page-subtitle">Checklist do que fazer na ilha — marca o que já viveste</p>
      </div>

      <div class="flex items-center gap-4">
        <div class="min-w-[180px]">
          <div class="flex items-center justify-between text-xs mb-1.5">
            <span style="color:rgba(255,255,255,0.4);">Progresso</span>
            <span class="font-semibold text-white">{{ doneCount }}/{{ totalCount }}</span>
          </div>
          <div class="w-full h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.06);">
            <div class="h-full rounded-full transition-all duration-300"
              :style="{ width: progressPercent + '%', background: 'linear-gradient(90deg,#7c3aed,#a855f7)' }"></div>
          </div>
        </div>

        <button class="btn-secondary" @click="resetAll" title="Desmarcar tudo">
          Reiniciar
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto px-6 py-6">
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="category in categories" :key="category.id" class="card flex flex-col gap-3">

          <!-- Category header -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xl leading-none">{{ category.emoji }}</span>
              <h3 class="font-semibold text-white text-sm">{{ category.title }}</h3>
            </div>
            <span class="badge-primary">{{ doneInCategory(category) }}/{{ category.items.length }}</span>
          </div>

          <div class="divider"></div>

          <!-- Items -->
          <ul class="flex flex-col gap-1">
            <li v-for="item in category.items" :key="item.id"
              class="group flex items-start gap-2.5 rounded-lg px-2 py-1.5 cursor-pointer transition-colors duration-150"
              :class="item.done ? 'item-done' : 'item-pending'"
              @click="toggleItem(item)"
            >
              <span class="checkbox flex-shrink-0 mt-0.5" :class="{ 'checkbox-checked': item.done }">
                <svg v-if="item.done" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </span>
              <span class="text-sm leading-snug flex-1"
                :style="{ color: item.done ? 'rgba(255,255,255,0.35)' : 'rgba(255,255,255,0.85)', textDecoration: item.done ? 'line-through' : 'none' }">
                {{ item.label }}
              </span>
              <button v-if="item.custom" @click.stop="removeItem(category, item)"
                class="opacity-0 group-hover:opacity-100 flex-shrink-0 transition-opacity duration-150"
                style="color:rgba(255,255,255,0.25);" title="Remover item">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </li>
          </ul>

          <!-- Add custom item -->
          <form @submit.prevent="addItem(category)" class="flex items-center gap-2 mt-1">
            <input v-model="newItemLabel[category.id]" type="text" placeholder="Adicionar item à lista..."
              class="input text-sm py-1.5" />
            <button type="submit" class="btn-secondary text-sm py-1.5 px-3 flex-shrink-0">Adicionar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, watch } from 'vue'

interface ChecklistItem {
  id: string
  label: string
  done: boolean
  custom?: boolean
}

interface ChecklistCategory {
  id: string
  title: string
  emoji: string
  items: ChecklistItem[]
}

const STORAGE_KEY = 'djerba-checklist-v1'

function defaultCategories(): ChecklistCategory[] {
  const seed: { id: string; title: string; emoji: string; items: string[] }[] = [
    {
      id: 'praias',
      title: 'Praias e Natureza',
      emoji: '🏖️',
      items: [
        'Relaxar na Plage de Sidi Mahrez',
        'Caminhar pela praia de Aghir / Seguia',
        'Ver o pôr do sol na costa norte da ilha',
        'Passeio de barco "pirata" com paragem para snorkeling',
        'Visitar o Djerba Explore (parque e crocodilos)',
      ],
    },
    {
      id: 'cultura',
      title: 'Cultura e História',
      emoji: '🕌',
      items: [
        'Explorar a medina de Houmt Souk',
        'Visitar a Sinagoga La Ghriba, em Erriadh',
        'Conhecer o Forte de Borj Ghazi Mustapha (Borj El Kebir)',
        'Visitar o Museu Lalla Hadria (artesanato e tradições da ilha)',
        'Ver os oleiros tradicionais na vila de Guellala',
      ],
    },
    {
      id: 'arte',
      title: 'Arte e Vilas',
      emoji: '🎨',
      items: [
        'Descobrir os murais de Djerbahood, em Erriadh',
        'Passear pelas ruelas brancas e azuis das vilas tradicionais',
        'Fotografar as portas e janelas decoradas à mão',
      ],
    },
    {
      id: 'gastronomia',
      title: 'Gastronomia',
      emoji: '🍽️',
      items: [
        'Provar um couscous tradicional tunisino',
        "Experimentar um brik à l'oeuf",
        'Beber chá com hortelã e pinhões',
        'Provar tâmaras frescas (a ilha é famosa por elas)',
        'Jantar de peixe fresco à beira-mar em Houmt Souk',
      ],
    },
    {
      id: 'compras',
      title: 'Compras e Mercados',
      emoji: '🛍️',
      items: [
        'Percorrer o mercado de Houmt Souk (especiarias, tapetes, cerâmica)',
        'Comprar azeite de oliveira local',
        'Levar uma peça de cerâmica de Guellala como recordação',
        'Pechinchar nas lojas de artesanato (faz parte da experiência)',
      ],
    },
    {
      id: 'aventura',
      title: 'Aventuras e Passeios',
      emoji: '🐪',
      items: [
        'Passeio de camelo pelas dunas',
        'Excursão de um dia ao deserto de Ksar Ghilane',
        'Ver os flamingos na lagoa de Djerba',
        'Apanhar o ferry para a ilha a partir de Jorf',
      ],
    },
    {
      id: 'logistica',
      title: 'Antes de viajar',
      emoji: '✅',
      items: [
        'Verificar visto / documentação necessária',
        'Levar protetor solar, chapéu e roupa leve',
        'Aprender frases básicas em árabe e francês',
        'Trocar dinheiro para dinares tunisinos',
      ],
    },
  ]

  return seed.map((cat) => ({
    id: cat.id,
    title: cat.title,
    emoji: cat.emoji,
    items: cat.items.map((label, idx) => ({ id: `${cat.id}-${idx}`, label, done: false })),
  }))
}

function loadCategories(): ChecklistCategory[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length) return parsed as ChecklistCategory[]
    }
  } catch {
    // dados corrompidos no localStorage — ignora e usa a lista padrão
  }
  return defaultCategories()
}

const categories = reactive<ChecklistCategory[]>(loadCategories())
const newItemLabel = reactive<Record<string, string>>({})

watch(
  categories,
  () => localStorage.setItem(STORAGE_KEY, JSON.stringify(categories)),
  { deep: true },
)

const totalCount = computed(() => categories.reduce((sum, cat) => sum + cat.items.length, 0))
const doneCount = computed(() =>
  categories.reduce((sum, cat) => sum + cat.items.filter((item) => item.done).length, 0),
)
const progressPercent = computed(() =>
  totalCount.value === 0 ? 0 : Math.round((doneCount.value / totalCount.value) * 100),
)

function doneInCategory(category: ChecklistCategory) {
  return category.items.filter((item) => item.done).length
}

function toggleItem(item: ChecklistItem) {
  item.done = !item.done
}

function addItem(category: ChecklistCategory) {
  const label = (newItemLabel[category.id] || '').trim()
  if (!label) return

  category.items.push({
    id: `custom-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
    label,
    done: false,
    custom: true,
  })
  newItemLabel[category.id] = ''
}

function removeItem(category: ChecklistCategory, item: ChecklistItem) {
  category.items = category.items.filter((i) => i.id !== item.id)
}

function resetAll() {
  if (!confirm('Desmarcar todos os itens da checklist?')) return
  categories.forEach((cat) => cat.items.forEach((item) => (item.done = false)))
}
</script>

<style scoped>
.checkbox {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 5px;
  border: 1.5px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.03);
  transition: all 0.15s ease;
}

.checkbox-checked {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  border-color: #7c3aed;
}

.item-pending:hover {
  background: rgba(255, 255, 255, 0.04);
}

.item-done {
  background: rgba(124, 58, 237, 0.06);
}
</style>
