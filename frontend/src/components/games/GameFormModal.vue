<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
      <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700/50">
          <h3 class="text-lg font-semibold text-white">{{ isEditing ? 'Editar Jogo' : 'Adicionar Jogo' }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
          <form @submit.prevent="handleSave" id="game-form" class="space-y-5">
            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="label">Nome do Jogo *</label>
                <input v-model="form.name" type="text" class="input" placeholder="Nome do seu jogo" required />
              </div>

              <div>
                <label class="label">Gênero</label>
                <input v-model="form.genre" type="text" class="input" placeholder="Ex: Platformer, RPG, FPS..." />
              </div>

              <div>
                <label class="label">Engine</label>
                <select v-model="form.engine" class="input">
                  <option value="">Selecione...</option>
                  <option value="Unity">Unity</option>
                  <option value="Unreal Engine">Unreal Engine</option>
                  <option value="Godot">Godot</option>
                  <option value="GameMaker">GameMaker</option>
                  <option value="Construct">Construct</option>
                  <option value="Custom">Engine Própria</option>
                  <option value="Other">Outro</option>
                </select>
              </div>

              <div>
                <label class="label">Status</label>
                <select v-model="form.status" class="input">
                  <option value="development">Em Desenvolvimento</option>
                  <option value="beta">Beta / Early Access</option>
                  <option value="released">Lançado</option>
                  <option value="abandoned">Abandonado</option>
                </select>
              </div>

              <div>
                <label class="label">Data de lançamento</label>
                <input v-model="form.release_date" type="date" class="input" />
              </div>

              <div class="col-span-2">
                <label class="label">Descrição</label>
                <textarea v-model="form.description" class="input resize-none" rows="3" placeholder="Descreva o seu jogo..."></textarea>
              </div>
            </div>

            <!-- Platform Links -->
            <div>
              <h4 class="text-sm font-medium text-gray-300 mb-3">Links de plataformas</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <label class="label flex items-center gap-2">
                    <span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-0.5 rounded">Steam</span>
                    URL da página na Steam
                  </label>
                  <input
                    v-model="form.steam_url"
                    type="url"
                    class="input"
                    placeholder="https://store.steampowered.com/app/XXXXX/..."
                    @blur="extractSteamId"
                  />
                  <p v-if="form.steam_app_id" class="text-xs text-green-400 mt-1">App ID detectado: {{ form.steam_app_id }}</p>
                </div>

                <div>
                  <label class="label">itch.io</label>
                  <input v-model="form.itch_url" type="url" class="input" placeholder="https://usuario.itch.io/jogo" />
                </div>

                <div>
                  <label class="label">Epic Games Store</label>
                  <input v-model="form.epic_url" type="url" class="input" placeholder="https://store.epicgames.com/..." />
                </div>

                <div>
                  <label class="label">Google Play</label>
                  <input v-model="form.android_url" type="url" class="input" placeholder="https://play.google.com/store/apps/..." />
                </div>

                <div>
                  <label class="label">App Store</label>
                  <input v-model="form.ios_url" type="url" class="input" placeholder="https://apps.apple.com/..." />
                </div>

                <div>
                  <label class="label">Website oficial</label>
                  <input v-model="form.website_url" type="url" class="input" placeholder="https://meujogo.com" />
                </div>

                <div>
                  <label class="label">GOG.com</label>
                  <input v-model="form.gog_url" type="url" class="input" placeholder="https://gog.com/game/..." />
                </div>
              </div>
            </div>

            <!-- Tags -->
            <div>
              <label class="label">Tags</label>
              <div class="flex flex-wrap gap-2 mb-2">
                <span v-for="(tag, i) in form.tags" :key="i" class="badge bg-primary-600/20 text-primary-300 gap-1">
                  {{ tag }}
                  <button type="button" @click="removeTag(i)" class="hover:text-red-400">&times;</button>
                </span>
              </div>
              <div class="flex gap-2">
                <input v-model="newTag" type="text" class="input text-sm flex-1" placeholder="Adicionar tag..." @keydown.enter.prevent="addTag" />
                <button type="button" @click="addTag" class="btn-secondary text-sm px-3">+</button>
              </div>
            </div>
          </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-700/50 flex justify-end gap-3">
          <button type="button" class="btn-secondary" @click="$emit('close')">Cancelar</button>
          <button type="submit" form="game-form" class="btn-primary" :disabled="saving">
            {{ saving ? 'Salvando...' : (isEditing ? 'Salvar alterações' : 'Adicionar jogo') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useGameStore, type Game } from '@/stores/games'

const props = defineProps<{ game: Game | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const store = useGameStore()
const saving = ref(false)
const newTag = ref('')
const isEditing = computed(() => !!props.game?.id)

const form = ref<Partial<Game>>({
  name: props.game?.name || '',
  description: props.game?.description || '',
  genre: props.game?.genre || '',
  engine: props.game?.engine || '',
  status: props.game?.status || 'development',
  release_date: props.game?.release_date || '',
  steam_url: props.game?.steam_url || '',
  steam_app_id: props.game?.steam_app_id || '',
  itch_url: props.game?.itch_url || '',
  epic_url: props.game?.epic_url || '',
  gog_url: props.game?.gog_url || '',
  android_url: props.game?.android_url || '',
  ios_url: props.game?.ios_url || '',
  website_url: props.game?.website_url || '',
  tags: props.game?.tags || [],
})

function extractSteamId() {
  if (form.value.steam_url) {
    const match = form.value.steam_url.match(/\/app\/(\d+)/)
    if (match) form.value.steam_app_id = match[1]
  }
}

function addTag() {
  if (!newTag.value.trim()) return
  form.value.tags = [...(form.value.tags || []), newTag.value.trim()]
  newTag.value = ''
}

function removeTag(i: number) {
  form.value.tags = (form.value.tags || []).filter((_, idx) => idx !== i)
}

async function handleSave() {
  saving.value = true
  try {
    if (isEditing.value && props.game) {
      await store.updateGame(props.game.id, form.value)
    } else {
      await store.createGame(form.value)
    }
    emit('saved')
  } finally {
    saving.value = false
  }
}
</script>
