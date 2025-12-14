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
import { useRouter } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";
import { toast } from "vue-sonner";

const router = useRouter();
const prodis = ref<any[]>([]);
const years = ref<any[]>([]);
const formError = ref<string | null>(null);
const loading = ref(false);

const form = reactive({
  nim: "",
  nama: "",
  prodi_id: "",
  tahun_id: "",
  email: "",
  no_hp: "",
  status: "",
});

onMounted(() => {
  fetchProdis();
  fetchYears();
});

async function fetchProdis() {
  try {
    const response = await api.get("/prodis", {
      params: { per_page: 100 },
    });
    prodis.value = response.data.data;
  } catch (error) {
    console.error("Failed to fetch prodis", error);
  }
}

async function fetchYears() {
  try {
    const response = await api.get("/admin/years", {
      params: { per_page: 100 },
    });
    years.value = response.data.data;
  } catch (error) {
    console.error("Failed to fetch years", error);
  }
}

async function handleSubmit() {
  formError.value = null;
  loading.value = true;
  try {
    await api.post("/admin/alumni", form);
    toast.success("Data alumni berhasil ditambahkan");
    router.push("/admin/alumni");
  } catch (error: any) {
    console.error("Failed to save alumni", error);
    formError.value = error.response?.data?.message || "Gagal menyimpan data";
    toast.error(error.response?.data?.message || "Gagal menyimpan data alumni");
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
          <h1 class="text-3xl font-bold tracking-tight">Tambah Alumni</h1>
          <p class="text-muted-foreground">Buat data alumni baru</p>
        </div>
      </div>

      <Card class="max-w-2xl mx-auto">
        <CardHeader>
          <CardTitle>Data Alumni</CardTitle>
          <CardDescription>
            Isi formulir berikut untuk menambahkan alumni baru.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <Alert v-if="formError" variant="destructive">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>{{ formError }}</AlertDescription>
            </Alert>

            <div class="grid gap-2">
              <Label for="nim">NIM</Label>
              <Input
                id="nim"
                v-model="form.nim"
                placeholder="Masukkan NIM"
                required
              />
            </div>

            <div class="grid gap-2">
              <Label for="nama">Nama</Label>
              <Input
                id="nama"
                v-model="form.nama"
                placeholder="Masukkan Nama Lengkap"
                required
              />
            </div>

            <div class="grid gap-2">
              <Label for="prodi">Prodi</Label>
              <Select v-model="form.prodi_id">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Prodi" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="prodi in prodis"
                    :key="prodi.id"
                    :value="prodi.id.toString()"
                  >
                    {{ prodi.nama || prodi.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="grid gap-2">
              <Label for="tahun_id">Tahun Lulus</Label>
              <Select v-model="form.tahun_id">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Tahun Lulus" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="year in years"
                    :key="year.id"
                    :value="year.id.toString()"
                  >
                    {{ year.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="grid gap-2">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="email@example.com"
              />
            </div>

            <div class="grid gap-2">
              <Label for="no_hp">No HP</Label>
              <Input id="no_hp" v-model="form.no_hp" placeholder="08..." />
            </div>

            <div class="grid gap-2">
              <Label for="status">Status</Label>
              <Select v-model="form.status">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Bekerja">Bekerja</SelectItem>
                  <SelectItem value="Mencari Kerja">Mencari Kerja</SelectItem>
                  <SelectItem value="Wirausaha">Wirausaha</SelectItem>
                  <SelectItem value="Studi Lanjut">Studi Lanjut</SelectItem>
                  <SelectItem value="Belum Bekerja">Belum Bekerja</SelectItem>
                </SelectContent>
              </Select>
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
