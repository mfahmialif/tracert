<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Checkbox } from "@/components/ui/checkbox";
import { useTheme } from "@/composables/useTheme";
import PublicPageLoader from "./PublicPageLoader.vue";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "vue-sonner";
import { ArrowLeft, CheckCircle2, FileText, Moon, Send, Sun } from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const { isDark, toggleTheme } = useTheme();
const loading = ref(true);
const submitting = ref(false);
const submitted = ref(false);
const questionnaire = ref<any>(null);
const logoImage = "/logo_uiidalwa.png";
const respondentInfo = ref({
  name: "",
  email: "",
  phone: "",
});
const answers = ref<Record<number, any>>({});

onMounted(async () => {
  try {
    const { data } = await api.get(`/public/questionnaires/${route.params.id}`);
    questionnaire.value = data.data;
  } catch (error: any) {
    console.error("Failed to fetch questionnaire", error);
    toast.error(
      error.response?.data?.message || "Failed to load questionnaire"
    );
  } finally {
    loading.value = false;
  }
});

const allRequiredAnswered = computed(() => {
  if (!questionnaire.value) return false;

  const allQuestions = questionnaire.value.sections.flatMap(
    (s: any) => s.questions
  );
  const requiredQuestions = allQuestions.filter((q: any) => q.is_required);

  return requiredQuestions.every((q: any) => {
    const answer = answers.value[q.id];
    if (Array.isArray(answer)) {
      return answer.length > 0;
    }
    return answer != null && answer !== "";
  });
});

async function handleSubmit() {
  if (!respondentInfo.value.name || !respondentInfo.value.email) {
    toast.error("Please fill in your name and email");
    return;
  }

  if (!allRequiredAnswered.value) {
    toast.error("Please answer all required questions");
    return;
  }

  submitting.value = true;
  try {
    const payload = {
      respondent_name: respondentInfo.value.name,
      respondent_email: respondentInfo.value.email,
      respondent_phone: respondentInfo.value.phone,
      answers: Object.entries(answers.value).map(
        ([questionId, answerValue]) => ({
          question_id: parseInt(questionId),
          answer_value: answerValue,
        })
      ),
    };

    await api.post(`/public/questionnaires/${route.params.id}/submit`, payload);
    submitted.value = true;
    toast.success("Response submitted successfully!");
  } catch (error: any) {
    console.error("Failed to submit", error);
    toast.error(error.response?.data?.message || "Failed to submit response");
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div
    class="min-h-screen overflow-hidden bg-transparent text-slate-950 antialiased dark:text-white"
  >
    <PublicPageLoader v-if="loading" message="Sedang menyiapkan formulir kuesioner..." />

    <div class="pointer-events-none fixed inset-0 -z-10">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(20,184,166,0.16),transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.86),rgba(240,253,244,0.92))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.2),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(45,212,191,0.12),transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.96),rgba(6,30,24,0.94))]"
      />
      <div class="absolute -top-40 left-1/3 h-[28rem] w-[28rem] rounded-full bg-emerald-500/14 blur-3xl dark:bg-emerald-400/10" />
      <div class="absolute bottom-0 right-0 h-[24rem] w-[24rem] rounded-full bg-teal-500/12 blur-3xl dark:bg-teal-400/10" />
    </div>

    <header
      class="sticky top-0 z-50 border-b border-white/70 bg-white/82 shadow-lg shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/82"
    >
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div
              class="grid h-12 w-12 place-items-center rounded-2xl border border-emerald-200/70 bg-white p-2 shadow-lg shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95"
            >
              <img :src="logoImage" alt="UII Dalwa" class="h-8 w-auto object-contain" />
            </div>
            <div class="hidden sm:block">
              <h1 class="text-lg font-black tracking-tight text-slate-950 dark:text-white">
                Tracer Study
              </h1>
              <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                Kuesioner Publik UII Dalwa
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="ghost"
              size="icon"
              class="rounded-2xl"
              :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
              @click="toggleTheme"
            >
              <Sun v-if="isDark" class="h-5 w-5" />
              <Moon v-else class="h-5 w-5" />
            </Button>
            <Button
              variant="outline"
              class="rounded-2xl border-emerald-200/80 bg-white/70 text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 dark:border-emerald-400/20 dark:bg-white/5 dark:text-emerald-200 dark:hover:bg-emerald-400/10"
              @click="router.push('/public/questionnaires')"
            >
              <ArrowLeft class="mr-2 h-4 w-4" />
              Kembali
            </Button>
          </div>
        </div>
      </div>
    </header>

    <div
      v-if="!loading && submitted"
      class="container mx-auto flex min-h-[70vh] max-w-2xl items-center px-6 py-20 text-center"
    >
      <div
        class="w-full rounded-[2.5rem] border border-slate-200/80 bg-white/82 p-10 shadow-2xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
      >
        <div class="mx-auto mb-6 grid h-24 w-24 place-items-center rounded-[2rem] bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
          <CheckCircle2 class="h-14 w-14" />
        </div>
        <h2 class="mb-4 text-3xl font-black tracking-tight">Terima Kasih</h2>
        <p class="mb-8 text-lg leading-8 text-slate-600 dark:text-slate-300">
          Jawaban Anda berhasil dikirim. Data ini akan membantu kampus meningkatkan
          mutu tracer study dan layanan alumni.
        </p>
        <Button
          class="rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
          @click="router.push('/public/questionnaires')"
        >
          Kembali ke Kuesioner
        </Button>
      </div>
    </div>

    <main
      v-else-if="!loading && questionnaire"
      class="container mx-auto px-6 py-12 max-w-4xl"
    >
      <Card
        class="mb-8 overflow-hidden rounded-[2rem] border-slate-200/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
      >
        <CardHeader>
          <div
            class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"
          >
            <FileText class="h-4 w-4" />
            Form Kuesioner Publik
          </div>
          <CardTitle class="text-3xl font-black tracking-tight md:text-4xl">
            {{ questionnaire.title }}
          </CardTitle>
          <CardDescription class="pt-2 text-base leading-7 text-slate-600 dark:text-slate-300">
            {{ questionnaire.description }}
          </CardDescription>
          <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-200">
              Tahun {{ questionnaire.year }}
            </span>
            <span>•</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-white/10">
              Batas akhir: {{ questionnaire.end_date }}
            </span>
          </div>
        </CardHeader>
      </Card>

      <Card
        class="mb-8 rounded-[2rem] border-slate-200/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
      >
        <CardHeader>
          <CardTitle class="text-2xl font-black">Data Responden</CardTitle>
          <CardDescription class="text-slate-600 dark:text-slate-300">
            Mohon isi data kontak dengan benar.
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div>
            <Label for="name">Nama <span class="text-red-500">*</span></Label>
            <Input
              id="name"
              v-model="respondentInfo.name"
              class="mt-2 rounded-2xl"
              placeholder="Masukkan nama lengkap"
              required
            />
          </div>
          <div>
            <Label for="email">Email <span class="text-red-500">*</span></Label>
            <Input
              id="email"
              v-model="respondentInfo.email"
              type="email"
              class="mt-2 rounded-2xl"
              placeholder="nama@email.com"
              required
            />
          </div>
          <div>
            <Label for="phone">Nomor Telepon</Label>
            <Input
              id="phone"
              v-model="respondentInfo.phone"
              type="tel"
              class="mt-2 rounded-2xl"
              placeholder="+62 xxx xxxx xxxx"
            />
          </div>
        </CardContent>
      </Card>

      <div
        v-for="(section, sIdx) in questionnaire.sections"
        :key="sIdx"
        class="mb-8"
      >
        <h3 class="mb-4 text-lg font-black text-emerald-700 dark:text-emerald-300">
          Bagian {{ section.section }}
        </h3>

        <Card
          v-for="(question, qIdx) in section.questions"
          :key="question.id"
          class="mb-6 rounded-[2rem] border-slate-200/80 bg-white/82 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
        >
          <CardHeader>
            <CardTitle class="text-base font-semibold leading-7">
              {{ qIdx + 1 }}. {{ question.text }}
              <span v-if="question.is_required" class="text-red-500 ml-1"
                >*</span
              >
            </CardTitle>
          </CardHeader>
          <CardContent>
            <!-- Text Input -->
            <Input
              v-if="question.type === 'text'"
              v-model="answers[question.id]"
              class="rounded-2xl"
              placeholder="Jawaban Anda"
            />

            <!-- Long Text -->
            <Textarea
              v-else-if="question.type === 'long_text'"
              v-model="answers[question.id]"
              class="rounded-2xl"
              placeholder="Jawaban Anda"
              rows="4"
            />

            <!-- Radio -->
            <RadioGroup
              v-else-if="question.type === 'radio'"
              v-model="answers[question.id]"
              class="space-y-3"
            >
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-3 dark:border-white/10 dark:bg-slate-950/40"
              >
                <RadioGroupItem
                  :value="option"
                  :id="`q${question.id}-${oIdx}`"
                />
                <Label :for="`q${question.id}-${oIdx}`">{{ option }}</Label>
              </div>
            </RadioGroup>

            <!-- Checkbox -->
            <div v-else-if="question.type === 'checkbox'" class="space-y-2">
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-3 dark:border-white/10 dark:bg-slate-950/40"
              >
                <Checkbox
                  :id="`q${question.id}-${oIdx}`"
                  :checked="(answers[question.id] || []).includes(option)"
                  @update:checked="
                    (checked) => {
                      if (!answers[question.id]) answers[question.id] = [];
                      if (checked) {
                        answers[question.id].push(option);
                      } else {
                        answers[question.id] = answers[question.id].filter(
                          (v: string) => v !== option
                        );
                      }
                    }
                  "
                />
                <Label :for="`q${question.id}-${oIdx}`">{{ option }}</Label>
              </div>
            </div>

            <!-- Select -->
            <Select
              v-else-if="question.type === 'select'"
              v-model="answers[question.id]"
            >
              <SelectTrigger class="rounded-2xl">
                <SelectValue placeholder="Pilih opsi" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="(option, oIdx) in question.options"
                  :key="oIdx"
                  :value="option"
                >
                  {{ option }}
                </SelectItem>
              </SelectContent>
            </Select>

            <!-- Scale -->
            <RadioGroup
              v-else-if="question.type === 'scale'"
              v-model="answers[question.id]"
              class="flex flex-wrap gap-4"
            >
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex flex-col items-center"
              >
                <RadioGroupItem
                  :value="option"
                  :id="`q${question.id}-${oIdx}`"
                />
                <Label :for="`q${question.id}-${oIdx}`" class="mt-1">{{
                  option
                }}</Label>
              </div>
            </RadioGroup>

            <!-- Date -->
            <Input
              v-else-if="question.type === 'date'"
              v-model="answers[question.id]"
              type="date"
              class="rounded-2xl"
            />
          </CardContent>
        </Card>
      </div>

      <Card
        class="rounded-[2rem] border-slate-200/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
      >
        <CardContent class="pt-6">
          <Button
            @click="handleSubmit"
            :disabled="submitting || !allRequiredAnswered"
            class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/20 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-60 dark:from-emerald-400 dark:to-teal-400 dark:text-emerald-950 dark:hover:from-emerald-300 dark:hover:to-teal-300"
            size="lg"
          >
            <Send class="mr-2 h-5 w-5" />
            {{ submitting ? "Mengirim..." : "Kirim Jawaban" }}
          </Button>
          <p
            v-if="!allRequiredAnswered"
            class="text-sm text-red-500 text-center mt-2"
          >
            Mohon jawab semua pertanyaan wajib (*)
          </p>
        </CardContent>
      </Card>
    </main>
  </div>
</template>
