import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

interface User {
  id: number
  username: string
  role: {
    id: number
    name: string
  }
  alumni?: {
    id: number
    nim: string
    nama: string
    prodi?: {
      id: number
      nama: string
    }
    tahun_lulus: number
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role.name === 'admin' || user.value?.role.name === 'superadmin')
  const isSuperAdmin = computed(() => user.value?.role.name === 'superadmin')
  const isAlumni = computed(() => user.value?.role.name === 'alumni')
  const roleName = computed(() => user.value?.role.name || '')

  async function login(username: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/login', { username, password })
      user.value = response.data.user
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Login gagal'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      user.value = null
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/me')
      user.value = response.data.user
      return true
    } catch {
      user.value = null
      return false
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    isSuperAdmin,
    isAlumni,
    roleName,
    login,
    logout,
    fetchUser,
  }
})
