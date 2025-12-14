import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../services/api";

interface Questionnaire {
  id: number;
  title: string;
  description: string;
  year: { id: number; name: string };
  is_mandatory: boolean;

  questions_count: number;
  start_date: string;
  end_date: string;
  has_submitted: boolean;
}

interface Section {
  section: number;
  questions: Question[];
}

interface Question {
  id: number;
  text: string;
  type: string;
  options: any;
  is_required: boolean;
  depends_on: number | null;
  depends_value: string | null;
}

interface QuestionnaireDetail {
  id: number;
  title: string;
  description: string;
  year: string;
  is_mandatory: boolean;
  sections: Section[];
  has_submitted?: boolean;
  submitted_answers?: Record<string, any>;
}

export const useQuestionnaireStore = defineStore("questionnaire", () => {
  const questionnaires = ref<Questionnaire[]>([]);
  const currentQuestionnaire = ref<QuestionnaireDetail | null>(null);
  const loading = ref(false);
  const submitting = ref(false);
  const error = ref<string | null>(null);

  const meta = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 9, // Default to 9 for grid 3x3
  });

  const counts = ref({
    all: 0,
    completed: 0,
    pending: 0,
  });

  async function fetchCounts() {
    try {
      const response = await api.get("/questionnaires/counts");
      counts.value = response.data;
    } catch (e) {
      console.error("Failed to fetch counts", e);
    }
  }

  async function fetchQuestionnaires(
    page = 1,
    append = false,
    sortBy = "newest",
    sortOrder = "desc",
    status = "pending",
    search = "" // Add search param
  ) {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get("/questionnaires", {
        params: {
          page,
          per_page: meta.value.per_page,
          sort_by: sortBy,
          sort_order: sortOrder,
          status,
          search,
        },
      });
      
      const newData = response.data.data;
      if (append) {
        questionnaires.value = [...questionnaires.value, ...newData];
      } else {
        questionnaires.value = newData;
      }
      
      meta.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
        per_page: response.data.per_page,
      };
    } catch (e: any) {
      error.value = e.response?.data?.message || "Gagal memuat kuesioner";
    } finally {
      loading.value = false;
    }
  }

  async function fetchQuestionnaire(id: number) {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get(`/questionnaires/${id}`);
      currentQuestionnaire.value = response.data.data;
    } catch (e: any) {
      error.value = e.response?.data?.message || "Gagal memuat kuesioner";
    } finally {
      loading.value = false;
    }
  }

  async function submitQuestionnaire(
    id: number,
    answers: { question_id: number; value: any }[]
  ) {
    submitting.value = true;
    error.value = null;
    try {
      await api.post(`/questionnaires/${id}/submit`, { answers });
      return true;
    } catch (e: any) {
      error.value = e.response?.data?.message || "Gagal mengirim jawaban";
      return false;
    } finally {
      submitting.value = false;
    }
  }

  return {
    questionnaires,
    currentQuestionnaire,
    loading,
    submitting,
    meta,
    error,
    fetchQuestionnaires,
    fetchQuestionnaire,
    submitQuestionnaire,
    counts,
    fetchCounts,
  };
});
