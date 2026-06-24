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
import { Check, ChevronLeft, ChevronRight, CheckCircle, Info } from "lucide-vue-next";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

const route = useRoute();
const router = useRouter();
const qStore = useQuestionnaireStore();

const currentSection = ref(1);
const answers = ref<Record<number, any>>({});
const otherTexts = ref<Record<number, string>>({});
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
      const newAnswers = { ...newQ.submitted_answers };
      
      if (newQ.sections) {
        newQ.sections.forEach((section: any) => {
          section.questions.forEach((question: any) => {
            const val = newAnswers[question.id];
            if (!val) return;

            if (question.allow_other) {
              if (question.type === 'radio') {
                if (!question.options.includes(val)) {
                  otherTexts.value[question.id] = val;
                  newAnswers[question.id] = '__other__';
                }
              } else if (question.type === 'checkbox' && Array.isArray(val)) {
                const checkedOptions = val.map((v: string) => {
                  if (!question.options.includes(v)) {
                    otherTexts.value[question.id] = v;
                    return '__other__';
                  }
                  return v;
                });
                newAnswers[question.id] = checkedOptions;
              }
            }
          });
        });
      }
      
      answers.value = newAnswers;
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
    ([questionId, value]) => {
      const qId = Number(questionId);
      let resolvedValue = value;

      // Replace __other__ with actual typed text
      if (value === '__other__') {
        resolvedValue = otherTexts.value[qId] || '';
      } else if (Array.isArray(value) && value.includes('__other__')) {
        resolvedValue = value.map((v: string) =>
          v === '__other__' ? (otherTexts.value[qId] || '') : v
        ).filter((v: string) => v !== '');
      }

      return { question_id: qId, value: resolvedValue };
    }
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
  router.push("/home");
}
</script>

<template>
  <div class="min-h-screen overflow-hidden bg-transparent px-4 py-8 text-slate-950 dark:text-white">
    <div class="pointer-events-none fixed inset-0 -z-10">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(20,184,166,0.14),transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.88),rgba(240,253,244,0.9))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(45,212,191,0.1),transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.98),rgba(6,30,24,0.95))]"
      />
      <div class="absolute -top-32 left-1/4 h-[26rem] w-[26rem] rounded-full bg-emerald-500/12 blur-3xl dark:bg-emerald-400/10" />
      <div class="absolute bottom-0 right-0 h-[24rem] w-[24rem] rounded-full bg-teal-500/10 blur-3xl dark:bg-teal-400/10" />
    </div>
    <div class="container mx-auto max-w-3xl">
      <Alert
        v-if="isReadOnly"
        class="mb-6 rounded-2xl border-amber-500/50 bg-amber-500/10 text-amber-700 dark:text-amber-300"
      >
        <Info class="h-4 w-4" />
        <AlertTitle>Mode Baca Saja</AlertTitle>
        <AlertDescription>
          Anda sudah mengisi kuesioner ini. Jawaban Anda ditampilkan di bawah
          ini.
        </AlertDescription>
      </Alert>

      <!-- Header -->
      <Card class="mb-6 rounded-[2rem] border-white/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]">
        <CardHeader>
          <div class="flex items-center justify-between mb-2">
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/home')"
              class="-ml-2 rounded-2xl"
            >
              <ChevronLeft class="h-4 w-4 mr-1" />
              Kembali
            </Button>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-200"
              >Bagian {{ currentSection }} dari {{ totalSections }}</span
            >
          </div>
          <CardTitle class="text-3xl font-black tracking-tight">{{ questionnaire?.title }}</CardTitle>
          <CardDescription class="pt-2 leading-7 text-slate-600 dark:text-slate-300">{{ questionnaire?.description }}</CardDescription>
        </CardHeader>
        <CardContent>
          <Progress :model-value="progress" class="h-3 rounded-full" />
          <p class="mt-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
            Progress {{ progress }}%
          </p>
        </CardContent>
      </Card>

      <Card v-if="currentSectionData" class="rounded-[2rem] border-white/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]">
        <CardHeader>
          <CardTitle class="text-xl font-black text-emerald-700 dark:text-emerald-300">Bagian {{ currentSection }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-8">
          <template
            v-for="question in currentSectionData.questions"
            :key="question.id"
          >
            <div
              v-if="shouldShowQuestion(question)"
              class="space-y-3 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/70 p-5 dark:border-white/10 dark:bg-slate-950/40"
            >
              <Label class="text-base font-semibold leading-7">
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
                class="rounded-2xl bg-white/80 dark:bg-slate-950/50"
              />

              <!-- Textarea -->
              <Textarea
                v-else-if="question.type === 'textarea'"
                v-model="answers[question.id]"
                placeholder="Jawaban Anda"
                rows="3"
                :disabled="isReadOnly"
                class="rounded-2xl bg-white/80 dark:bg-slate-950/50"
              />

              <!-- Number -->
              <Input
                v-else-if="question.type === 'number'"
                v-model.number="answers[question.id]"
                type="number"
                :disabled="isReadOnly"
                class="rounded-2xl bg-white/80 dark:bg-slate-950/50"
              />

              <!-- Date -->
              <Input
                v-else-if="question.type === 'date'"
                v-model="answers[question.id]"
                type="date"
                :disabled="isReadOnly"
                class="rounded-2xl bg-white/80 dark:bg-slate-950/50"
              />

              <!-- Radio -->
              <RadioGroup
                v-else-if="question.type === 'radio'"
                v-model="answers[question.id]"
                :disabled="isReadOnly"
                class="space-y-3"
              >
                <div
                  v-for="option in question.options"
                  :key="option"
                  class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]"
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
                <!-- Yang Lain option -->
                <div
                  v-if="question.allow_other"
                  class="space-y-2 rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]"
                >
                  <div class="flex items-center space-x-2">
                    <RadioGroupItem
                      :id="`${question.id}-other`"
                      value="__other__"
                    />
                    <Label :for="`${question.id}-other`" class="font-normal"
                      >Yang lain</Label
                    >
                  </div>
                  <Input
                    v-if="answers[question.id] === '__other__'"
                    v-model="otherTexts[question.id]"
                    placeholder="Tulis jawaban lainnya..."
                    :disabled="isReadOnly"
                    class="mt-2 rounded-xl bg-white/80 dark:bg-slate-950/50"
                  />
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
                  class="flex cursor-pointer items-center space-x-2 rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]"
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
                    class="grid h-5 w-5 shrink-0 place-content-center rounded-md border border-emerald-500 shadow transition-colors"
                    :class="{
                      'bg-emerald-600 text-white': (
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
                <!-- Yang Lain option -->
                <div
                  v-if="question.allow_other"
                  class="space-y-2 rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]"
                >
                  <div
                    class="flex cursor-pointer items-center space-x-2"
                    @click="
                      () => {
                        if (isReadOnly) return;
                        const current = answers[question.id] || [];
                        const isChecked = current.includes('__other__');
                        toggleCheckbox(question.id, '__other__', !isChecked);
                        if (isChecked) {
                          otherTexts[question.id] = '';
                        }
                      }
                    "
                  >
                    <div
                      class="grid h-5 w-5 shrink-0 place-content-center rounded-md border border-emerald-500 shadow transition-colors"
                      :class="{
                        'bg-emerald-600 text-white': (answers[question.id] || []).includes('__other__'),
                        'opacity-50 cursor-not-allowed': isReadOnly,
                      }"
                    >
                      <Check
                        v-if="(answers[question.id] || []).includes('__other__')"
                        class="h-3 w-3 text-white"
                        stroke-width="3"
                      />
                    </div>
                    <Label class="font-normal cursor-pointer pointer-events-none"
                      >Yang lain</Label
                    >
                  </div>
                  <Input
                    v-if="(answers[question.id] || []).includes('__other__')"
                    v-model="otherTexts[question.id]"
                    placeholder="Tulis jawaban lainnya..."
                    :disabled="isReadOnly"
                    class="mt-2 rounded-xl bg-white/80 dark:bg-slate-950/50"
                  />
                </div>
              </div>

              <!-- Select -->
              <Select
                v-else-if="question.type === 'select'"
                v-model="answers[question.id]"
                :disabled="isReadOnly"
              >
                <SelectTrigger class="rounded-2xl bg-white/80 dark:bg-slate-950/50">
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
                      class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-secondary accent-emerald-600 dark:bg-slate-800"
                      :disabled="isReadOnly"
                    />
                  </div>
                  <span
                    class="w-12 rounded-2xl border border-emerald-200 bg-white p-2 text-center font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-slate-950/50 dark:text-emerald-300"
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
            class="rounded-2xl"
          >
            <ChevronLeft class="mr-2 h-4 w-4" />
            Sebelumnya
          </Button>
          <div v-else></div>

          <Button
            v-if="currentSection < totalSections"
            @click="nextSection"
            :disabled="!isSectionValid"
            class="rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
          >
            Selanjutnya
            <ChevronRight class="ml-2 h-4 w-4" />
          </Button>
          <Button
            v-else-if="!isReadOnly"
            variant="default"
            @click="submitQuestionnaire"
            :disabled="!isSectionValid || qStore.submitting"
            class="rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
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
            <Button variant="outline" class="rounded-2xl" @click="router.push('/home')">
              Kembali
            </Button>
          </div>
        </CardFooter>
      </Card>

      <!-- Success Dialog -->
      <Dialog :open="showSuccess" @update:open="handleSuccessClose">
        <DialogContent class="rounded-[2rem] text-center sm:max-w-md">
          <DialogHeader>
            <div class="mx-auto mb-4 w-fit rounded-2xl bg-emerald-100 p-3 dark:bg-emerald-400/10">
              <CheckCircle class="h-8 w-8 text-emerald-600 dark:text-emerald-300" />
            </div>
            <DialogTitle>Terima Kasih!</DialogTitle>
            <DialogDescription>
              Jawaban kuesioner Anda telah berhasil disimpan.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="sm:justify-center">
            <Button class="rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300" @click="handleSuccessClose">
              Kembali ke Dashboard
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
