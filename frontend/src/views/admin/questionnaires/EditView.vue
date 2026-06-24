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
  Copy,
  X,
  Circle,
  Square,
  Loader2,
} from "lucide-vue-next";
import draggable from "vuedraggable";

const route = useRoute();
const router = useRouter();
const id = route.params.id;

const questionnaire = ref<any>(null);
const loading = ref(true);
const isSaving = ref(false);
const activeQuestionId = ref<any>(null);
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
  is_public: false,
  start_date: "",
  end_date: "",
});

// Question Form
const questionForm = ref({
  section: 1,
  type: "text",
  text: "",
  optionsList: ["Opsi 1"],
  allow_other: false,
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

async function fetchQuestionnaire(silent = false) {
  if (!silent) loading.value = true;
  try {
    const response = await api.get(`/admin/questionnaires/${id}`);
    questionnaire.value = response.data.data;
  } finally {
    if (!silent) loading.value = false;
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
    is_public: questionnaire.value.is_public || false,
    start_date: questionnaire.value.start_date,
    end_date: questionnaire.value.end_date,
  };
  showDetailsModal.value = true;
}

async function handleSaveDetails() {
  try {
    await api.put(`/admin/questionnaires/${id}`, detailsForm.value);
    showDetailsModal.value = false;
    fetchQuestionnaire(true);
  } catch (e) {
    console.error(e);
  }
}

function openEditQuestion(q: any) {
  editingQuestion.value = q;
  activeQuestionId.value = q.id;
  questionForm.value = {
    section: q.section,
    type: q.type,
    text: q.text,
    optionsList: Array.isArray(q.options)
      ? [...q.options]
      : typeof q.options === "object" && q.options !== null
        ? Object.values(q.options)
        : q.options ? [q.options] : [""],
    allow_other: q.allow_other || false,
    is_required: q.is_required,
  };
}

function resetForm() {
  editingQuestion.value = null;
  questionForm.value = {
    section: 1,
    type: "text",
    text: "",
    optionsList: [""],
    allow_other: false,
    is_required: true,
  };
}

function cancelEdit() {
  if (editingQuestion.value && typeof editingQuestion.value.id === 'string' && editingQuestion.value.id.startsWith('temp')) {
    fetchQuestionnaire(true);
  }
  activeQuestionId.value = null;
  resetForm();
}

function addNewQuestion() {
  const totalQuestions =
    questionnaire.value?.sections?.reduce(
      (acc: number, section: any) => acc + (section.questions?.length || 0),
      0
    ) || 0;

  if (!questionnaire.value.sections) {
    questionnaire.value.sections = [];
  }
  if (questionnaire.value.sections.length === 0) {
    questionnaire.value.sections.push({ section: 1, questions: [] });
  }
  
  const targetSection = questionnaire.value.sections[questionnaire.value.sections.length - 1];
  
  const tempId = `temp_${Date.now()}`;
  const newQ = {
    id: tempId,
    section: targetSection.section,
    type: "radio",
    text: "",
    options: [""],
    allow_other: false,
    is_required: true,
    order: totalQuestions + 1,
  };
  
  targetSection.questions.push(newQ);
  openEditQuestion(newQ);
}

function addNewSection() {
  const totalQuestions =
    questionnaire.value?.sections?.reduce(
      (acc: number, section: any) => acc + (section.questions?.length || 0),
      0
    ) || 0;

  if (!questionnaire.value.sections) {
    questionnaire.value.sections = [];
  }
  
  const maxSection = questionnaire.value.sections.reduce((max: number, section: any) => {
    return Math.max(max, section.section);
  }, 0);
  
  const newSectionNumber = maxSection + 1;
  
  questionnaire.value.sections.push({ section: newSectionNumber, questions: [] });
  
  const tempId = `temp_${Date.now()}`;
  const newQ = {
    id: tempId,
    section: newSectionNumber,
    type: "radio",
    text: "",
    options: [""],
    allow_other: false,
    is_required: true,
    order: totalQuestions + 1,
  };
  
  questionnaire.value.sections[questionnaire.value.sections.length - 1].questions.push(newQ);
  openEditQuestion(newQ);
}

async function handleDuplicateQuestion(q: any) {
  try {
    await api.post(`/admin/questionnaires/${id}/questions`, {
      section: q.section,
      type: q.type,
      question_text: q.text + " (Duplikat)",
      options: q.options,
      allow_other: q.allow_other,
      is_required: q.is_required,
      order: q.order + 1,
      questionnaire_id: id,
    });
    fetchQuestionnaire(true);
  } catch (e) {
    console.error("Failed to duplicate", e);
  }
}

async function handleSaveQuestion() {
  isSaving.value = true;
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
    options: questionForm.value.optionsList
      ? questionForm.value.optionsList.filter((o) => o.trim())
      : null,
    questionnaire_id: id,
  };

  try {
    if (editingQuestion.value && typeof editingQuestion.value.id === 'number') {
      await api.put(`/admin/questions/${editingQuestion.value.id}`, payload);
    } else {
      await api.post(`/admin/questionnaires/${id}/questions`, payload);
    }
    activeQuestionId.value = null;
    fetchQuestionnaire(true);
    resetForm();
  } catch (e) {
    console.error(e);
  } finally {
    isSaving.value = false;
  }
}

async function handleDeleteQuestion(qId: number) {
  if (!confirm("Hapus pertanyaan ini?")) return;
  try {
    await api.delete(`/admin/questions/${qId}`);
    fetchQuestionnaire(true);
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
    <div class="mx-auto max-w-6xl space-y-6">
      <!-- Header -->
      <div class="rounded-[2rem] border border-white/80 bg-white/[0.72] p-5 shadow-xl shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.055] md:p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
          <Button variant="outline" size="icon" class="rounded-2xl bg-white/70 dark:bg-slate-950/40" @click="router.back()">
            <ChevronLeft class="h-4 w-4" />
          </Button>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-black md:text-3xl">{{ questionnaire?.title }}</h1>
              <Button variant="ghost" size="icon" class="rounded-2xl" @click="openEditDetails">
                <Edit2 class="h-4 w-4" />
              </Button>
            </div>
            <p class="text-muted-foreground text-sm">
              {{ questionnaire?.year?.name }} {{ questionnaire?.year?.smt || '' }} •
              {{ questionnaire?.is_active ? "Aktif" : "Draft" }}
            </p>
          </div>
        </div>
        </div>

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
                      >{{ y.name }} {{ y.smt || '' }}</SelectItem
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
                        (e: any) => {
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
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="detailsForm.is_mandatory"
                  class="h-4 w-4"
                />
                <Label>Wajib Diisi</Label>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="detailsForm.is_public"
                  class="h-4 w-4"
                />
                <Label>Publikasi Publik (Bisa diakses tanpa login)</Label>
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
            <CardTitle class="text-lg font-black">Bagian {{ section.section }}</CardTitle>
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
                <div>
                  <!-- INLINE EDITOR -->
                  <div v-if="activeQuestionId === q.id" class="rounded-2xl border-t-8 border-t-emerald-600 border-x border-b border-slate-200 bg-white shadow-xl dark:bg-slate-900 overflow-hidden w-full my-4">
                  <div class="px-6 py-6 space-y-6">
                    <!-- Header Form -->
                    <div class="flex items-start gap-6">
                      <div class="flex-1">
                        <Input
                          v-model="questionForm.text"
                          placeholder="Pertanyaan tanpa judul"
                          class="text-2xl font-medium border-0 border-b-2 rounded-none px-1 h-12 focus-visible:ring-0 focus-visible:border-emerald-600 bg-transparent shadow-none"
                        />
                      </div>
                      <div class="w-1/3">
                        <Select v-model="questionForm.type">
                          <SelectTrigger class="h-12 rounded-xl">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="text">Teks Singkat</SelectItem>
                            <SelectItem value="textarea">Uraian</SelectItem>
                            <SelectItem value="radio">Pilihan Ganda (Radio)</SelectItem>
                            <SelectItem value="checkbox">Kotak Centang</SelectItem>
                            <SelectItem value="scale">Skala Likert</SelectItem>
                            <SelectItem value="select">Dropdown</SelectItem>
                            <SelectItem value="date">Tanggal</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </div>

                    <!-- Section & Options -->
                    <div class="space-y-4">
                      <div class="flex items-center gap-2">
                        <Label class="text-xs uppercase font-bold text-muted-foreground tracking-wider">Bagian (Section)</Label>
                        <Input type="number" v-model="questionForm.section" min="1" class="w-20 h-8 rounded-lg" />
                      </div>

                      <!-- Dynamic Options -->
                      <div v-if="['radio', 'checkbox', 'select', 'scale'].includes(questionForm.type)" class="space-y-3 pt-2">
                        <div v-for="(opt, idx) in questionForm.optionsList" :key="idx" class="flex items-center gap-3 group">
                          <Circle v-if="questionForm.type === 'radio'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                          <Square v-else-if="questionForm.type === 'checkbox'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                          <span v-else class="text-sm font-medium w-5 text-center shrink-0">{{ idx + 1 }}.</span>
                          
                          <Input 
                            v-model="questionForm.optionsList[idx]" 
                            class="border-0 border-b hover:border-b-2 focus-visible:ring-0 focus-visible:border-emerald-600 rounded-none bg-transparent shadow-none h-10 px-1 transition-all" 
                            :placeholder="'Opsi ' + (idx + 1)" 
                          />
                          
                          <Button 
                            variant="ghost" 
                            size="icon" 
                            @click="questionForm.optionsList.splice(idx, 1)" 
                            :disabled="questionForm.optionsList.length <= 1" 
                            class="text-muted-foreground hover:text-destructive h-8 w-8 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity"
                          >
                            <X class="h-4 w-4" />
                          </Button>
                        </div>
                        
                        <!-- Add Option row -->
                        <div class="flex items-center gap-3">
                          <Circle v-if="questionForm.type === 'radio'" class="h-5 w-5 text-muted-foreground/30 shrink-0" />
                          <Square v-else-if="questionForm.type === 'checkbox'" class="h-5 w-5 text-muted-foreground/30 shrink-0" />
                          <span v-else class="text-sm font-medium w-5 text-center text-muted-foreground/30 shrink-0">{{ questionForm.optionsList.length + 1 }}.</span>
                          
                          <div class="flex items-center flex-wrap gap-1 text-sm h-10">
                            <button @click="questionForm.optionsList.push('')" class="text-muted-foreground hover:border-b hover:border-muted-foreground transition-all">Tambah opsi</button>
                            <span v-if="['radio', 'checkbox'].includes(questionForm.type) && !questionForm.allow_other" class="text-muted-foreground px-1"> atau </span>
                            <button v-if="['radio', 'checkbox'].includes(questionForm.type) && !questionForm.allow_other" @click="questionForm.allow_other = true" class="text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-2 py-1 rounded-md font-medium transition-colors">Tambahkan "Yang Lain"</button>
                          </div>
                        </div>

                        <!-- Yang Lain Row -->
                        <div v-if="questionForm.allow_other" class="flex items-center gap-3 group">
                          <Circle v-if="questionForm.type === 'radio'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                          <Square v-else-if="questionForm.type === 'checkbox'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                          
                          <Input value="Yang lain: ........." readonly class="border-0 border-b rounded-none bg-transparent text-muted-foreground italic shadow-none h-10 px-1 pointer-events-none" />
                          
                          <Button variant="ghost" size="icon" @click="questionForm.allow_other = false" class="text-muted-foreground hover:text-destructive h-8 w-8 opacity-0 group-hover:opacity-100 transition-opacity">
                            <X class="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Toolbar/Footer -->
                  <div class="border-t bg-slate-50/50 dark:bg-slate-900/20 px-6 py-4 flex flex-row items-center justify-between sm:justify-between w-full">
                    <div class="flex items-center gap-2">
                      <div class="flex items-center gap-2 border-r pr-4">
                        <Label for="req" class="text-sm font-medium cursor-pointer">Wajib diisi</Label>
                        <div class="relative inline-flex items-center cursor-pointer">
                          <input type="checkbox" id="req" v-model="questionForm.is_required" class="sr-only peer">
                          <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </div>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <Button variant="ghost" @click="cancelEdit" class="rounded-xl">Batal</Button>
                      <Button @click="handleSaveQuestion" :disabled="isSaving" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 font-medium">
                        <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- READ-ONLY CARD -->
                <div v-else
                  @click="openEditQuestion(q)"
                  class="cursor-pointer group relative flex items-start gap-4 rounded-2xl border border-slate-200/80 bg-white/70 p-4 transition-colors hover:bg-emerald-50/60 dark:border-white/10 dark:bg-slate-950/35 dark:hover:bg-white/[0.07]"
                >
                  <GripVertical
                    class="h-5 w-5 text-muted-foreground mt-1 cursor-move opacity-0 group-hover:opacity-100"
                  />
                  <div class="flex-1 space-y-1">
                    <div class="flex items-start justify-between">
                      <p class="font-medium" :class="{'text-muted-foreground italic': !q.text}">
                        {{ q.text || 'Pertanyaan tanpa judul' }}
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
                      class="mt-4 space-y-3"
                    >
                      <div v-for="(opt, idx) in q.options" :key="idx" class="flex items-center gap-3">
                        <Circle v-if="q.type === 'radio'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                        <Square v-else-if="q.type === 'checkbox'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                        <span v-else-if="q.type === 'select'" class="text-sm font-medium w-5 text-center shrink-0">{{ idx + 1 }}.</span>
                        <span class="text-base text-slate-700 dark:text-slate-300">{{ opt || `Opsi ${idx + 1}` }}</span>
                      </div>
                      <div v-if="q.allow_other" class="flex items-center gap-3">
                        <Circle v-if="q.type === 'radio'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                        <Square v-else-if="q.type === 'checkbox'" class="h-5 w-5 text-muted-foreground/50 shrink-0" />
                        <div class="flex items-center gap-2 w-full max-w-sm">
                          <span class="text-base text-slate-700 dark:text-slate-300 whitespace-nowrap">Yang lain:</span>
                          <div class="border-b border-muted-foreground/40 w-full h-5"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <Button
                      variant="ghost"
                      size="icon"
                      @click.stop="handleDuplicateQuestion(q)"
                      title="Duplikat Pertanyaan"
                      ><Copy class="h-4 w-4"
                    /></Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      @click.stop="openEditQuestion(q)"
                      title="Edit Pertanyaan"
                      ><Edit2 class="h-4 w-4"
                    /></Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="text-destructive hover:text-destructive"
                      @click.stop="handleDeleteQuestion(q.id)"
                      ><Trash2 class="h-4 w-4"
                    /></Button>
                  </div>
                </div>
                </div>
              </template>
            </draggable>
          </CardContent>
        </Card>
        
        <!-- Action Buttons at Bottom -->
        <div class="flex flex-col md:flex-row justify-center items-center gap-4 py-8">
          <Button variant="outline" class="h-12 rounded-2xl border-emerald-200 text-emerald-700 px-6 font-bold hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-900/30 shadow-sm" @click="addNewSection"
              ><Plus class="mr-2 h-5 w-5" /> Tambah Bagian</Button>
          <Button class="h-12 rounded-2xl bg-emerald-600 px-6 font-bold shadow-lg shadow-emerald-700/20 hover:bg-emerald-700" @click="addNewQuestion"
              ><Plus class="mr-2 h-5 w-5" /> Tambah Pertanyaan</Button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
