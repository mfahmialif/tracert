<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
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
import { toast } from "vue-sonner";

const router = useRouter();
const route = useRoute();
const formError = ref<string | null>(null);
const loading = ref(false);
const faculties = ref<any[]>([]);

const form = reactive({
  name: "",
  code: "",
  faculty_id: "",
  strata: "S1",
});

onMounted(() => {
  fetchFaculties();
  fetchProdi();
});

async function fetchFaculties() {
  try {
    const response = await api.get("/admin/faculties");
    faculties.value = response.data;
  } catch (error) {
    console.error("Failed to fetch faculties", error);
  }
}

async function fetchProdi() {
  try {
    const response = await api.get(`/admin/prodis/${route.params.id}`);
    const data = response.data;
    form.name = data.name;
    form.code = data.code;
    form.faculty_id = data.faculty_id.toString();
    form.strata = data.strata;
  } catch (error) {
    console.error("Failed to fetch prodi", error);
    router.push("/admin/prodis");
  }
}

async function handleSubmit() {
  formError.value = null;
  loading.value = true;
  try {
    await api.put(`/admin/prodis/${route.params.id}`, form);
    toast.success("Data prodi berhasil diperbarui");
    router.push("/admin/prodis");
  } catch (error: any) {
    console.error("Failed to update prodi", error);
    formError.value = error.response?.data?.message || "Gagal memperbarui data";
    toast.error(
      error.response?.data?.message || "Gagal memperbarui data prodi"
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
          <h1 class="text-3xl font-bold tracking-tight">Edit Prodi</h1>
          <p class="text-muted-foreground">Perbarui data prodi</p>
        </div>
      </div>

      <Card class="max-w-2xl mx-auto">
        <CardHeader>
          <CardTitle>Data Prodi</CardTitle>
          <CardDescription>
            Perbarui informasi prodi di bawah ini.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <Alert v-if="formError" variant="destructive">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>{{ formError }}</AlertDescription>
            </Alert>

            <div class="grid gap-2">
              <Label for="faculty">Fakultas</Label>
              <Select v-model="form.faculty_id">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Fakultas" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="faculty in faculties"
                    :key="faculty.id"
                    :value="faculty.id.toString()"
                  >
                    {{ faculty.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="grid gap-2">
              <Label for="code">Kode Prodi</Label>
              <Input
                id="code"
                v-model="form.code"
                placeholder="Contoh: TI"
                required
              />
            </div>

            <div class="grid gap-2">
              <Label for="name">Nama Prodi</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Masukkan Nama Prodi"
                required
              />
            </div>

            <div class="grid gap-2">
              <Label for="strata">Strata</Label>
              <Select v-model="form.strata">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Strata" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="S1">S1</SelectItem>
                  <SelectItem value="S2">S2</SelectItem>
                  <SelectItem value="S3">S3</SelectItem>
                </SelectContent>
              </Select>
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
