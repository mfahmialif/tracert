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
import { ChevronLeft, ClipboardList } from "lucide-vue-next";

const router = useRouter();
const form = ref({
  title: "",
  description: "",
  year_id: "",
  prodi_ids: [] as number[],
  start_date: "",
  end_date: "",
  is_active: true,
  is_mandatory: false,
  is_public: false,
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
    <div class="mx-auto grid w-full max-w-5xl gap-6 lg:grid-cols-[0.8fr_1.2fr]">
      <div class="rounded-[2rem] border border-white/80 bg-white/[0.72] p-6 shadow-xl shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.055] md:p-8">
        <Button
          variant="ghost"
          class="-ml-3 mb-6 rounded-2xl"
          @click="router.back()"
        >
          <ChevronLeft class="mr-2 h-4 w-4" /> Kembali
        </Button>
        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
          <ClipboardList class="h-7 w-7" />
        </div>
        <h1 class="mt-5 text-3xl font-black tracking-tight">Buat Kuesioner Baru</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
          Lengkapi informasi dasar, target program studi, dan status publikasi sebelum menambahkan pertanyaan.
        </p>
      </div>

      <Card class="w-full">
        <CardHeader class="border-b border-slate-200/70 dark:border-white/10">
          <Button
            variant="ghost"
            class="w-fit -ml-4 mb-2 rounded-2xl lg:hidden"
            @click="router.back()"
          >
            <ChevronLeft class="mr-2 h-4 w-4" /> Kembali
          </Button>
          <CardTitle class="text-2xl font-black">Detail Kuesioner</CardTitle>
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
              <Select v-model="form.year_id">
                <SelectTrigger
                  ><SelectValue placeholder="Pilih Tahun"
                /></SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="y in years"
                    :key="y.id"
                    :value="y.id.toString()"
                  >
                    {{ y.name }} {{ y.smt || '' }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label>Target Program Studi</Label>
              <div
                class="max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-slate-950/30"
              >
                <div
                  v-for="prodi in prodis"
                  :key="prodi.id"
                  class="flex items-center space-x-2"
                >
                  <input
                    type="checkbox"
                    :id="'prodi-' + prodi.id"
                    :checked="form.prodi_ids.includes(prodi.id)"
                    @change="
                      (e: Event) => {
                        if ((e.target as HTMLInputElement).checked)
                          form.prodi_ids.push(prodi.id);
                        else
                          form.prodi_ids = form.prodi_ids.filter(
                            (id) => id !== prodi.id
                          );
                      }
                    "
                    class="h-4 w-4"
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

          <div class="grid gap-3">
            <div class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-slate-950/30">
              <input
                type="checkbox"
                id="active"
                v-model="form.is_active"
                class="h-4 w-4"
              />
              <Label for="active">Aktifkan Kuesioner</Label>
            </div>
            <div class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-slate-950/30">
              <input
                type="checkbox"
                id="mandatory"
                v-model="form.is_mandatory"
                class="h-4 w-4"
              />
              <Label for="mandatory">Wajib Diisi</Label>
            </div>
            <div class="flex items-center space-x-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-slate-950/30">
              <input
                type="checkbox"
                id="public"
                v-model="form.is_public"
                class="h-4 w-4"
              />
              <Label for="public"
                >Publikasi Publik (Bisa diakses tanpa login)</Label
              >
            </div>
          </div>
        </CardContent>
        <CardFooter class="border-t border-slate-200/70 dark:border-white/10">
          <Button class="h-12 w-full rounded-2xl bg-emerald-600 font-bold shadow-lg shadow-emerald-700/20 hover:bg-emerald-700" @click="handleSubmit" :disabled="loading">
            <span v-if="loading">Menyimpan...</span>
            <span v-else>Buat Kuesioner</span>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AdminLayout>
</template>
