import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

interface User {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref<string | null>(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  async function login(email: string, password: string) {
    const { data } = await api.post('/login', { email, password })
    setAuth(data.user, data.token)
    return data
  }

  async function register(name: string, email: string, password: string, password_confirmation: string) {
    const { data } = await api.post('/register', { name, email, password, password_confirmation })
    setAuth(data.user, data.token)
    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {}
    clearAuth()
  }

  async function fetchMe() {
    const { data } = await api.get('/me')
    user.value = data
    localStorage.setItem('user', JSON.stringify(data))
  }

  function setAuth(userData: User, tokenValue: string) {
    user.value = userData
    token.value = tokenValue
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', tokenValue)
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, login, register, logout, fetchMe }
})
