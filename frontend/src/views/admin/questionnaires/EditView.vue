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
import {
  ChevronLeft,
  Plus,
  Trash2,
  Edit2,
  GripVertical,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const id = route.params.id;

const questionnaire = ref<any>(null);
const loading = ref(true);
const showQuestionModal = ref(false);
const editingQuestion = ref<any>(null);

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
});

async function fetchQuestionnaire() {
  loading.value = true;
  try {
    const response = await api.get(`/admin/questionnaires/${id}`);
    questionnaire.value = response.data.data;
  } finally {
    loading.value = false;
  }
}

function openEditQuestion(q: any) {
  editingQuestion.value = q;
  questionForm.value = {
    section: q.section,
    type: q.type,
    text: q.text,
    options: Array.isArray(q.options) ? q.options.join("\n") : q.options,
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
  const payload = {
    ...questionForm.value,
    options: questionForm.value.options
      ? questionForm.value.options.split("\n").filter((o) => o.trim())
      : null,
    questionnaire_id: id,
  };

  try {
    if (editingQuestion.value) {
      await api.put(`/admin/questions/${editingQuestion.value.id}`, payload);
    } else {
      await api.post("/admin/questions", payload);
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
            <h1 class="text-2xl font-bold">{{ questionnaire?.title }}</h1>
            <p class="text-muted-foreground text-sm">
              Edit pertanyaan dan struktur
            </p>
          </div>
        </div>
        <Dialog v-model:open="showQuestionModal">
          <DialogTrigger as-child>
            <Button @click="resetForm"
              ><Plus class="mr-2 h-4 w-4" /> Tambah Pertanyaan</Button
            >
          </DialogTrigger>
          <DialogContent class="sm:max-w-[500px]">
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
                  ['radio', 'checkbox', 'select'].includes(questionForm.type)
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
                <!-- Simple check input for required -->
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
          <CardContent class="grid gap-4">
            <div
              v-for="q in section.questions"
              :key="q.id"
              class="group flex items-start gap-4 rounded-lg border p-4 bg-card hover:bg-muted/50 transition-colors relative"
            >
              <GripVertical
                class="h-5 w-5 text-muted-foreground mt-1 cursor-move opacity-0 group-hover:opacity-100"
              />
              <div class="flex-1 space-y-1">
                <div class="flex items-start justify-between">
                  <p class="font-medium">
                    {{ q.text }}
                    <span v-if="q.is_required" class="text-destructive">*</span>
                  </p>
                  <Badge variant="outline" class="text-xs">{{ q.type }}</Badge>
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
                <Button variant="ghost" size="icon" @click="openEditQuestion(q)"
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
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
