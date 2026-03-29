<template>
  <div class="flex flex-col h-full">
    <!-- Board Header -->
    <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-700/50 bg-dark-800/50">
      <RouterLink to="/tasks" class="text-gray-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </RouterLink>

      <div class="flex items-center gap-3 flex-1">
        <div
          class="w-8 h-8 rounded-lg flex items-center justify-center"
          :style="{ backgroundColor: (board?.color || '#6366f1') + '33' }"
        >
          <svg class="w-4 h-4" :style="{ color: board?.color || '#6366f1' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <h1 class="text-lg font-bold text-white">{{ board?.name }}</h1>
      </div>

      <button @click="showAddColumn = true" class="btn-secondary text-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Coluna
      </button>
    </div>

    <!-- Loading -->
    <div v-if="boardStore.loading" class="flex items-center justify-center flex-1">
      <div class="animate-spin w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full"></div>
    </div>

    <!-- Kanban Board -->
    <div v-else class="flex-1 overflow-x-auto p-6">
      <div class="flex gap-4 h-full" style="min-height: calc(100vh - 130px)">
        <div
          v-for="column in board?.columns"
          :key="column.id"
          class="flex flex-col w-72 flex-shrink-0"
        >
          <!-- Column Header -->
          <div
            class="flex items-center gap-2 mb-3 px-1"
            :style="{ borderLeft: `3px solid ${column.color}` }"
            style="padding-left: 8px;"
          >
            <span class="font-semibold text-sm text-white flex-1">{{ column.name }}</span>
            <span class="text-xs text-gray-500 bg-dark-700 px-2 py-0.5 rounded-full">{{ column.task_cards?.length || 0 }}</span>

            <div class="relative" @click.stop>
              <button @click="toggleColumnMenu(column.id)" class="text-gray-500 hover:text-white p-1 rounded">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01" />
                </svg>
              </button>
              <div v-if="openColumnMenu === column.id" class="absolute right-0 top-8 bg-dark-700 border border-gray-600 rounded-lg shadow-xl z-10 w-36 py-1">
                <button @click="startAddCard(column.id)" class="w-full text-left px-3 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">Adicionar card</button>
                <button @click="confirmDeleteColumn(column)" class="w-full text-left px-3 py-2 text-sm text-red-400 hover:bg-red-500/10">Deletar coluna</button>
              </div>
            </div>
          </div>

          <!-- Cards List with Drag & Drop -->
          <div
            class="flex-1 space-y-3 min-h-16 rounded-xl p-2 transition-colors duration-200"
            :class="[dragOverColumn === column.id ? 'bg-primary-600/10 border border-primary-600/30' : 'bg-dark-700/30']"
            @dragover.prevent="dragOverColumn = column.id"
            @dragleave="dragOverColumn = null"
            @drop.prevent="handleDrop(column.id)"
          >
            <div
              v-for="card in column.task_cards"
              :key="card.id"
              class="bg-dark-800 border border-gray-700/50 rounded-xl p-3 cursor-grab hover:border-gray-500 transition-all duration-200 hover:shadow-lg group"
              :class="[card.is_completed ? 'opacity-60' : '']"
              draggable="true"
              @dragstart="handleDragStart(card, column.id)"
              @dragend="dragCard = null"
              @click="openCardModal(card)"
            >
              <!-- Priority indicator -->
              <div class="flex items-center gap-2 mb-2">
                <span
                  class="w-2 h-2 rounded-full flex-shrink-0"
                  :class="priorityColor(card.priority)"
                ></span>
                <span v-if="card.category" class="text-xs text-primary-400 bg-primary-600/10 px-2 py-0.5 rounded-full">{{ card.category }}</span>
                <span v-if="card.is_completed" class="ml-auto text-xs text-green-400">✓</span>
              </div>

              <!-- Title -->
              <h4 class="text-sm font-medium text-white leading-snug" :class="[card.is_completed ? 'line-through text-gray-500' : '']">{{ card.title }}</h4>

              <!-- Labels -->
              <div v-if="card.labels?.length" class="flex flex-wrap gap-1 mt-2">
                <span v-for="label in card.labels.slice(0, 3)" :key="label" class="text-xs bg-dark-700 text-gray-400 px-1.5 py-0.5 rounded">{{ label }}</span>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-700/50 opacity-0 group-hover:opacity-100 transition-opacity">
                <span v-if="card.due_date" class="text-xs text-gray-500 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(card.due_date) }}
                </span>
                <span v-if="card.attachments?.length" class="text-xs text-gray-500 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  {{ card.attachments.length }}
                </span>
              </div>
            </div>

            <!-- Add Card inline -->
            <div v-if="addingCardInColumn === column.id" class="bg-dark-800 border border-primary-500/50 rounded-xl p-3">
              <input
                v-model="newCardTitle"
                ref="newCardInput"
                type="text"
                class="input text-sm mb-2"
                placeholder="Título do card..."
                @keydown.enter="handleAddCard(column.id)"
                @keydown.esc="addingCardInColumn = null"
              />
              <div class="flex gap-2">
                <button @click="handleAddCard(column.id)" class="btn-primary text-xs py-1 px-3">Adicionar</button>
                <button @click="addingCardInColumn = null" class="btn-ghost text-xs py-1 px-3">Cancelar</button>
              </div>
            </div>
          </div>

          <!-- Add Card Button -->
          <button
            v-if="addingCardInColumn !== column.id"
            @click="startAddCard(column.id)"
            class="mt-3 w-full btn-ghost text-sm py-2 justify-center border border-dashed border-gray-700 hover:border-gray-500"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar card
          </button>
        </div>

        <!-- Add Column placeholder -->
        <div v-if="!showAddColumn" class="w-72 flex-shrink-0">
          <button @click="showAddColumn = true" class="w-full h-12 border-2 border-dashed border-gray-700 rounded-xl text-gray-500 hover:border-gray-500 hover:text-gray-300 transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Coluna
          </button>
        </div>
      </div>
    </div>

    <!-- Add Column Modal -->
    <Teleport to="body">
      <div v-if="showAddColumn" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showAddColumn = false">
        <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-sm p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Nova Coluna</h3>
          <form @submit.prevent="handleAddColumn" class="space-y-4">
            <div>
              <label class="label">Nome da Coluna</label>
              <input v-model="newColumn.name" type="text" class="input" placeholder="Ex: Em andamento" required autofocus />
            </div>
            <div>
              <label class="label">Cor</label>
              <div class="flex gap-2 flex-wrap">
                <button
                  v-for="color in colors"
                  :key="color"
                  type="button"
                  class="w-7 h-7 rounded-full border-2 transition-transform hover:scale-110"
                  :style="{ backgroundColor: color, borderColor: newColumn.color === color ? 'white' : 'transparent' }"
                  @click="newColumn.color = color"
                />
              </div>
            </div>
            <div class="flex gap-3">
              <button type="button" class="btn-secondary flex-1 justify-center" @click="showAddColumn = false">Cancelar</button>
              <button type="submit" class="btn-primary flex-1 justify-center">Criar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Card Detail Modal -->
    <CardModal
      v-if="selectedCard"
      :card="selectedCard"
      :board="board"
      @close="selectedCard = null"
      @updated="handleCardUpdated"
      @deleted="handleCardDeleted"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useBoardStore, type TaskCard, type Column } from '@/stores/boards'
import CardModal from '@/components/tasks/CardModal.vue'

const route = useRoute()
const boardStore = useBoardStore()

const board = computed(() => boardStore.currentBoard)
const showAddColumn = ref(false)
const openColumnMenu = ref<number | null>(null)
const dragCard = ref<{ card: TaskCard; fromColumnId: number } | null>(null)
const dragOverColumn = ref<number | null>(null)
const addingCardInColumn = ref<number | null>(null)
const newCardTitle = ref('')
const newCardInput = ref<HTMLInputElement | null>(null)
const selectedCard = ref<TaskCard | null>(null)
const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#06b6d4']
const newColumn = ref({ name: '', color: '#6366f1' })

onMounted(() => {
  boardStore.fetchBoard(Number(route.params.boardId))
})

function toggleColumnMenu(id: number) {
  openColumnMenu.value = openColumnMenu.value === id ? null : id
}

function priorityColor(priority: string) {
  const map: Record<string, string> = {
    low: 'bg-gray-500',
    medium: 'bg-yellow-500',
    high: 'bg-orange-500',
    urgent: 'bg-red-500',
  }
  return map[priority] || 'bg-gray-500'
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

function handleDragStart(card: TaskCard, fromColumnId: number) {
  dragCard.value = { card, fromColumnId }
}

async function handleDrop(targetColumnId: number) {
  dragOverColumn.value = null
  if (!dragCard.value) return

  const { card, fromColumnId } = dragCard.value
  if (fromColumnId === targetColumnId) return

  const targetColumn = board.value?.columns.find(c => c.id === targetColumnId)
  const position = targetColumn?.task_cards?.length || 0

  // Optimistic UI update
  const sourceColumn = board.value?.columns.find(c => c.id === fromColumnId)
  if (sourceColumn && targetColumn) {
    sourceColumn.task_cards = sourceColumn.task_cards.filter(c => c.id !== card.id)
    const movedCard = { ...card, column_id: targetColumnId, position }
    targetColumn.task_cards.push(movedCard)
  }

  await boardStore.moveCard(card.id, targetColumnId, position)
  dragCard.value = null
}

async function handleAddColumn() {
  if (!board.value) return
  await boardStore.createColumn(board.value.id, newColumn.value)
  showAddColumn.value = false
  newColumn.value = { name: '', color: '#6366f1' }
}

async function confirmDeleteColumn(column: Column) {
  openColumnMenu.value = null
  if (confirm(`Deletar a coluna "${column.name}"? Todos os cards serão removidos.`)) {
    await boardStore.deleteColumn(column.id)
  }
}

function startAddCard(columnId: number) {
  openColumnMenu.value = null
  addingCardInColumn.value = columnId
  newCardTitle.value = ''
  nextTick(() => newCardInput.value?.focus())
}

async function handleAddCard(columnId: number) {
  if (!newCardTitle.value.trim()) return
  await boardStore.createCard(columnId, { title: newCardTitle.value.trim() })
  newCardTitle.value = ''
  addingCardInColumn.value = null
}

function openCardModal(card: TaskCard) {
  selectedCard.value = { ...card }
}

function handleCardUpdated(updated: TaskCard) {
  selectedCard.value = updated
  boardStore.fetchBoard(Number(route.params.boardId))
}

function handleCardDeleted(cardId: number) {
  selectedCard.value = null
  boardStore.fetchBoard(Number(route.params.boardId))
}

// Close menus when clicking outside
function handleClickOutside() {
  openColumnMenu.value = null
}
</script>
