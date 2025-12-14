<script setup lang="ts">
import { onMounted, computed } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useTheme } from "../../composables/useTheme";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
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
import { Users, FileText, LayoutDashboard, School } from "lucide-vue-next";

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
      ticks: { color: isDark.value ? "#9ca3af" : "#4b5563" },
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
      backgroundColor: "#6366f1",
      borderRadius: 4,
    },
  ],
}));

const yearChartData = computed(() => ({
  labels: dashStore.data?.per_tahun?.map((p) => p.tahun) || [],
  datasets: [
    {
      label: "Responses",
      data: dashStore.data?.per_tahun?.map((p) => p.total_responses) || [],
      backgroundColor: "#8b5cf6",
      borderRadius: 4,
    },
  ],
}));
</script>

<template>
  <AdminLayout>
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Total Alumni</CardTitle>
          <Users class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashStore.data?.summary?.total_alumni || 0 }}
          </div>
          <p class="text-xs text-muted-foreground">+20.1% from last month</p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Responses</CardTitle>
          <FileText class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashStore.data?.summary?.total_responses || 0 }}
          </div>
          <p class="text-xs text-muted-foreground">Total submissions</p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Active Forms</CardTitle>
          <LayoutDashboard class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashStore.data?.summary?.active_questionnaires || 0 }}
          </div>
          <p class="text-xs text-muted-foreground">Accepting responses</p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
          <CardTitle class="text-sm font-medium">Response Rate</CardTitle>
          <School class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashStore.data?.summary?.response_rate || 0 }}%
          </div>
          <p class="text-xs text-muted-foreground">Average completion</p>
        </CardContent>
      </Card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
      <Card class="col-span-4">
        <CardHeader>
          <CardTitle>Respon per Program Studi</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-[300px]">
            <Bar :data="prodiChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>
      <Card class="col-span-3">
        <CardHeader>
          <CardTitle>Respon per Tahun</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-[300px]">
            <Bar :data="yearChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
