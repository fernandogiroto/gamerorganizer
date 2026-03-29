<template>
  <div class="relative min-h-screen flex overflow-hidden" style="background:#09090f;">

    <!-- Background ambient glows -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-20"
        style="background:radial-gradient(circle,#7c3aed 0%,transparent 70%); filter:blur(60px);"></div>
      <div class="absolute -bottom-40 -right-20 w-80 h-80 rounded-full opacity-15"
        style="background:radial-gradient(circle,#a855f7 0%,transparent 70%); filter:blur(80px);"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] opacity-5"
        style="background:radial-gradient(circle,#7c3aed 0%,transparent 60%); filter:blur(40px);"></div>
    </div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.03]"
      style="background-image:linear-gradient(rgba(167,139,250,1) 1px,transparent 1px),linear-gradient(90deg,rgba(167,139,250,1) 1px,transparent 1px);background-size:48px 48px;">
    </div>

    <!-- Left panel — branding -->
    <div class="hidden lg:flex flex-col justify-between w-1/2 p-12 relative">
      <div>
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center w-10 h-10 rounded-xl"
            style="background:linear-gradient(135deg,#7c3aed,#5b21b6);box-shadow:0 0 20px rgba(124,58,237,0.5);">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="font-bold text-white text-lg tracking-tight">GamerOrganizer</span>
        </div>
      </div>

      <!-- Center content -->
      <div class="space-y-8">
        <div class="space-y-3">
          <div class="flex items-center gap-2 mb-4">
            <span class="badge-primary text-xs">Game Studio Hub</span>
          </div>
          <h1 class="text-4xl font-extrabold text-white leading-tight tracking-tight">
            Organize seu<br/>
            <span style="background:linear-gradient(135deg,#a78bfa,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
              estúdio de games
            </span>
          </h1>
          <p class="text-base leading-relaxed" style="color:rgba(255,255,255,0.45);">
            Tasks, marketing, influencers e dados dos seus jogos — tudo num só lugar.
          </p>
        </div>

        <!-- Feature list -->
        <div class="space-y-3">
          <div v-for="feat in features" :key="feat.text" class="flex items-center gap-3">
            <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg"
              style="background:rgba(124,58,237,0.12);border:1px solid rgba(124,58,237,0.2);">
              <svg class="w-4 h-4" style="color:#a78bfa;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feat.icon"/>
              </svg>
            </div>
            <span class="text-sm" style="color:rgba(255,255,255,0.55);">{{ feat.text }}</span>
          </div>
        </div>
      </div>

      <p class="text-xs" style="color:rgba(255,255,255,0.2);">
        © 2026 GamerOrganizer · Feito para game devs
      </p>
    </div>

    <!-- Right panel — form -->
    <div class="flex flex-1 items-center justify-center p-8 relative">
      <div class="w-full max-w-sm animate-slide-up">

        <!-- Mobile logo -->
        <div class="flex lg:hidden flex-col items-center mb-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-2xl mb-3"
            style="background:linear-gradient(135deg,#7c3aed,#5b21b6);box-shadow:0 0 24px rgba(124,58,237,0.5);">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h2 class="font-bold text-white text-xl tracking-tight">GamerOrganizer</h2>
        </div>

        <!-- Card -->
        <div class="card-glass p-8 space-y-6">
          <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Entrar na conta</h2>
            <p class="text-sm mt-1" style="color:rgba(255,255,255,0.4);">Bem-vindo de volta!</p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="label">E-mail</label>
              <input
                v-model="form.email"
                type="email"
                class="input"
                placeholder="seu@email.com"
                autocomplete="email"
                required
              />
            </div>

            <div>
              <label class="label">Senha</label>
              <input
                v-model="form.password"
                type="password"
                class="input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
            </div>

            <!-- Error -->
            <div v-if="error" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm"
              style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#f87171;">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              {{ error }}
            </div>

            <button
              type="submit"
              class="btn-primary w-full justify-center py-3 text-sm font-semibold"
              :disabled="loading"
            >
              <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ loading ? 'Entrando...' : 'Entrar' }}
            </button>
          </form>

          <div class="divider"></div>

          <p class="text-center text-sm" style="color:rgba(255,255,255,0.35);">
            Não tem conta?
            <RouterLink to="/register" class="font-medium transition-colors" style="color:#a78bfa;"
              @mouseenter="e => (e.target as HTMLElement).style.color='#c4b5fd'"
              @mouseleave="e => (e.target as HTMLElement).style.color='#a78bfa'"
            >
              Criar conta
            </RouterLink>
          </p>
        </div>
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

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const features = [
  { text: 'Kanban de tasks com drag & drop', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
  { text: 'Gerenciamento de influencers e marketing', icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' },
  { text: 'Dados e gráficos dos seus jogos na Steam', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
]

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/tasks')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'E-mail ou senha inválidos'
  } finally {
    loading.value = false
  }
}
</script>
