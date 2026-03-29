<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
      <div class="bg-dark-800 rounded-2xl border border-gray-700 w-full max-w-2xl max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700/50">
          <h3 class="text-lg font-semibold text-white">
            {{ isEditing ? 'Editar Influenciador' : 'Novo Influenciador' }}
          </h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <div class="flex-1 overflow-y-auto px-6 py-5">
          <form @submit.prevent="handleSave" id="inf-form" class="space-y-5">
            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="label">Nome *</label>
                <input v-model="form.name" type="text" class="input" placeholder="Nome do canal/influenciador" required />
              </div>

              <div>
                <label class="label">Tipo *</label>
                <select v-model="form.type" class="input" required>
                  <option value="">Selecione...</option>
                  <option value="youtuber">YouTuber</option>
                  <option value="streamer">Streamer (Twitch/etc)</option>
                  <option value="instagram">Instagram</option>
                  <option value="tiktok">TikTok</option>
                  <option value="blogger">Blogger</option>
                  <option value="podcast">Podcast</option>
                  <option value="other">Outro</option>
                </select>
              </div>

              <div>
                <label class="label">Status</label>
                <select v-model="form.status" class="input">
                  <option value="prospect">Prospect</option>
                  <option value="contacted">Contactado</option>
                  <option value="negotiating">Negociando</option>
                  <option value="active">Ativo</option>
                  <option value="inactive">Inativo</option>
                </select>
              </div>

              <div>
                <label class="label">Nicho / Área</label>
                <input v-model="form.niche" type="text" class="input" placeholder="Ex: Indie Game Dev, RPG..." />
              </div>

              <div>
                <label class="label">Inscritos / Seguidores</label>
                <input v-model.number="form.subscribers" type="number" class="input" placeholder="0" min="0" />
              </div>
            </div>

            <!-- Contact -->
            <div>
              <h4 class="text-sm font-medium text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Contato
              </h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="label">E-mail</label>
                  <input v-model="form.email" type="email" class="input" placeholder="contato@email.com" />
                </div>
                <div>
                  <label class="label">URL do canal</label>
                  <input v-model="form.channel_url" type="url" class="input" placeholder="https://youtube.com/..." />
                </div>
                <div>
                  <label class="label">Instagram</label>
                  <input v-model="form.instagram" type="text" class="input" placeholder="@usuario" />
                </div>
                <div>
                  <label class="label">Twitter / X</label>
                  <input v-model="form.twitter" type="text" class="input" placeholder="@usuario" />
                </div>
                <div>
                  <label class="label">Discord</label>
                  <input v-model="form.discord" type="text" class="input" placeholder="usuario#0000 ou servidor" />
                </div>
                <div>
                  <label class="label">Último contato</label>
                  <input v-model="form.last_contact_date" type="date" class="input" />
                </div>
              </div>
            </div>

            <!-- Location -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">País</label>
                <select v-model="form.country" class="input">
                  <option value="Brazil">Brasil</option>
                  <option value="United States">Estados Unidos</option>
                  <option value="Portugal">Portugal</option>
                  <option value="Argentina">Argentina</option>
                  <option value="Spain">Espanha</option>
                  <option value="Other">Outro</option>
                </select>
              </div>
              <div>
                <label class="label">Idioma</label>
                <select v-model="form.language" class="input">
                  <option value="Portuguese">Português</option>
                  <option value="English">Inglês</option>
                  <option value="Spanish">Espanhol</option>
                </select>
              </div>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Média de views</label>
                <input v-model.number="form.avg_views" type="number" class="input" placeholder="0" min="0" />
              </div>
              <div>
                <label class="label">Taxa de engajamento (%)</label>
                <input v-model.number="form.engagement_rate" type="number" class="input" placeholder="0.0" min="0" max="100" step="0.01" />
              </div>
            </div>

            <!-- Tags & Games -->
            <div>
              <label class="label">Jogos cobertos (um por linha)</label>
              <textarea
                :value="(form.games_covered || []).join('\n')"
                @input="form.games_covered = ($event.target as HTMLTextAreaElement).value.split('\n').filter(Boolean)"
                class="input resize-none text-sm"
                rows="3"
                placeholder="Nome do Jogo 1&#10;Nome do Jogo 2"
              ></textarea>
            </div>

            <!-- Notes -->
            <div>
              <label class="label">Notas / Observações</label>
              <textarea v-model="form.notes" class="input resize-none text-sm" rows="3" placeholder="Notas internas sobre este influenciador..."></textarea>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-700/50 flex gap-3 justify-end">
          <button type="button" class="btn-secondary" @click="$emit('close')">Cancelar</button>
          <button type="submit" form="inf-form" class="btn-primary" :disabled="saving">
            {{ saving ? 'Salvando...' : (isEditing ? 'Salvar alterações' : 'Adicionar') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useInfluencerStore, type Influencer } from '@/stores/influencers'

const props = defineProps<{
  influencer: Influencer | null
}>()

const emit = defineEmits<{
  close: []
  saved: []
}>()

const store = useInfluencerStore()
const saving = ref(false)
const isEditing = computed(() => !!props.influencer?.id)

const form = ref<Partial<Influencer>>({
  name: props.influencer?.name || '',
  type: props.influencer?.type || '',
  status: props.influencer?.status || 'prospect',
  niche: props.influencer?.niche || '',
  channel_url: props.influencer?.channel_url || '',
  email: props.influencer?.email || '',
  instagram: props.influencer?.instagram || '',
  twitter: props.influencer?.twitter || '',
  discord: props.influencer?.discord || '',
  country: props.influencer?.country || 'Brazil',
  language: props.influencer?.language || 'Portuguese',
  subscribers: props.influencer?.subscribers || 0,
  avg_views: props.influencer?.avg_views || undefined,
  engagement_rate: props.influencer?.engagement_rate || undefined,
  last_contact_date: props.influencer?.last_contact_date || '',
  games_covered: props.influencer?.games_covered || [],
  notes: props.influencer?.notes || '',
})

async function handleSave() {
  saving.value = true
  try {
    if (isEditing.value && props.influencer) {
      await store.updateInfluencer(props.influencer.id, form.value)
    } else {
      await store.createInfluencer(form.value)
    }
    emit('saved')
  } finally {
    saving.value = false
  }
}
</script>
