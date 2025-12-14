<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
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
import { useRouter, useRoute } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const formError = ref<string | null>(null);
const loading = ref(false);

const form = reactive({
  name: "",
  code: "",
});

onMounted(() => {
  fetchFaculty();
});

async function fetchFaculty() {
  try {
    const response = await api.get(`/admin/faculties/${route.params.id}`);
    const data = response.data;
    form.name = data.name;
    form.code = data.code;
  } catch (error) {
    console.error("Failed to fetch faculty", error);
    router.push("/admin/faculties");
  }
}

async function handleSubmit() {
  formError.value = null;
  loading.value = true;
  try {
    await api.put(`/admin/faculties/${route.params.id}`, form);
    router.push("/admin/faculties");
  } catch (error: any) {
    console.error("Failed to update faculty", error);
    formError.value = error.response?.data?.message || "Gagal memperbarui data";
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
          <h1 class="text-3xl font-bold tracking-tight">Edit Fakultas</h1>
          <p class="text-muted-foreground">Perbarui data fakultas</p>
        </div>
      </div>

      <Card class="max-w-2xl mx-auto">
        <CardHeader>
          <CardTitle>Data Fakultas</CardTitle>
          <CardDescription>
            Perbarui informasi fakultas di bawah ini.
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
                {{ loading ? "Menyimpan..." : "Simpan Perubahan" }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
