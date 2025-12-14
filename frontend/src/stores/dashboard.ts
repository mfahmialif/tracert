import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

interface Summary {
  total_alumni: number
  total_responses: number
  active_questionnaires: number
  response_rate: number
}

interface ProdiStat {
  prodi_id: number
  nama: string
  total_alumni: number
  total_responses: number
  percentage: number
}

interface TahunStat {
  tahun: number
  total_alumni: number
  total_responses: number
  percentage: number
}

interface QuestionnaireStat {
  id: number
  title: string
  periode: number
  responses_count: number
  is_active: boolean
}

interface DashboardData {
  summary: Summary
  per_prodi: ProdiStat[]
  per_tahun: TahunStat[]
  questionnaires: QuestionnaireStat[]
}

export const useDashboardStore = defineStore('dashboard', () => {
  const data = ref<DashboardData | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchDashboard(filters: Record<string, any> = {}) {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await api.get(`/admin/dashboard?${params}`)
      data.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat dashboard'
    } finally {
      loading.value = false
    }
  }

  return {
    data,
    loading,
    error,
    fetchDashboard,
  }
})
