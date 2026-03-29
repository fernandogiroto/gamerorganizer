<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
      <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-2xl max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-700/50">
          <span class="w-3 h-3 rounded-full flex-shrink-0" :class="priorityColor(localCard.priority)"></span>
          <input
            v-model="localCard.title"
            class="flex-1 bg-transparent text-lg font-semibold text-white focus:outline-none placeholder-gray-500"
            placeholder="Título do card"
            @blur="saveField('title', localCard.title)"
          />
          <button @click="$emit('close')" class="text-gray-400 hover:text-white p-1">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="flex flex-1 overflow-hidden">
          <!-- Main -->
          <div class="flex-1 overflow-y-auto px-6 py-4 space-y-5">
            <!-- Description -->
            <div>
              <label class="label flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                Descrição
              </label>
              <textarea
                v-model="localCard.description"
                class="input resize-none text-sm"
                rows="5"
                placeholder="Adicione uma descrição detalhada..."
                @blur="saveField('description', localCard.description)"
              ></textarea>
            </div>

            <!-- Labels -->
            <div>
              <label class="label flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Labels
              </label>
              <div class="flex flex-wrap gap-2 mb-2">
                <span
                  v-for="(label, i) in localCard.labels"
                  :key="i"
                  class="badge bg-primary-600/20 text-primary-300 group gap-1"
                >
                  {{ label }}
                  <button @click="removeLabel(i)" class="hover:text-red-400 leading-none">&times;</button>
                </span>
              </div>
              <div class="flex gap-2">
                <input v-model="newLabel" type="text" class="input text-sm flex-1" placeholder="Adicionar label..." @keydown.enter.prevent="addLabel" />
                <button @click="addLabel" class="btn-secondary text-sm px-3">+</button>
              </div>
            </div>

            <!-- Attachments -->
            <div v-if="localCard.attachments?.length">
              <label class="label flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                Anexos ({{ localCard.attachments.length }})
              </label>
              <div class="space-y-2">
                <div
                  v-for="att in localCard.attachments"
                  :key="att.path"
                  class="flex items-center gap-3 bg-dark-700 rounded-lg px-3 py-2"
                >
                  <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <a :href="att.url" target="_blank" class="text-sm text-primary-400 hover:underline truncate flex-1">{{ att.name }}</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="w-52 border-l border-gray-700/50 px-4 py-4 space-y-4 overflow-y-auto">
            <!-- Priority -->
            <div>
              <label class="label text-xs uppercase tracking-wide">Prioridade</label>
              <select
                v-model="localCard.priority"
                class="input text-sm"
                @change="saveField('priority', localCard.priority)"
              >
                <option value="low">Baixa</option>
                <option value="medium">Média</option>
                <option value="high">Alta</option>
                <option value="urgent">Urgente</option>
              </select>
            </div>

            <!-- Category -->
            <div>
              <label class="label text-xs uppercase tracking-wide">Categoria</label>
              <input
                v-model="localCard.category"
                type="text"
                class="input text-sm"
                placeholder="Ex: Bug, Feature..."
                @blur="saveField('category', localCard.category)"
              />
            </div>

            <!-- Due Date -->
            <div>
              <label class="label text-xs uppercase tracking-wide">Data limite</label>
              <input
                v-model="localCard.due_date"
                type="date"
                class="input text-sm"
                @change="saveField('due_date', localCard.due_date)"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="label text-xs uppercase tracking-wide">Status</label>
              <button
                @click="toggleCompleted"
                class="w-full text-left btn text-sm py-2 px-3 border"
                :class="localCard.is_completed
                  ? 'bg-green-600/20 text-green-400 border-green-600/30'
                  : 'bg-dark-700 text-gray-400 border-gray-600'"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ localCard.is_completed ? 'Concluído' : 'Em aberto' }}
              </button>
            </div>

            <!-- Delete -->
            <div class="pt-2 border-t border-gray-700/50">
              <button @click="handleDelete" class="w-full btn-danger text-sm py-2 justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Deletar card
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useBoardStore, type TaskCard, type Board } from '@/stores/boards'

const props = defineProps<{
  card: TaskCard
  board: Board | null
}>()

const emit = defineEmits<{
  close: []
  updated: [card: TaskCard]
  deleted: [cardId: number]
}>()

const boardStore = useBoardStore()
const localCard = ref<TaskCard>({ ...props.card })
const newLabel = ref('')

watch(() => props.card, (val) => {
  localCard.value = { ...val }
}, { deep: true })

function priorityColor(priority: string) {
  const map: Record<string, string> = {
    low: 'bg-gray-500', medium: 'bg-yellow-500', high: 'bg-orange-500', urgent: 'bg-red-500',
  }
  return map[priority] || 'bg-gray-500'
}

async function saveField(field: string, value: any) {
  const updated = await boardStore.updateCard(localCard.value.id, { [field]: value })
  emit('updated', updated)
}

async function toggleCompleted() {
  localCard.value.is_completed = !localCard.value.is_completed
  await saveField('is_completed', localCard.value.is_completed)
}

function addLabel() {
  if (!newLabel.value.trim()) return
  const labels = [...(localCard.value.labels || []), newLabel.value.trim()]
  localCard.value.labels = labels
  newLabel.value = ''
  saveField('labels', labels)
}

function removeLabel(idx: number) {
  const labels = [...(localCard.value.labels || [])]
  labels.splice(idx, 1)
  localCard.value.labels = labels
  saveField('labels', labels)
}

async function handleDelete() {
  if (confirm(`Deletar o card "${localCard.value.title}"?`)) {
    await boardStore.deleteCard(localCard.value.id)
    emit('deleted', localCard.value.id)
  }
}
</script>
