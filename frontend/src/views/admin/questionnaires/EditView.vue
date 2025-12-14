<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import {
  ChevronLeft,
  Plus,
  Trash2,
  Edit2,
  GripVertical,
} from "lucide-vue-next";
import draggable from "vuedraggable";

const route = useRoute();
const router = useRouter();
const id = route.params.id;

const questionnaire = ref<any>(null);
const loading = ref(true);
const showQuestionModal = ref(false);
const showDetailsModal = ref(false);
const editingQuestion = ref<any>(null);
const years = ref<any[]>([]);
const prodis = ref<any[]>([]);

const detailsForm = ref({
  title: "",
  description: "",
  year_id: "",
  prodi_ids: [] as number[],
  is_active: true,
  is_mandatory: false,
  start_date: "",
  end_date: "",
});

// Question Form
const questionForm = ref({
  section: 1,
  type: "text",
  text: "",
  options: "",
  is_required: true,
});

onMounted(() => {
  fetchQuestionnaire();
  fetchOptions();
});

async function fetchOptions() {
  try {
    const [yearRes, prodiRes] = await Promise.all([
      api.get("/admin/years?per_page=100"),
      api.get("/prodis?per_page=100"),
    ]);
    years.value = yearRes.data.data;
    prodis.value = prodiRes.data.data;
  } catch (e) {
    console.error("Failed to fetch options", e);
  }
}

async function fetchQuestionnaire() {
  loading.value = true;
  try {
    const response = await api.get(`/admin/questionnaires/${id}`);
    questionnaire.value = response.data.data;
  } finally {
    loading.value = false;
  }
}

function openEditDetails() {
  if (!questionnaire.value) return;
  detailsForm.value = {
    title: questionnaire.value.title,
    description: questionnaire.value.description,
    year_id:
      questionnaire.value.year_id?.toString() ||
      questionnaire.value.year?.id?.toString(),
    prodi_ids: questionnaire.value.prodis?.map((p: any) => p.id) || [],
    is_active: questionnaire.value.is_active,
    is_mandatory: questionnaire.value.is_mandatory,
    start_date: questionnaire.value.start_date,
    end_date: questionnaire.value.end_date,
  };
  showDetailsModal.value = true;
}

async function handleSaveDetails() {
  try {
    await api.put(`/admin/questionnaires/${id}`, detailsForm.value);
    showDetailsModal.value = false;
    fetchQuestionnaire();
  } catch (e) {
    console.error(e);
  }
}

function openEditQuestion(q: any) {
  editingQuestion.value = q;
  questionForm.value = {
    section: q.section,
    type: q.type,
    text: q.text,
    options: Array.isArray(q.options)
      ? q.options.join("\n")
      : typeof q.options === "object" && q.options !== null
        ? Object.values(q.options).join("\n")
        : q.options,
    is_required: q.is_required,
  };
  showQuestionModal.value = true;
}

function resetForm() {
  editingQuestion.value = null;
  questionForm.value = {
    section: 1,
    type: "text",
    text: "",
    options: "",
    is_required: true,
  };
}

async function handleSaveQuestion() {
  // Calculate order (simple auto-increment based on total questions)
  const totalQuestions =
    questionnaire.value?.sections?.reduce(
      (acc: number, section: any) => acc + (section.questions?.length || 0),
      0
    ) || 0;

  const payload = {
    ...questionForm.value,
    question_text: questionForm.value.text, // Backend expects question_text
    order: editingQuestion.value
      ? editingQuestion.value.order
      : totalQuestions + 1,
    options: questionForm.value.options
      ? questionForm.value.options.split("\n").filter((o) => o.trim())
      : null,
    questionnaire_id: id,
  };

  try {
    if (editingQuestion.value) {
      // For update, we might also need to ensure question_text is sent if backend requires it
      await api.put(`/admin/questions/${editingQuestion.value.id}`, payload);
    } else {
      // Use correct nested endpoint for creation
      await api.post(`/admin/questionnaires/${id}/questions`, payload);
    }
    showQuestionModal.value = false;
    fetchQuestionnaire();
    resetForm();
  } catch (e) {
    console.error(e);
  }
}

async function handleDeleteQuestion(qId: number) {
  if (!confirm("Hapus pertanyaan ini?")) return;
  try {
    await api.delete(`/admin/questions/${qId}`);
    fetchQuestionnaire();
  } catch (e) {
    console.error(e);
  }
}

async function handleReorder() {
  // Re-calculate order and section for all questions across all sections
  const updates: any[] = [];

  if (!questionnaire.value?.sections) return;

  questionnaire.value.sections.forEach((section: any) => {
    section.questions.forEach((q: any, index: number) => {
      // Update order (1-based) and section if changed
      updates.push({
        id: q.id,
        order: index + 1,
        section: section.section,
      });
      // Update local state to reflect new section if moved
      q.section = section.section;
      q.order = index + 1;
    });
  });

  try {
    // Check route: POST /api/admin/questionnaires/{id}/questions/reorder
    await api.post(`/admin/questionnaires/${id}/questions/reorder`, {
      questions: updates,
    });
  } catch (e) {
    console.error("Failed to reorder", e);
  }
}
</script>

<template>
  <AdminLayout>
    <div class="p-4 container mx-auto max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button variant="outline" size="icon" @click="router.back()">
            <ChevronLeft class="h-4 w-4" />
          </Button>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold">{{ questionnaire?.title }}</h1>
              <Button variant="ghost" size="icon" @click="openEditDetails">
                <Edit2 class="h-4 w-4" />
              </Button>
            </div>
            <p class="text-muted-foreground text-sm">
              {{ questionnaire?.year?.name }} •
              {{ questionnaire?.is_active ? "Aktif" : "Draft" }}
            </p>
          </div>
        </div>

        <!-- Question Modal Trigger -->
        <Dialog v-model:open="showQuestionModal">
          <DialogTrigger as-child>
            <Button @click="resetForm"
              ><Plus class="mr-2 h-4 w-4" /> Tambah Pertanyaan</Button
            >
          </DialogTrigger>
          <DialogContent class="sm:max-w-[500px]">
            <!-- ... existing question modal content ... -->
            <DialogHeader>
              <DialogTitle>{{
                editingQuestion ? "Edit Pertanyaan" : "Tambah Pertanyaan"
              }}</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Section</Label>
                  <Input type="number" v-model="questionForm.section" min="1" />
                </div>
                <div class="space-y-2">
                  <Label>Tipe</Label>
                  <Select v-model="questionForm.type">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="text">Teks Singkat</SelectItem>
                      <SelectItem value="textarea">Uraian</SelectItem>
                      <SelectItem value="radio"
                        >Pilihan Ganda (Radio)</SelectItem
                      >
                      <SelectItem value="checkbox">Kotak Centang</SelectItem>
                      <SelectItem value="scale">Skala Likert</SelectItem>
                      <SelectItem value="select">Dropdown</SelectItem>
                      <SelectItem value="date">Tanggal</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="space-y-2">
                <Label>Pertanyaan</Label>
                <Textarea
                  v-model="questionForm.text"
                  placeholder="Tulis pertanyaan..."
                  rows="2"
                />
              </div>

              <div
                class="space-y-2"
                v-if="
                  ['radio', 'checkbox', 'select', 'scale'].includes(
                    questionForm.type
                  )
                "
              >
                <Label>Opsi Jawaban (pisahkan dengan baris baru)</Label>
                <Textarea
                  v-model="questionForm.options"
                  placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"
                  rows="4"
                />
              </div>

              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  id="req"
                  v-model="questionForm.is_required"
                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <Label for="req">Wajib Diisi</Label>
              </div>
            </div>
            <DialogFooter>
              <Button variant="secondary" @click="showQuestionModal = false"
                >Batal</Button
              >
              <Button @click="handleSaveQuestion">Simpan</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        <!-- Edit Details Modal -->
        <Dialog v-model:open="showDetailsModal">
          <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Edit Detail Kuesioner</DialogTitle>
            </DialogHeader>
            <div class="space-y-4 py-4">
              <div class="space-y-2">
                <Label>Judul</Label>
                <Input v-model="detailsForm.title" />
              </div>
              <div class="space-y-2">
                <Label>Deskripsi</Label>
                <Textarea v-model="detailsForm.description" />
              </div>
              <div class="space-y-2">
                <Label>Tahun</Label>
                <Select v-model="detailsForm.year_id">
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="y in years"
                      :key="y.id"
                      :value="y.id.toString()"
                      >{{ y.name }}</SelectItem
                    >
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label>Target Prodi</Label>
                <div
                  class="border rounded-md p-4 max-h-48 overflow-y-auto space-y-2"
                >
                  <div
                    v-for="prodi in prodis"
                    :key="prodi.id"
                    class="flex items-center space-x-2"
                  >
                    <input
                      type="checkbox"
                      :checked="detailsForm.prodi_ids.includes(prodi.id)"
                      @change="
                        (e) => {
                          if (e.target.checked)
                            detailsForm.prodi_ids.push(prodi.id);
                          else
                            detailsForm.prodi_ids =
                              detailsForm.prodi_ids.filter(
                                (id) => id !== prodi.id
                              );
                        }
                      "
                      class="h-4 w-4"
                    />
                    <Label>{{ prodi.name }}</Label>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="detailsForm.is_active"
                  class="h-4 w-4"
                />
                <Label>Aktif</Label>
              </div>
            </div>
            <DialogFooter>
              <Button variant="secondary" @click="showDetailsModal = false"
                >Batal</Button
              >
              <Button @click="handleSaveDetails">Simpan Perubahan</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      <!-- Question List -->
      <div v-if="loading" class="flex justify-center py-12">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
        ></div>
      </div>

      <div v-else class="grid gap-6">
        <Card
          v-for="section in questionnaire?.sections"
          :key="section.section"
          class="border-l-4 border-l-primary/50"
        >
          <CardHeader class="pb-2">
            <CardTitle class="text-lg">Bagian {{ section.section }}</CardTitle>
          </CardHeader>
          <CardContent>
            <draggable
              v-model="section.questions"
              group="questions"
              @change="handleReorder"
              item-key="id"
              class="grid gap-4"
              handle=".cursor-move"
            >
              <template #item="{ element: q }">
                <div
                  class="group flex items-start gap-4 rounded-lg border p-4 bg-card hover:bg-muted/50 transition-colors relative"
                >
                  <GripVertical
                    class="h-5 w-5 text-muted-foreground mt-1 cursor-move opacity-0 group-hover:opacity-100"
                  />
                  <div class="flex-1 space-y-1">
                    <div class="flex items-start justify-between">
                      <p class="font-medium">
                        {{ q.text }}
                        <span v-if="q.is_required" class="text-destructive"
                          >*</span
                        >
                      </p>
                      <Badge variant="outline" class="text-xs">{{
                        q.type
                      }}</Badge>
                    </div>
                    <div
                      v-if="['radio', 'checkbox', 'select'].includes(q.type)"
                      class="text-sm text-muted-foreground pl-2 border-l-2"
                    >
                      <div v-for="opt in q.options" :key="opt">• {{ opt }}</div>
                    </div>
                  </div>
                  <div
                    class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <Button
                      variant="ghost"
                      size="icon"
                      @click="openEditQuestion(q)"
                      ><Edit2 class="h-4 w-4"
                    /></Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="text-destructive hover:text-destructive"
                      @click="handleDeleteQuestion(q.id)"
                      ><Trash2 class="h-4 w-4"
                    /></Button>
                  </div>
                </div>
              </template>
            </draggable>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
