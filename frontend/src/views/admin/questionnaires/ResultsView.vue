<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useAuthStore } from "@/stores/auth";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Alert, AlertDescription } from "@/components/ui/alert";
import {
  Dialog,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogScrollContent,
  DialogTitle,
} from "@/components/ui/dialog";
import { toast } from "vue-sonner";
import {
  ArrowLeft,
  FileSpreadsheet,
  FileText,
  FileChartPie,
  RefreshCw,
  WandSparkles,
} from "lucide-vue-next";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
} from "chart.js";
import { Bar, Pie } from "vue-chartjs";
// @ts-ignore
import html2pdf from "html2pdf.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const title = ref("");
const results = ref<any[]>([]);
const questionnaireIsPublic = ref(true);
const totalAlumniCount = ref<number | null>(null);
const availableAlumniCount = ref<number | null>(null);
const exporting = ref<"excel" | "pdf" | null>(null);
const generatorOpen = ref(false);
const generating = ref(false);
const generatorApiError = ref("");

type OptionCount = { option: string; count: number };
type Distribution = { question_id: number; option_counts: OptionCount[] };

const today = new Date();
const thirtyDaysAgo = new Date();
thirtyDaysAgo.setDate(today.getDate() - 30);

function formatInputDate(date: Date) {
  const offset = date.getTimezoneOffset();
  return new Date(date.getTime() - offset * 60_000).toISOString().slice(0, 10);
}

const generatorForm = ref({
  respondent_count: 50,
  replace_generated: true,
  submitted_from: formatInputDate(thirtyDaysAgo),
  submitted_until: formatInputDate(today),
  distributions: [] as Distribution[],
});

const choiceQuestions = computed(() =>
  results.value.filter((question) =>
    ["radio", "checkbox", "select", "scale"].includes(question.type)
  )
);

const automaticQuestions = computed(() =>
  results.value.filter(
    (question) => !["radio", "checkbox", "select", "scale"].includes(question.type)
  )
);

async function fetchResults() {
  loading.value = true;
  try {
    const { data } = await api.get(
      `/admin/questionnaires/${route.params.id}/results`
    );
    title.value = data.title;
    results.value = data.results;
    questionnaireIsPublic.value = data.is_public ?? true;
    totalAlumniCount.value = data.total_alumni_count ?? null;
    availableAlumniCount.value = data.available_alumni_count ?? null;
  } catch (error) {
    console.error("Failed to fetch results", error);
  } finally {
    loading.value = false;
  }
}

onMounted(fetchResults);

function balancedOptionCounts(options: string[], total: number): OptionCount[] {
  if (!options.length) return [];

  const base = Math.floor(total / options.length);
  const remainder = total % options.length;

  return options.map((option, index) => ({
    option,
    count: base + (index < remainder ? 1 : 0),
  }));
}

function normalizeOptions(options: unknown): string[] {
  if (Array.isArray(options)) {
    return options.map(String);
  }

  if (options && typeof options === "object") {
    return Object.values(options).map(String);
  }

  if (typeof options === "string") {
    try {
      return normalizeOptions(JSON.parse(options));
    } catch {
      return [];
    }
  }

  return [];
}

function redistributeAll() {
  const total = Math.max(1, Number(generatorForm.value.respondent_count) || 1);
  generatorForm.value.respondent_count = total;
  generatorForm.value.distributions = choiceQuestions.value.map((question) => ({
    question_id: question.id,
    option_counts: balancedOptionCounts(normalizeOptions(question.options), total),
  }));
}

function openGenerator() {
  generatorApiError.value = "";
  if (!questionnaireIsPublic.value) {
    const max = generatorForm.value.replace_generated
      ? (totalAlumniCount.value ?? 1)
      : (availableAlumniCount.value ?? 1);
    generatorForm.value.respondent_count = Math.max(
      1,
      Math.min(generatorForm.value.respondent_count, max)
    );
  }
  redistributeAll();
  generatorOpen.value = true;
}

const alumniMax = computed(() => {
  if (questionnaireIsPublic.value) return 1000;
  return generatorForm.value.replace_generated
    ? (totalAlumniCount.value ?? 1)
    : (availableAlumniCount.value ?? 1);
});

function distributionFor(questionId: number) {
  return generatorForm.value.distributions.find(
    (distribution) => distribution.question_id === questionId
  );
}

function distributionTotal(questionId: number) {
  return (
    distributionFor(questionId)?.option_counts.reduce(
      (total, item) => total + (Number(item.count) || 0),
      0
    ) || 0
  );
}

const generatorErrors = computed(() => {
  const errors: string[] = [];
  const total = Number(generatorForm.value.respondent_count);

  if (!Number.isInteger(total) || total < 1 || total > 1000) {
    errors.push("Jumlah responden harus antara 1 dan 1.000.");
  }

  if (
    !questionnaireIsPublic.value &&
    total > alumniMax.value
  ) {
    errors.push(`Hanya ${alumniMax.value} alumni target yang tersedia.`);
  }

  if (generatorForm.value.submitted_from > generatorForm.value.submitted_until) {
    errors.push("Tanggal awal tidak boleh melewati tanggal akhir.");
  }

  for (const question of choiceQuestions.value) {
    const distribution = distributionFor(question.id);
    const counts = distribution?.option_counts.map((item) => Number(item.count) || 0) || [];

    if (counts.some((count) => count < 0 || count > total)) {
      errors.push(`Jumlah pilihan pada “${question.text}” tidak valid.`);
      continue;
    }

    const sum = counts.reduce((value, count) => value + count, 0);
    if (question.type !== "checkbox" && sum !== total) {
      errors.push(`Total pilihan pada “${question.text}” harus ${total}.`);
    }
    if (question.type === "checkbox" && question.is_required && sum < total) {
      errors.push(`Pertanyaan wajib “${question.text}” membutuhkan minimal ${total} pilihan.`);
    }
  }

  return errors;
});

async function handleGenerate() {
  if (generatorErrors.value.length) return;

  generating.value = true;
  generatorApiError.value = "";

  try {
    const { data } = await api.post(
      `/admin/questionnaires/${route.params.id}/generate-responses`,
      generatorForm.value
    );
    toast.success(data.message);
    generatorOpen.value = false;
    await fetchResults();
  } catch (error: any) {
    const validationErrors = error.response?.data?.errors;
    generatorApiError.value = validationErrors
      ? Object.values(validationErrors).flat().join(" ")
      : error.response?.data?.message || "Gagal membuat responden simulasi.";
  } finally {
    generating.value = false;
  }
}

function getChartData(question: any) {
  const labels = Object.keys(question.stats || {});
  const data = Object.values(question.stats || {});
  // Generate random colors
  const backgroundColors = labels.map(
    () => `#${Math.floor(Math.random() * 16777215).toString(16)}`
  );

  return {
    labels,
    datasets: [
      {
        label: "Jumlah Respon",
        data,
        backgroundColor: backgroundColors,
      },
    ],
  };
}

function fallbackExportFilename(type: "excel" | "pdf") {
  const safeTitle = title.value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "");
  const date = new Date().toISOString().slice(0, 10);
  return `hasil_kuesioner_${safeTitle || route.params.id}_${date}.${
    type === "excel" ? "xlsx" : "pdf"
  }`;
}

function responseFilename(disposition: string | undefined, fallback: string) {
  if (!disposition) return fallback;

  const encoded = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
  if (encoded) {
    try {
      return decodeURIComponent(encoded.replace(/["']/g, ""));
    } catch {
      return encoded.replace(/["']/g, "");
    }
  }

  return disposition.match(/filename="?([^";]+)"?/i)?.[1] || fallback;
}

async function handleExport(type: "excel" | "pdf") {
  exporting.value = type;

  try {
    const response = await api.get(
      `/admin/questionnaires/${route.params.id}/export/${type}`,
      { responseType: "blob" }
    );
    const filename = responseFilename(
      response.headers["content-disposition"],
      fallbackExportFilename(type)
    );
    const downloadUrl = URL.createObjectURL(response.data);
    const link = document.createElement("a");
    link.href = downloadUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
    toast.success(`${filename} berhasil diunduh.`);
  } catch (error: any) {
    let message = "Gagal mengunduh laporan.";
    if (error.response?.data instanceof Blob) {
      try {
        const payload = JSON.parse(await error.response.data.text());
        message = payload.message || message;
      } catch {
        // Response bukan JSON; gunakan pesan umum.
      }
    }
    toast.error(message);
  } finally {
    exporting.value = null;
  }
}

function handleExportChartPdf() {
  const element = document.getElementById("results-content");
  const opt = {
    margin: [10, 10, 10, 10],
    filename: `grafik_hasil_${title.value.replace(/\s+/g, "_")}.pdf`,
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
  };
  html2pdf().set(opt).from(element).save();
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
};
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center gap-4">
        <Button
          variant="outline"
          size="icon"
          @click="router.push('/admin/questionnaires')"
        >
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div class="flex-1">
          <h1 class="text-2xl font-bold tracking-tight">Hasil Kuesioner</h1>
          <button
            @click="
              router.push(
                `/admin/questionnaires/${route.params.id}/respondents`
              )
            "
            class="text-muted-foreground hover:text-primary transition-colors cursor-pointer underline-offset-4 hover:underline"
          >
            {{ title }}
            <span class="text-xs">(klik untuk lihat responden)</span>
          </button>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <Button
          variant="outline"
          :disabled="exporting !== null"
          @click="handleExport('excel')"
        >
          <RefreshCw v-if="exporting === 'excel'" class="mr-2 h-4 w-4 animate-spin" />
          <FileSpreadsheet v-else class="mr-2 h-4 w-4" />
          {{ exporting === "excel" ? "Mengunduh..." : "Export Excel" }}
        </Button>
        <Button
          variant="outline"
          :disabled="exporting !== null"
          @click="handleExport('pdf')"
        >
          <RefreshCw v-if="exporting === 'pdf'" class="mr-2 h-4 w-4 animate-spin" />
          <FileText v-else class="mr-2 h-4 w-4" />
          {{ exporting === "pdf" ? "Mengunduh..." : "Export PDF Laporan" }}
        </Button>
        <Button variant="outline" @click="handleExportChartPdf">
          <FileChartPie class="mr-2 h-4 w-4" /> Export PDF Grafik
        </Button>
        <Button
          v-if="authStore.isSuperAdmin"
          class="bg-emerald-600 text-white hover:bg-emerald-700"
          @click="openGenerator"
        >
          <WandSparkles class="mr-2 h-4 w-4" /> Generate Responden
        </Button>
      </div>

      <Dialog v-model:open="generatorOpen">
        <DialogScrollContent class="max-w-5xl">
          <DialogHeader>
            <DialogTitle>Generate Responden Simulasi</DialogTitle>
            <DialogDescription>
              <template v-if="questionnaireIsPublic">
                Buat identitas responden simulasi Indonesia dan tentukan jumlah setiap jawaban.
              </template>
              <template v-else>
                Gunakan data alumni target dan tentukan jumlah setiap jawaban.
              </template>
              Semua nilai menggunakan jumlah data, bukan persentase.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-6">
            <div class="grid gap-4 rounded-2xl border bg-muted/30 p-4 md:grid-cols-3">
              <div class="grid gap-2">
                <Label for="respondent-count">Jumlah responden</Label>
                <Input
                  id="respondent-count"
                  v-model.number="generatorForm.respondent_count"
                  type="number"
                  min="1"
                  :max="alumniMax"
                />
                <p v-if="!questionnaireIsPublic" class="text-xs text-muted-foreground">
                  Tersedia {{ alumniMax }} alumni target.
                </p>
              </div>
              <div class="grid gap-2">
                <Label for="submitted-from">Tanggal jawaban mulai</Label>
                <Input id="submitted-from" v-model="generatorForm.submitted_from" type="date" />
              </div>
              <div class="grid gap-2">
                <Label for="submitted-until">Tanggal jawaban akhir</Label>
                <Input id="submitted-until" v-model="generatorForm.submitted_until" type="date" />
              </div>

              <div class="flex items-center justify-between gap-4 md:col-span-2">
                <div>
                  <p class="text-sm font-medium">Ganti hasil generate sebelumnya</p>
                  <p class="text-xs text-muted-foreground">
                    Response asli tetap aman dan tidak akan dihapus.
                  </p>
                </div>
                <Switch v-model="generatorForm.replace_generated" />
              </div>

              <div class="flex items-end md:justify-end">
                <Button variant="outline" class="w-full md:w-auto" @click="redistributeAll">
                  <RefreshCw class="mr-2 h-4 w-4" /> Bagi Merata
                </Button>
              </div>
            </div>

            <Alert v-if="generatorApiError" variant="destructive">
              <AlertDescription>{{ generatorApiError }}</AlertDescription>
            </Alert>

            <Alert v-if="generatorErrors.length" variant="destructive">
              <AlertDescription>
                <ul class="list-disc space-y-1 pl-5">
                  <li v-for="error in generatorErrors" :key="error">{{ error }}</li>
                </ul>
              </AlertDescription>
            </Alert>

            <div v-if="choiceQuestions.length" class="space-y-4">
              <div>
                <h3 class="font-semibold">Distribusi jawaban per pertanyaan</h3>
                <p class="text-sm text-muted-foreground">
                  Untuk checkbox, setiap pilihan dihitung terpisah sehingga total dapat melebihi jumlah responden.
                </p>
              </div>

              <Card v-for="(question, questionIndex) in choiceQuestions" :key="question.id">
                <CardHeader class="pb-3">
                  <CardTitle class="text-base leading-6">
                    {{ questionIndex + 1 }}. {{ question.text }}
                  </CardTitle>
                  <div class="flex flex-wrap gap-2 text-xs text-muted-foreground">
                    <span class="rounded-full bg-muted px-2 py-1 uppercase">{{ question.type }}</span>
                    <span class="rounded-full bg-muted px-2 py-1">
                      Total pilihan: {{ distributionTotal(question.id) }}
                    </span>
                  </div>
                </CardHeader>
                <CardContent class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  <div
                    v-for="item in distributionFor(question.id)?.option_counts"
                    :key="item.option"
                    class="grid gap-2 rounded-xl border p-3"
                  >
                    <Label :for="`q-${question.id}-${item.option}`" class="line-clamp-2">
                      {{ item.option }}
                    </Label>
                    <Input
                      :id="`q-${question.id}-${item.option}`"
                      v-model.number="item.count"
                      type="number"
                      min="0"
                      :max="generatorForm.respondent_count"
                    />
                  </div>
                </CardContent>
              </Card>
            </div>

            <div v-if="automaticQuestions.length" class="rounded-2xl border border-dashed p-4">
              <p class="font-medium">{{ automaticQuestions.length }} pertanyaan isian dibuat otomatis</p>
              <p class="mt-1 text-sm text-muted-foreground">
                Nama, email, telepon, kota, pekerjaan, tanggal, angka, dan jawaban teks akan memakai data simulasi Indonesia.
              </p>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" :disabled="generating" @click="generatorOpen = false">
              Batal
            </Button>
            <Button
              :disabled="generating || generatorErrors.length > 0"
              class="bg-emerald-600 text-white hover:bg-emerald-700"
              @click="handleGenerate"
            >
              <RefreshCw v-if="generating" class="mr-2 h-4 w-4 animate-spin" />
              <WandSparkles v-else class="mr-2 h-4 w-4" />
              {{ generating ? "Membuat data..." : `Generate ${generatorForm.respondent_count} Responden` }}
            </Button>
          </DialogFooter>
        </DialogScrollContent>
      </Dialog>

      <div v-if="loading" class="flex justify-center py-12">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
        ></div>
      </div>

      <div v-else class="grid gap-6" id="results-content">
        <Card v-for="q in results" :key="q.id">
          <CardHeader>
            <CardTitle
              class="text-lg font-medium flex items-start justify-between"
            >
              <span class="flex-1">
                {{ q.text }}
                <span class="ml-2 text-sm font-normal text-muted-foreground"
                  >({{ q.count }} respon)</span
                >
              </span>
              <Button
                variant="ghost"
                size="sm"
                class="ml-4 text-primary hover:text-primary/80"
                @click="
                  router.push(
                    `/admin/questionnaires/${route.params.id}/questions/${q.id}/respondents`
                  )
                "
              >
                Lihat Selengkapnya →
              </Button>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <!-- Charts for choice questions -->
            <div
              v-if="['radio', 'checkbox', 'select', 'scale'].includes(q.type)"
              class="h-[300px] w-full"
            >
              <Bar
                v-if="q.type === 'scale' || q.type === 'checkbox'"
                :data="getChartData(q)"
                :options="chartOptions"
              />
              <Pie v-else :data="getChartData(q)" :options="chartOptions" />
            </div>

            <!-- List for text questions -->
            <div v-else class="space-y-2">
              <div
                v-for="(ans, idx) in q.answers"
                :key="idx"
                class="p-3 bg-muted/50 rounded-md text-sm"
              >
                {{ ans || "-" }}
              </div>
              <p
                v-if="q.answers && q.answers.length === 0"
                class="text-sm text-muted-foreground italic"
              >
                Belum ada jawaban teks.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
