<script setup lang="ts">
import { onMounted, computed } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useTheme } from "../../composables/useTheme";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";
import { Users, FileText, LayoutDashboard, School, TrendingUp } from "lucide-vue-next";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const dashStore = useDashboardStore();
const { isDark } = useTheme();

onMounted(() => {
  dashStore.fetchDashboard();
});

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: isDark.value ? "#e5e7eb" : "#374151" } },
  },
  scales: {
    x: {
      ticks: {
        autoSkip: true,
        maxRotation: 0,
        color: isDark.value ? "#9ca3af" : "#4b5563",
      },
      grid: { color: isDark.value ? "#374151" : "#e5e7eb" },
    },
    y: {
      ticks: { color: isDark.value ? "#9ca3af" : "#4b5563" },
      grid: { color: isDark.value ? "#374151" : "#e5e7eb" },
    },
  },
}));

const prodiChartData = computed(() => ({
  labels: dashStore.data?.per_prodi?.map((p) => p.nama) || [],
  datasets: [
    {
      label: "Responses",
      data: dashStore.data?.per_prodi?.map((p) => p.total_responses) || [],
      backgroundColor: "#059669",
      borderRadius: 10,
    },
  ],
}));

const yearChartData = computed(() => ({
  labels: dashStore.data?.per_tahun?.map((p) => p.tahun) || [],
  datasets: [
    {
      label: "Responses",
      data: dashStore.data?.per_tahun?.map((p) => p.total_responses) || [],
      backgroundColor: "#14b8a6",
      borderRadius: 10,
    },
  ],
}));
</script>

<template>
  <AdminLayout>
    <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/[0.72] p-6 shadow-xl shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.055] md:p-8">
      <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="mb-3 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            Admin Console
          </p>
          <h1 class="text-3xl font-black tracking-tight md:text-4xl">Dashboard Overview</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Pantau alumni, kuesioner aktif, dan performa respons tracer study dalam satu ruang kerja.
          </p>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
          <TrendingUp class="h-5 w-5" />
          Data ringkas realtime
        </div>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card class="group overflow-hidden">
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Total Alumni</CardTitle>
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
            <Users class="h-5 w-5" />
          </span>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-black">
            <Skeleton v-if="dashStore.loading" class="h-8 w-20" />
            <span v-else>{{ dashStore.data?.summary?.total_alumni || 0 }}</span>
          </div>
          <CardDescription>+20.1% from last month</CardDescription>
        </CardContent>
      </Card>
      <Card class="group overflow-hidden">
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Responses</CardTitle>
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-teal-500/10 text-teal-700 dark:text-teal-300">
            <FileText class="h-5 w-5" />
          </span>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-black">
            <Skeleton v-if="dashStore.loading" class="h-8 w-20" />
            <span v-else>{{
              dashStore.data?.summary?.total_responses || 0
            }}</span>
          </div>
          <CardDescription>Total submissions</CardDescription>
        </CardContent>
      </Card>
      <Card class="group overflow-hidden">
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Active Forms</CardTitle>
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-cyan-500/10 text-cyan-700 dark:text-cyan-300">
            <LayoutDashboard class="h-5 w-5" />
          </span>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-black">
            <Skeleton v-if="dashStore.loading" class="h-8 w-20" />
            <span v-else>{{
              dashStore.data?.summary?.active_questionnaires || 0
            }}</span>
          </div>
          <CardDescription>Accepting responses</CardDescription>
        </CardContent>
      </Card>
      <Card class="group overflow-hidden">
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Response Rate</CardTitle>
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-lime-500/10 text-lime-700 dark:text-lime-300">
            <School class="h-5 w-5" />
          </span>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-black">
            <Skeleton v-if="dashStore.loading" class="h-8 w-20" />
            <span v-else
              >{{ dashStore.data?.summary?.response_rate || 0 }}%</span
            >
          </div>
          <CardDescription>Average completion</CardDescription>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-7">
      <Card class="lg:col-span-4">
        <CardHeader>
          <CardTitle>Respon per Program Studi</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-64 sm:h-72 lg:h-[300px]">
            <Bar :data="prodiChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>
      <Card class="lg:col-span-3">
        <CardHeader>
          <CardTitle>Respon per Tahun</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-64 sm:h-72 lg:h-[300px]">
            <Bar :data="yearChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
