<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="page-title">Tasks</h1>
        <p class="text-gray-400 text-sm mt-1">Organize suas tarefas em quadros Kanban</p>
      </div>
      <button @click="showCreateBoard = true" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Board
      </button>
    </div>

    <!-- Loading -->
    <div v-if="boardStore.loading" class="flex items-center justify-center h-48">
      <div class="animate-spin w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="boardStore.boards.length === 0" class="flex flex-col items-center justify-center h-64 text-center">
      <div class="w-16 h-16 rounded-2xl bg-primary-600/20 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <p class="text-gray-300 font-medium">Nenhum board criado ainda</p>
      <p class="text-gray-500 text-sm mt-1">Crie seu primeiro board para organizar suas tarefas</p>
      <button @click="showCreateBoard = true" class="btn-primary mt-4">Criar Board</button>
    </div>

    <!-- Boards Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <RouterLink
        v-for="board in boardStore.boards"
        :key="board.id"
        :to="`/tasks/${board.id}`"
        class="card hover:border-gray-600 transition-all duration-200 group cursor-pointer"
      >
        <div class="flex items-start justify-between mb-3">
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center"
            :style="{ backgroundColor: board.color + '33', border: `1px solid ${board.color}55` }"
          >
            <svg class="w-5 h-5" :style="{ color: board.color }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <button
            @click.prevent="confirmDeleteBoard(board)"
            class="opacity-0 group-hover:opacity-100 text-gray-500 hover:text-red-400 transition-all p-1 rounded"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>

        <h3 class="font-semibold text-white group-hover:text-primary-300 transition-colors">{{ board.name }}</h3>
        <p v-if="board.description" class="text-gray-400 text-xs mt-1 line-clamp-2">{{ board.description }}</p>

        <div class="flex items-center gap-3 mt-4 pt-3 border-t border-gray-700/50">
          <span class="text-xs text-gray-500">{{ board.columns?.length || 0 }} colunas</span>
          <span class="text-xs text-gray-500">
            {{ board.columns?.reduce((acc, c) => acc + (c.task_cards?.length || 0), 0) || 0 }} cards
          </span>
        </div>
      </RouterLink>
    </div>

    <!-- Create Board Modal -->
    <Teleport to="body">
      <div v-if="showCreateBoard" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showCreateBoard = false">
        <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-md p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Criar novo Board</h3>

          <form @submit.prevent="handleCreateBoard" class="space-y-4">
            <div>
              <label class="label">Nome do Board</label>
              <input v-model="newBoard.name" type="text" class="input" placeholder="Ex: Desenvolvimento do Jogo" required autofocus />
            </div>
            <div>
              <label class="label">Descrição (opcional)</label>
              <input v-model="newBoard.description" type="text" class="input" placeholder="Breve descrição" />
            </div>
            <div>
              <label class="label">Cor</label>
              <div class="flex gap-2 flex-wrap">
                <button
                  v-for="color in colors"
                  :key="color"
                  type="button"
                  class="w-7 h-7 rounded-full border-2 transition-transform hover:scale-110"
                  :style="{ backgroundColor: color, borderColor: newBoard.color === color ? 'white' : 'transparent' }"
                  @click="newBoard.color = color"
                />
              </div>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1 justify-center" @click="showCreateBoard = false">Cancelar</button>
              <button type="submit" class="btn-primary flex-1 justify-center" :disabled="creating">
                {{ creating ? 'Criando...' : 'Criar Board' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBoardStore, type Board } from '@/stores/boards'

const boardStore = useBoardStore()
const showCreateBoard = ref(false)
const creating = ref(false)

const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#06b6d4']
const newBoard = ref({ name: '', description: '', color: '#6366f1' })

onMounted(() => boardStore.fetchBoards())

async function handleCreateBoard() {
  creating.value = true
  try {
    await boardStore.createBoard(newBoard.value)
    showCreateBoard.value = false
    newBoard.value = { name: '', description: '', color: '#6366f1' }
  } finally {
    creating.value = false
  }
}

async function confirmDeleteBoard(board: Board) {
  if (confirm(`Deletar o board "${board.name}"? Todas as colunas e cards serão removidos.`)) {
    await boardStore.deleteBoard(board.id)
  }
}
</script>
