<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { toast } from "vue-sonner";
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
  tahun_id: "",
  prodi_ids: [] as number[],
  start_date: "",
  end_date: "",
  is_active: true,
  is_mandatory: false,
});
const loading = ref(false);
const years = ref<any[]>([]);
const prodis = ref<any[]>([]);

onMounted(() => {
  fetchOptions();
});

async function fetchOptions() {
  try {
    const [yearRes, prodiRes] = await Promise.all([
      api.get("/admin/years?per_page=100"),
      api.get("/prodis?per_page=100"), // Assuming public or admin usage
    ]);
    years.value = yearRes.data.data;
    prodis.value = prodiRes.data.data;
  } catch (e) {
    console.error("Failed to fetch options", e);
  }
}

async function handleSubmit() {
  loading.value = true;
  try {
    await api.post("/admin/questionnaires", {
      ...form.value,
      // Ensure prodi_ids are numbers
      prodi_ids: form.value.prodi_ids.map(Number),
    });
    toast.success("Kuesioner berhasil ditambahkan");
    router.push("/admin/questionnaires");
  } catch (e: any) {
    console.error(e);
    toast.error(e.response?.data?.message || "Gagal menyimpan kuesioner");
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

          <div class="space-y-4">
            <div class="space-y-2">
              <Label>Tahun Periode</Label>
              <Select v-model="form.tahun_id">
                <SelectTrigger
                  ><SelectValue placeholder="Pilih Tahun"
                /></SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="y in years"
                    :key="y.id"
                    :value="y.id.toString()"
                  >
                    {{ y.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label>Target Program Studi</Label>
              <div
                class="border rounded-md p-4 max-h-48 overflow-y-auto space-y-2"
              >
                <div
                  v-for="prodi in prodis"
                  :key="prodi.id"
                  class="flex items-center space-x-2"
                >
                  <Checkbox
                    :id="'prodi-' + prodi.id"
                    :checked="form.prodi_ids.includes(prodi.id)"
                    @update:checked="
                      (checked: boolean) => {
                        if (checked) form.prodi_ids.push(prodi.id);
                        else
                          form.prodi_ids = form.prodi_ids.filter(
                            (id) => id !== prodi.id
                          );
                      }
                    "
                  />
                  <Label :for="'prodi-' + prodi.id" class="cursor-pointer">
                    {{ prodi.name }} ({{ prodi.code }})
                  </Label>
                </div>
              </div>
              <p class="text-xs text-muted-foreground">
                Pilih program studi yang dituju.
              </p>
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
