<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useQuestionnaireStore } from "../../stores/questionnaire";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
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
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
// import { Checkbox } from "@/components/ui/checkbox";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { ChevronLeft, ChevronRight, CheckCircle, Info } from "lucide-vue-next";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

const route = useRoute();
const router = useRouter();
const qStore = useQuestionnaireStore();

const currentSection = ref(1);
const answers = ref<Record<number, any>>({});
const showSuccess = ref(false);

const questionnaireId = computed(() => Number(route.params.id));
const questionnaire = computed(() => qStore.currentQuestionnaire);
const sections = computed(() => questionnaire.value?.sections || []);
const totalSections = computed(() => sections.value.length);
const currentSectionData = computed(() =>
  sections.value.find((s) => s.section === currentSection.value)
);
const progress = computed(() =>
  Math.round((currentSection.value / totalSections.value) * 100)
);

const isReadOnly = computed(() => !!questionnaire.value?.has_submitted);

watch(
  questionnaire,
  (newQ) => {
    if (newQ?.submitted_answers) {
      answers.value = { ...newQ.submitted_answers };
    }
  },
  { immediate: true }
);

onMounted(() => {
  qStore.fetchQuestionnaire(questionnaireId.value);
});

function shouldShowQuestion(question: any): boolean {
  if (!question.depends_on) return true;
  return answers.value[question.depends_on] === question.depends_value;
}

const isSectionValid = computed(() => {
  // If read only, valid by default
  if (isReadOnly.value) return true;
  if (!currentSectionData.value) return false;

  for (const question of currentSectionData.value.questions) {
    if (!shouldShowQuestion(question)) continue;
    if (question.is_required) {
      const val = answers.value[question.id];

      // Check for arrays (Checkbox)
      if (Array.isArray(val)) {
        if (val.length === 0) return false;
      }
      // Check for empty values
      else if (val === null || val === undefined || val === "") {
        // Special case for number 0
        if (typeof val === "number" && val === 0) return true;
        return false;
      }
    }
  }
  return true;
});

function nextSection() {
  if (currentSection.value < totalSections.value) currentSection.value++;
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function prevSection() {
  if (currentSection.value > 1) currentSection.value--;
  window.scrollTo({ top: 0, behavior: "smooth" });
}

async function submitQuestionnaire() {
  const formattedAnswers = Object.entries(answers.value).map(
    ([questionId, value]) => ({ question_id: Number(questionId), value })
  );
  const success = await qStore.submitQuestionnaire(
    questionnaireId.value,
    formattedAnswers
  );
  if (success) {
    showSuccess.value = true;
  }
}

function toggleCheckbox(questionId: number, option: string, checked: boolean) {
  const current = (answers.value[questionId] as string[]) || [];
  let newValue;
  if (checked) {
    if (!current.includes(option)) {
      newValue = [...current, option];
    } else {
      newValue = current;
    }
  } else {
    newValue = current.filter((v) => v !== option);
  }

  answers.value[questionId] = newValue;
}

function handleSuccessClose() {
  showSuccess.value = false;
  router.push("/");
}
</script>

<template>
  <div class="min-h-screen bg-muted/100 py-8 px-4">
    <div class="container mx-auto max-w-3xl">
      <Alert
        v-if="isReadOnly"
        class="mb-6 border-yellow-500/50 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400"
      >
        <Info class="h-4 w-4" />
        <AlertTitle>Mode Baca Saja</AlertTitle>
        <AlertDescription>
          Anda sudah mengisi kuesioner ini. Jawaban Anda ditampilkan di bawah
          ini.
        </AlertDescription>
      </Alert>

      <!-- Header -->
      <Card class="mb-6">
        <CardHeader>
          <div class="flex items-center justify-between mb-2">
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/')"
              class="-ml-2"
            >
              <ChevronLeft class="h-4 w-4 mr-1" />
              Kembali
            </Button>
            <span class="text-sm text-muted-foreground"
              >Bagian {{ currentSection }} dari {{ totalSections }}</span
            >
          </div>
          <CardTitle>{{ questionnaire?.title }}</CardTitle>
          <CardDescription>{{ questionnaire?.description }}</CardDescription>
        </CardHeader>
        <CardContent>
          <Progress :model-value="progress" class="h-2" />
        </CardContent>
      </Card>

      <!-- Questions -->
      <Card v-if="currentSectionData">
        <CardHeader>
          <CardTitle class="text-lg">Bagian {{ currentSection }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-8">
          <template
            v-for="question in currentSectionData.questions"
            :key="question.id"
          >
            <div v-if="shouldShowQuestion(question)" class="space-y-3">
              <Label class="text-base font-medium">
                {{ question.text }}
                <span v-if="question.is_required" class="text-destructive"
                  >*</span
                >
              </Label>

              <!-- Text Input -->
              <Input
                v-if="question.type === 'text'"
                v-model="answers[question.id]"
                placeholder="Jawaban Anda"
                :disabled="isReadOnly"
              />

              <!-- Textarea -->
              <Textarea
                v-else-if="question.type === 'textarea'"
                v-model="answers[question.id]"
                placeholder="Jawaban Anda"
                rows="3"
                :disabled="isReadOnly"
              />

              <!-- Number -->
              <Input
                v-else-if="question.type === 'number'"
                v-model.number="answers[question.id]"
                type="number"
                :disabled="isReadOnly"
              />

              <!-- Date -->
              <Input
                v-else-if="question.type === 'date'"
                v-model="answers[question.id]"
                type="date"
                :disabled="isReadOnly"
              />

              <!-- Radio -->
              <RadioGroup
                v-else-if="question.type === 'radio'"
                v-model="answers[question.id]"
                :disabled="isReadOnly"
              >
                <div
                  v-for="option in question.options"
                  :key="option"
                  class="flex items-center space-x-2"
                >
                  <RadioGroupItem
                    :id="`${question.id}-${option}`"
                    :value="option"
                  />
                  <Label
                    :for="`${question.id}-${option}`"
                    class="font-normal"
                    >{{ option }}</Label
                  >
                </div>
              </RadioGroup>

              <!-- Checkbox -->
              <div
                v-else-if="question.type === 'checkbox'"
                class="flex flex-col space-y-2"
              >
                <!-- Simple string array/single value handling for checkbox is tricky, assuming array for multiple -->
                <div
                  v-for="option in question.options"
                  :key="option"
                  class="flex items-center space-x-2 cursor-pointer"
                  @click="
                    () => {
                      if (isReadOnly) return;
                      const current = answers[question.id] || [];
                      const isChecked = current.includes(option);
                      toggleCheckbox(question.id, option, !isChecked);
                    }
                  "
                >
                  <!-- Manual Checkbox Implementation -->
                  <div
                    class="grid place-content-center h-4 w-4 shrink-0 rounded-sm border border-primary shadow transition-colors"
                    :class="{
                      'bg-primary text-primary-foreground': (
                        answers[question.id] || []
                      ).includes(option),
                      'opacity-50 cursor-not-allowed': isReadOnly,
                    }"
                  >
                    <Check
                      v-if="(answers[question.id] || []).includes(option)"
                      class="h-3 w-3 text-white"
                      stroke-width="3"
                    />
                  </div>
                  <Label
                    :for="`${question.id}-${option}`"
                    class="font-normal cursor-pointer pointer-events-none"
                    >{{ option }}</Label
                  >
                </div>
              </div>

              <!-- Select -->
              <Select
                v-else-if="question.type === 'select'"
                v-model="answers[question.id]"
                :disabled="isReadOnly"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Pilih jawaban..." />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="option in question.options"
                    :key="option"
                    :value="option"
                  >
                    {{ option }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <!-- Scale (Range) - using Slider if available or basic range input styled -->
              <div v-else-if="question.type === 'scale'" class="space-y-4">
                <div class="flex items-center gap-4">
                  <div class="flex-1">
                    <input
                      type="range"
                      v-model.number="answers[question.id]"
                      :min="1"
                      :max="question.options?.length || 5"
                      class="w-full h-2 bg-secondary rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-primary"
                      :disabled="isReadOnly"
                    />
                  </div>
                  <span
                    class="font-bold border p-2 rounded-md w-12 text-center"
                    >{{ answers[question.id] || "-" }}</span
                  >
                </div>
                <div
                  class="flex justify-between text-xs text-muted-foreground px-1"
                >
                  <span>1 (Sangat Kurang)</span>
                  <span>{{ question.options?.length || 5 }} (Sangat Baik)</span>
                </div>
              </div>
            </div>
          </template>
        </CardContent>
        <CardFooter class="flex justify-between">
          <Button
            v-if="currentSection > 1"
            variant="outline"
            @click="prevSection"
          >
            <ChevronLeft class="mr-2 h-4 w-4" />
            Sebelumnya
          </Button>
          <div v-else></div>

          <Button
            v-if="currentSection < totalSections"
            @click="nextSection"
            :disabled="!isSectionValid"
          >
            Selanjutnya
            <ChevronRight class="ml-2 h-4 w-4" />
          </Button>
          <Button
            v-else-if="!isReadOnly"
            variant="default"
            @click="submitQuestionnaire"
            :disabled="!isSectionValid || qStore.submitting"
          >
            <span
              v-if="qStore.submitting"
              class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
            ></span>
            Submit
          </Button>
          <div v-else class="flex items-center gap-4">
            <div
              class="text-muted-foreground text-sm flex items-center font-medium"
            >
              <CheckCircle class="w-4 h-4 mr-2" /> Sudah Dikirim
            </div>
            <Button variant="outline" @click="router.push('/')">
              Kembali
            </Button>
          </div>
        </CardFooter>
      </Card>

      <!-- Success Dialog -->
      <Dialog :open="showSuccess" @update:open="handleSuccessClose">
        <DialogContent class="sm:max-w-md text-center">
          <DialogHeader>
            <div class="mx-auto bg-green-100 p-3 rounded-full w-fit mb-4">
              <CheckCircle class="h-8 w-8 text-green-600" />
            </div>
            <DialogTitle>Terima Kasih!</DialogTitle>
            <DialogDescription>
              Jawaban kuesioner Anda telah berhasil disimpan.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="sm:justify-center">
            <Button @click="handleSuccessClose"> Kembali ke Dashboard </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
