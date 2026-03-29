<template>
  <div class="min-h-screen bg-dark-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 mb-4">
          <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">GamerOrganizer</h1>
        <p class="text-gray-400 mt-1">Crie sua conta gratuitamente</p>
      </div>

      <div class="card">
        <h2 class="text-xl font-semibold text-white mb-6">Criar conta</h2>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label class="label">Nome</label>
            <input v-model="form.name" type="text" class="input" placeholder="Seu nome" required />
          </div>

          <div>
            <label class="label">E-mail</label>
            <input v-model="form.email" type="email" class="input" placeholder="seu@email.com" required />
          </div>

          <div>
            <label class="label">Senha</label>
            <input v-model="form.password" type="password" class="input" placeholder="Mínimo 8 caracteres" required />
          </div>

          <div>
            <label class="label">Confirmar Senha</label>
            <input v-model="form.password_confirmation" type="password" class="input" placeholder="Repita a senha" required />
          </div>

          <p v-if="error" class="text-red-400 text-sm">{{ error }}</p>

          <button type="submit" class="btn-primary w-full justify-center py-2.5" :disabled="loading">
            <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Criando conta...' : 'Criar conta' }}
          </button>
        </form>

        <p class="text-center text-gray-400 text-sm mt-6">
          Já tem conta?
          <RouterLink to="/login" class="text-primary-400 hover:text-primary-300 font-medium">Entrar</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  error.value = ''
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'As senhas não conferem'
    return
  }
  loading.value = true
  try {
    await auth.register(form.value.name, form.value.email, form.value.password, form.value.password_confirmation)
    router.push('/tasks')
  } catch (e: any) {
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join('. ')
    } else {
      error.value = e.response?.data?.message || 'Erro ao criar conta'
    }
  } finally {
    loading.value = false
  }
}
</script>
