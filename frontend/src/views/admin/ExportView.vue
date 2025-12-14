<script setup lang="ts">
import { ref, onMounted } from "vue";
import api from "../../services/api";
import AdminLayout from "../../layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from "@/components/ui/select";
import { Label } from "@/components/ui/label";
import { FileSpreadsheet, File } from "lucide-vue-next";

const filters = ref({
  questionnaire_id: "",
  prodi_id: "",
  tahun_lulus: "",
});

const questionnaires = ref<any[]>([]);
const prodis = ref<any[]>([]);
const loading = ref(false);

onMounted(async () => {
  const [qRes, pRes] = await Promise.all([
    api.get("/admin/questionnaires"),
    api.get("/admin/prodi"), // Assuming endpoint exists or hardcode for now
  ]);
  questionnaires.value = qRes.data.data;
  prodis.value = pRes.data.data || [];
});

async function handleExport(type: "excel" | "pdf") {
  loading.value = true;
  try {
    const response = await api.get(`/admin/export/${type}`, {
      params: filters.value,
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute(
      "download",
      `tracer-study-export-${new Date().toISOString().split("T")[0]}.${type === "excel" ? "xlsx" : "pdf"}`
    );
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (e) {
    console.error("Export failed", e);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Export Data</h1>
      <p class="text-muted-foreground">Download laporan hasil survey</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 mt-6">
      <Card>
        <CardHeader>
          <CardTitle>Filter Data</CardTitle>
          <CardDescription>Pilih data yang ingin diexport</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <Label>Pilih Kuesioner</Label>
            <Select v-model="filters.questionnaire_id">
              <SelectTrigger
                ><SelectValue placeholder="Semua Kuesioner"
              /></SelectTrigger>
              <SelectContent>
                <SelectItem value="">Semua Kuesioner</SelectItem>
                <SelectItem
                  v-for="q in questionnaires"
                  :key="q.id"
                  :value="String(q.id)"
                  >{{ q.title }}</SelectItem
                >
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label>Program Studi</Label>
            <Select v-model="filters.prodi_id">
              <SelectTrigger
                ><SelectValue placeholder="Semua Prodi"
              /></SelectTrigger>
              <SelectContent>
                <SelectItem value="">Semua Prodi</SelectItem>
                <!-- Mock data if prodis empty, real app would have data -->
                <SelectItem value="1">Teknik Informatika</SelectItem>
                <SelectItem value="2">Sistem Informasi</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label>Tahun Lulus</Label>
            <Select v-model="filters.tahun_lulus">
              <SelectTrigger
                ><SelectValue placeholder="Semua Tahun"
              /></SelectTrigger>
              <SelectContent>
                <SelectItem value="">Semua Tahun</SelectItem>
                <SelectItem value="2024">2024</SelectItem>
                <SelectItem value="2023">2023</SelectItem>
                <SelectItem value="2022">2022</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Download report</CardTitle>
          <CardDescription>Pilih format file</CardDescription>
        </CardHeader>
        <CardContent class="grid gap-4">
          <Button
            class="h-24 text-lg justify-start px-6"
            variant="outline"
            @click="handleExport('excel')"
            :disabled="loading"
          >
            <div class="bg-green-100 p-3 rounded-full mr-4">
              <FileSpreadsheet class="h-8 w-8 text-green-600" />
            </div>
            <div class="text-left">
              <div class="font-semibold">Export to Excel</div>
              <div class="text-sm text-muted-foreground font-normal">
                Laporan detail untuk analisis data
              </div>
            </div>
          </Button>

          <Button
            class="h-24 text-lg justify-start px-6"
            variant="outline"
            @click="handleExport('pdf')"
            :disabled="loading"
          >
            <div class="bg-red-100 p-3 rounded-full mr-4">
              <File class="h-8 w-8 text-red-600" />
            </div>
            <div class="text-left">
              <div class="font-semibold">Export to PDF</div>
              <div class="text-sm text-muted-foreground font-normal">
                Laporan ringkas siap cetak
              </div>
            </div>
          </Button>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
