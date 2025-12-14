import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

interface Questionnaire {
  id: number
  title: string
  description: string
  type: string
  periode_tahun: number
  is_mandatory: boolean
  questions_count: number
  start_date: string
  end_date: string
  has_submitted: boolean
}

interface Section {
  section: number
  questions: Question[]
}

interface Question {
  id: number
  text: string
  type: string
  options: any
  is_required: boolean
  depends_on: number | null
  depends_value: string | null
}

interface QuestionnaireDetail {
  id: number
  title: string
  description: string
  type: string
  is_mandatory: boolean
  sections: Section[]
}

export const useQuestionnaireStore = defineStore('questionnaire', () => {
  const questionnaires = ref<Questionnaire[]>([])
  const currentQuestionnaire = ref<QuestionnaireDetail | null>(null)
  const loading = ref(false)
  const submitting = ref(false)
  const error = ref<string | null>(null)

  async function fetchQuestionnaires() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/questionnaires')
      questionnaires.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat kuesioner'
    } finally {
      loading.value = false
    }
  }

  async function fetchQuestionnaire(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/questionnaires/${id}`)
      currentQuestionnaire.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat kuesioner'
    } finally {
      loading.value = false
    }
  }

  async function submitQuestionnaire(id: number, answers: { question_id: number, value: any }[]) {
    submitting.value = true
    error.value = null
    try {
      await api.post(`/questionnaires/${id}/submit`, { answers })
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal mengirim jawaban'
      return false
    } finally {
      submitting.value = false
    }
  }

  return {
    questionnaires,
    currentQuestionnaire,
    loading,
    submitting,
    error,
    fetchQuestionnaires,
    fetchQuestionnaire,
    submitQuestionnaire,
  }
})
