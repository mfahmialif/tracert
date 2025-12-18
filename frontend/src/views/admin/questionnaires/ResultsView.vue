<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import {
  ArrowLeft,
  FileSpreadsheet,
  FileText,
  FileChartPie,
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
const loading = ref(true);
const title = ref("");
const results = ref<any[]>([]);

onMounted(async () => {
  try {
    const { data } = await api.get(
      `/admin/questionnaires/${route.params.id}/results`
    );
    title.value = data.title;
    results.value = data.results;
  } catch (error) {
    console.error("Failed to fetch results", error);
  } finally {
    loading.value = false;
  }
});

function getChartData(question: any) {
  const labels = Object.keys(question.stats || {});
  const data = Object.values(question.stats || {}) as number[];
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

function handleExport(type: "excel" | "pdf") {
  const url = `${api.defaults.baseURL}/admin/questionnaires/${route.params.id}/export/${type}`;
  window.open(url, "_blank");
}

function handleExportChartPdf() {
  const element = document.getElementById("results-content");
  if (!element) return;

  const opt = {
    margin: [10, 10, 10, 10] as [number, number, number, number],
    filename: `grafik_hasil_${title.value.replace(/\s+/g, "_")}.pdf`,
    image: { type: "jpeg" as const, quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" as const },
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

      <div class="flex gap-2">
        <Button variant="outline" @click="handleExport('excel')">
          <FileSpreadsheet class="mr-2 h-4 w-4" /> Export Excel
        </Button>
        <Button variant="outline" @click="handleExport('pdf')">
          <FileText class="mr-2 h-4 w-4" /> Export PDF Laporan
        </Button>
        <Button variant="outline" @click="handleExportChartPdf">
          <FileChartPie class="mr-2 h-4 w-4" /> Export PDF Grafik
        </Button>
      </div>

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
