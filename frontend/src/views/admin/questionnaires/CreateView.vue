<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
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
import { Checkbox } from "@/components/ui/checkbox";
import { ChevronLeft } from "lucide-vue-next";

const router = useRouter();
const form = ref({
  title: "",
  description: "",
  type: "tracer_study",
  target_audience: "all",
  start_date: "",
  end_date: "",
  is_active: true,
  is_mandatory: false,
});
const loading = ref(false);

async function handleSubmit() {
  loading.value = true;
  try {
    await api.post("/admin/questionnaires", form.value);
    router.push("/admin/questionnaires");
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="p-4 sm:p-8 flex justify-center w-full">
      <Card class="w-full max-w-2xl">
        <CardHeader>
          <Button
            variant="ghost"
            class="w-fit -ml-4 mb-2"
            @click="router.back()"
          >
            <ChevronLeft class="mr-2 h-4 w-4" /> Kembali
          </Button>
          <CardTitle>Buat Kuesioner Baru</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="space-y-2">
            <Label for="title">Judul Kuesioner</Label>
            <Input
              id="title"
              v-model="form.title"
              placeholder="Contoh: Tracer Study 2024"
              required
            />
          </div>

          <div class="space-y-2">
            <Label for="desc">Deskripsi</Label>
            <Textarea
              id="desc"
              v-model="form.description"
              placeholder="Deskripsi singkat..."
              rows="3"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Tipe Kuesioner</Label>
              <Select v-model="form.type">
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="tracer_study">Tracer Study</SelectItem>
                  <SelectItem value="satisfaction_survey"
                    >Survei Kepuasan</SelectItem
                  >
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Target Audiens</Label>
              <Select v-model="form.target_audience">
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Alumni</SelectItem>
                  <SelectItem value="graduated_1_year"
                    >Lulusan 1 Tahun</SelectItem
                  >
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Tanggal Mulai</Label>
              <Input type="date" v-model="form.start_date" />
            </div>
            <div class="space-y-2">
              <Label>Tanggal Selesai</Label>
              <Input type="date" v-model="form.end_date" />
            </div>
          </div>

          <div class="flex flex-col gap-4">
            <div class="flex items-center space-x-2">
              <Checkbox
                id="active"
                :checked="form.is_active"
                @update:checked="(v: boolean) => (form.is_active = v)"
              />
              <Label for="active">Aktifkan Kuesioner</Label>
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox
                id="mandatory"
                :checked="form.is_mandatory"
                @update:checked="(v: boolean) => (form.is_mandatory = v)"
              />
              <Label for="mandatory">Wajib Diisi</Label>
            </div>
          </div>
        </CardContent>
        <CardFooter>
          <Button class="w-full" @click="handleSubmit" :disabled="loading">
            <span v-if="loading">Menyimpan...</span>
            <span v-else>Buat Kuesioner</span>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AdminLayout>
</template>
