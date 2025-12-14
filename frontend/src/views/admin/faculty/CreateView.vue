<script setup lang="ts">
import { ref, reactive } from "vue";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from "@/components/ui/card";
import { useRouter } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";
import { toast } from "vue-sonner";

const router = useRouter();
const formError = ref<string | null>(null);
const loading = ref(false);

const form = reactive({
  name: "",
  code: "",
});

async function handleSubmit() {
  formError.value = null;
  loading.value = true;
  try {
    await api.post("/admin/faculties", form);
    toast.success("Data fakultas berhasil ditambahkan");
    router.push("/admin/faculties");
  } catch (error: any) {
    console.error("Failed to save faculty", error);
    formError.value = error.response?.data?.message || "Gagal menyimpan data";
    toast.error(
      error.response?.data?.message || "Gagal menyimpan data fakultas"
    );
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center space-x-2">
        <Button variant="ghost" size="icon" @click="router.back()">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Tambah Fakultas</h1>
          <p class="text-muted-foreground">Buat data fakultas baru</p>
        </div>
      </div>

      <Card class="max-w-2xl mx-auto">
        <CardHeader>
          <CardTitle>Data Fakultas</CardTitle>
          <CardDescription>
            Isi formulir berikut untuk menambahkan fakultas baru.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <Alert v-if="formError" variant="destructive">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>{{ formError }}</AlertDescription>
            </Alert>

            <div class="grid gap-2">
              <Label for="code">Kode Fakultas</Label>
              <Input
                id="code"
                v-model="form.code"
                placeholder="Contoh: FT"
                required
              />
            </div>

            <div class="grid gap-2">
              <Label for="name">Nama Fakultas</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Masukkan Nama Fakultas"
                required
              />
            </div>

            <div class="flex justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="router.back()">
                Batal
              </Button>
              <Button type="submit" :disabled="loading">
                {{ loading ? "Menyimpan..." : "Simpan" }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
