<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
  CardFooter,
} from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ArrowLeft, Save } from "lucide-vue-next";
import { toast } from "vue-sonner";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";

const router = useRouter();
const loading = ref(false);
const error = ref("");

const form = ref({
  code: "",
  name: "",
  smt: "",
  is_active: true,
});

async function handleSubmit() {
  loading.value = true;
  error.value = "";
  try {
    await api.post("/admin/years", form.value);
    toast.success("Data tahun akademik berhasil ditambahkan");
    router.push("/admin/years");
  } catch (err: any) {
    error.value =
      err.response?.data?.message || "Terjadi kesalahan saat menyimpan data";
    toast.error(
      err.response?.data?.message || "Gagal menyimpan data tahun akademik"
    );
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex items-center gap-4 mb-6">
      <Button variant="outline" size="icon" @click="router.back()">
        <ArrowLeft class="h-4 w-4" />
      </Button>
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Tambah Tahun Akademik</h1>
        <p class="text-muted-foreground">Buat data tahun akademik baru</p>
      </div>
    </div>

    <Card class="max-w-xl mx-auto">
      <CardHeader>
        <CardTitle>Form Tahun Akademik</CardTitle>
        <CardDescription
          >Isi detail tahun akademik di bawah ini.</CardDescription
        >
      </CardHeader>
      <form @submit.prevent="handleSubmit">
        <CardContent class="space-y-4">
          <Alert v-if="error" variant="destructive">
            <AlertDescription>{{ error }}</AlertDescription>
          </Alert>

          <div class="grid gap-2">
            <Label for="code">Kode</Label>
            <Input
              id="code"
              v-model="form.code"
              placeholder="Contoh: 20231"
              required
            />
          </div>

          <div class="grid gap-2">
            <Label for="name">Nama Tahun Akademik</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="Contoh: 2023/2024 Ganjil"
              required
            />
          </div>

          <div class="grid gap-2">
            <Label for="smt">Semester</Label>
            <Select v-model="form.smt">
              <SelectTrigger>
                <SelectValue placeholder="Pilih Semester" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Ganjil">Ganjil</SelectItem>
                <SelectItem value="Genap">Genap</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex items-center space-x-2">
            <Switch
              id="is_active"
              :checked="form.is_active"
              @update:checked="form.is_active = $event"
            />
            <Label for="is_active">Aktif</Label>
          </div>
        </CardContent>
        <CardFooter class="flex justify-end gap-2">
          <Button type="button" variant="outline" @click="router.back()"
            >Batal</Button
          >
          <Button type="submit" :disabled="loading">
            <Save class="mr-2 h-4 w-4" />
            {{ loading ? "Menyimpan..." : "Simpan" }}
          </Button>
        </CardFooter>
      </form>
    </Card>
  </AdminLayout>
</template>
