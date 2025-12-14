import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useQuestionnaireManagementStore = defineStore('questionnaireManagement', () => {
  const questionnaires = ref<any[]>([])
  const currentQuestionnaire = ref<any>(null)
  const questionnaireTypes = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Questionnaire Types
  async function fetchQuestionnaireTypes() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/admin/questionnaire-types')
      questionnaireTypes.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat tipe kuesioner'
    } finally {
      loading.value = false
    }
  }

  async function createQuestionnaireType(data: any) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/admin/questionnaire-types', data)
      questionnaireTypes.value.push(response.data.data)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal membuat tipe kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  async function updateQuestionnaireType(id: number, data: any) {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(`/admin/questionnaire-types/${id}`, data)
      const index = questionnaireTypes.value.findIndex(t => t.id === id)
      if (index !== -1) {
        questionnaireTypes.value[index] = response.data.data
      }
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal mengupdate tipe kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  async function deleteQuestionnaireType(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/questionnaire-types/${id}`)
      questionnaireTypes.value = questionnaireTypes.value.filter(t => t.id !== id)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal menghapus tipe kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  // Questionnaires
  async function fetchQuestionnaires(filters: any = {}) {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await api.get(`/admin/questionnaires?${params}`)
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
      const response = await api.get(`/admin/questionnaires/${id}`)
      currentQuestionnaire.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat kuesioner'
    } finally {
      loading.value = false
    }
  }

  async function createQuestionnaire(data: any) {
    loading.value = true
    error.value = null
    try {
      await api.post('/admin/questionnaires', data)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal membuat kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  async function updateQuestionnaire(id: number, data: any) {
    loading.value = true
    error.value = null
    try {
      await api.put(`/admin/questionnaires/${id}`, data)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal mengupdate kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  async function deleteQuestionnaire(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/questionnaires/${id}`)
      questionnaires.value = questionnaires.value.filter(q => q.id !== id)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal menghapus kuesioner'
      return false
    } finally {
      loading.value = false
    }
  }

  // Questions
  async function addQuestion(questionnaireId: number, data: any) {
    loading.value = true
    error.value = null
    try {
      await api.post(`/admin/questionnaires/${questionnaireId}/questions`, data)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal menambah pertanyaan'
      return false
    } finally {
      loading.value = false
    }
  }

  async function updateQuestion(id: number, data: any) {
    loading.value = true
    error.value = null
    try {
      await api.put(`/admin/questions/${id}`, data)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal mengupdate pertanyaan'
      return false
    } finally {
      loading.value = false
    }
  }

  async function deleteQuestion(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/questions/${id}`)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal menghapus pertanyaan'
      return false
    } finally {
      loading.value = false
    }
  }

  async function reorderQuestions(questionnaireId: number, questions: any[]) {
    loading.value = true
    error.value = null
    try {
      await api.post(`/admin/questionnaires/${questionnaireId}/questions/reorder`, { questions })
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal mengatur ulang pertanyaan'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    questionnaires,
    currentQuestionnaire,
    questionnaireTypes,
    loading,
    error,
    fetchQuestionnaireTypes,
    createQuestionnaireType,
    updateQuestionnaireType,
    deleteQuestionnaireType,
    fetchQuestionnaires,
    fetchQuestionnaire,
    createQuestionnaire,
    updateQuestionnaire,
    deleteQuestionnaire,
    addQuestion,
    updateQuestion,
    deleteQuestion,
    reorderQuestions,
  }
})
