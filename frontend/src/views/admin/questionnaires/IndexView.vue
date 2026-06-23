<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
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
  CardDescription,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { toast } from "vue-sonner";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import {
  Plus,
  Trash2,
  Edit,
  ArrowUpDown,
  Check,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Search,
  BarChart2,
  ClipboardList,
  Sparkles,
} from "lucide-vue-next";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const router = useRouter();

const questionnaires = ref<any[]>([]);
const loading = ref(false);
const deleteId = ref<number | null>(null);
const search = ref("");
let searchTimeout: ReturnType<typeof setTimeout>;

// Watch search with debounce
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    params.value.page = 1;
    fetchQuestionnaires();
  }, 500);
});

// Pagination & Sorting State
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 9,
  total: 0,
});

const params = ref({
  page: 1,
  sort_by: "created_at",
  sort_order: "desc",
});

const sortOptions = [
  { label: "Terbaru", value: "created_at", order: "desc" },
  { label: "Terlama", value: "created_at", order: "asc" },
  { label: "Judul (A-Z)", value: "title", order: "asc" },
  { label: "Judul (Z-A)", value: "title", order: "desc" },
  { label: "Status (Aktif)", value: "is_active", order: "desc" },
  { label: "Status (Non-aktif)", value: "is_active", order: "asc" },
];

const currentSortLabel = computed(() => {
  const match = sortOptions.find(
    (o) =>
      o.value === params.value.sort_by && o.order === params.value.sort_order
  );
  return match ? match.label : "Terbaru";
});

const visiblePages = computed(() => {
  const { current_page, last_page } = pagination.value;
  const delta = 1;
  const range = [];
  const rangeWithDots: (number | string)[] = [];
  let l: number | undefined;

  range.push(1);
  for (let i = current_page - delta; i <= current_page + delta; i++) {
    if (i < last_page && i > 1) {
      range.push(i);
    }
  }
  range.push(last_page);

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push("...");
      }
    }
    rangeWithDots.push(i);
    l = i;
  }
  return [...new Set(rangeWithDots)];
});

function handleLimitChange(val: any) {
  pagination.value.per_page = parseInt(val);
  params.value.page = 1;
  fetchQuestionnaires();
}

onMounted(() => {
  fetchQuestionnaires();
});

async function fetchQuestionnaires() {
  loading.value = true;
  try {
    const response = await api.get("/admin/questionnaires", {
      params: {
        ...params.value,
        search: search.value,
        per_page: pagination.value.per_page,
      },
    });
    questionnaires.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
    };
  } finally {
    loading.value = false;
  }
}

function handleSort(sortBy: string, sortOrder: string) {
  params.value.sort_by = sortBy;
  params.value.sort_order = sortOrder;
  params.value.page = 1; // Reset to page 1
  fetchQuestionnaires();
}

function handlePageChange(newPage: number) {
  if (newPage < 1 || newPage > pagination.value.last_page) return;
  params.value.page = newPage;
  fetchQuestionnaires();
}

async function handleDelete() {
  if (!deleteId.value) return;
  try {
    await api.delete(`/admin/questionnaires/${deleteId.value}`);
    toast.success("Kuesioner berhasil dihapus");
    fetchQuestionnaires();
    deleteId.value = null;
  } catch (e: any) {
    console.error("Failed to delete");
    toast.error(e.response?.data?.message || "Gagal menghapus kuesioner");
  }
}
</script>

<template>
  <AdminLayout>
    <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/[0.72] p-6 shadow-xl shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.055] md:p-8">
      <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="mb-3 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            <ClipboardList class="mr-2 h-3.5 w-3.5" />
            Kuesioner
          </p>
          <h1 class="text-3xl font-black tracking-tight md:text-4xl">Manajemen Kuesioner</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Buat, atur, dan pantau kuesioner tracer study dengan tampilan yang lebih rapi.
          </p>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
          <Sparkles class="h-5 w-5" />
          {{ pagination.total }} data kuesioner
        </div>
      </div>
    </div>

    <div class="rounded-[1.75rem] border border-white/80 bg-white/[0.62] p-4 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.045]">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <!-- Search -->
        <div class="relative w-full md:max-w-[360px]">
          <Search
            class="absolute left-4 top-3.5 h-4 w-4 text-slate-400"
          />
          <Input
            placeholder="Cari kuesioner..."
            v-model="search"
            class="h-12 rounded-2xl bg-white/80 pl-11 dark:bg-slate-950/50"
          />
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <!-- Sort Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="h-12 justify-between rounded-2xl bg-white/70 md:w-[170px] dark:bg-slate-950/40">
              <span class="flex items-center">
                <ArrowUpDown class="mr-2 h-4 w-4" />
                <span class="truncate">{{ currentSortLabel }}</span>
              </span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-[200px]">
            <DropdownMenuItem
              v-for="opt in sortOptions"
              :key="opt.label"
              @click="handleSort(opt.value, opt.order)"
            >
              <Check
                v-if="
                  params.sort_by === opt.value &&
                  params.sort_order === opt.order
                "
                class="mr-2 h-4 w-4"
              />
              <span v-else class="mr-6"></span>
              {{ opt.label }}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Limit Selector -->
        <Select
          :model-value="pagination.per_page.toString()"
          @update:model-value="handleLimitChange"
        >
          <SelectTrigger class="h-12 w-[86px] rounded-2xl bg-white/70 dark:bg-slate-950/40">
            <SelectValue :placeholder="pagination.per_page.toString()" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="pageSize in [9, 10, 20, 30, 40, 50]"
              :key="pageSize"
              :value="pageSize.toString()"
            >
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Create Button -->
        <Button
          class="h-12 rounded-2xl bg-emerald-600 px-5 font-bold shadow-lg shadow-emerald-700/20 hover:bg-emerald-700"
          @click="router.push('/admin/questionnaires/create')"
        >
          <Plus class="mr-2 h-4 w-4" />
          <span class="hidden md:inline">Buat Baru</span>
          <span class="md:hidden">Baru</span>
        </Button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
      ></div>
    </div>

    <div
      v-else-if="questionnaires.length === 0"
      class="text-center py-12 text-muted-foreground"
    >
      Belum ada kuesioner.
    </div>

    <TransitionGroup
      name="list"
      tag="div"
      v-else
      class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
    >
      <Card
        v-for="q in questionnaires"
        :key="q.id"
        class="group flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-2xl hover:shadow-emerald-900/10 dark:hover:border-emerald-400/20"
      >
        <CardHeader>
          <div class="flex justify-between items-start">
            <Badge variant="outline" class="rounded-full border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">{{ q.year?.name || q.year_id }}</Badge>
            <Badge
              :variant="q.is_active ? 'default' : 'secondary'"
              :class="q.is_active ? 'rounded-full bg-emerald-600 hover:bg-emerald-700' : 'rounded-full'"
            >
              {{ q.is_active ? "Aktif" : "Draft" }}
            </Badge>
          </div>
          <CardTitle class="mt-2 line-clamp-1 text-xl font-black">{{ q.title }}</CardTitle>
          <CardDescription class="line-clamp-2 h-10 leading-6">{{
            q.description
          }}</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 text-sm text-slate-600 dark:text-slate-300">
          <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-slate-950/30">
            <span class="font-bold text-slate-900 dark:text-white">Target Prodi</span>
            <div class="flex flex-wrap gap-1">
              <Badge
                v-for="prodi in q.prodis.slice(0, 3)"
                :key="prodi.id"
                variant="secondary"
                class="rounded-full text-xs"
              >
                {{ prodi.name }}
              </Badge>
              <span
                v-if="q.prodis.length > 3"
                class="text-xs text-muted-foreground"
              >
                +{{ q.prodis.length - 3 }} lainnya
              </span>
              <span
                v-if="q.prodis.length === 0"
                class="text-xs text-muted-foreground"
              >
                Semua / Belum diset
              </span>
            </div>
          </div>
          <div class="mt-4 grid gap-2">
          <div class="flex justify-between rounded-2xl bg-white/55 px-3 py-2 dark:bg-white/[0.04]">
            <span>Wajib:</span>
            <Badge
              :variant="q.is_mandatory ? 'destructive' : 'secondary'"
              class="rounded-full font-normal"
            >
              {{ q.is_mandatory ? "Wajib" : "Tidak Wajib" }}
            </Badge>
          </div>
          <div class="flex justify-between rounded-2xl bg-white/55 px-3 py-2 dark:bg-white/[0.04]">
            <span>Publik:</span>
            <Badge
              :variant="q.is_public ? 'destructive' : 'secondary'"
              class="rounded-full font-normal"
            >
              {{ q.is_public ? "Publik" : "Tidak Publik" }}
            </Badge>
          </div>
          <div class="flex justify-between rounded-2xl bg-white/55 px-3 py-2 dark:bg-white/[0.04]">
            <span>Pertanyaan:</span>
            <span class="font-medium">{{ q.questions_count || 0 }}</span>
          </div>
          </div>
        </CardContent>
        <CardFooter class="flex gap-2 border-t border-slate-200/70 pt-4 dark:border-white/10">
          <Button
            variant="secondary"
            class="flex-1 rounded-2xl"
            @click="router.push(`/admin/questionnaires/${q.id}/results`)"
          >
            <BarChart2 class="mr-2 h-4 w-4" /> Hasil
          </Button>
          <Button
            variant="outline"
            class="flex-1 rounded-2xl bg-white/70 dark:bg-slate-950/40"
            @click="router.push(`/admin/questionnaires/${q.id}/edit`)"
          >
            <Edit class="mr-2 h-4 w-4" /> Edit
          </Button>
          <Dialog>
            <DialogTrigger as-child>
              <Button
                variant="destructive"
                size="icon"
                class="rounded-2xl"
                @click="deleteId = q.id"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Hapus Kuesioner?</DialogTitle>
                <DialogDescription>
                  Tindakan ini tidak dapat dibatalkan. Kuesioner dan semua data
                  respon akan dihapus permanen.
                </DialogDescription>
              </DialogHeader>
              <DialogFooter>
                <Button variant="secondary" @click="deleteId = null"
                  >Batal</Button
                >
                <Button variant="destructive" @click="handleDelete"
                  >Ya, Hapus</Button
                >
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </CardFooter>
      </Card>
    </TransitionGroup>

    <!-- Pagination -->
    <!-- Pagination -->
    <div class="flex items-center justify-end space-x-2 py-4">
      <div class="flex-1 text-sm text-muted-foreground">
        {{ pagination.total }} data ditemukan.
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="icon"
          class="hidden h-8 w-8 p-0 lg:flex"
          @click="handlePageChange(1)"
          :disabled="pagination.current_page === 1"
        >
          <span class="sr-only">Go to first page</span>
          <ChevronsLeft class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="icon"
          class="h-8 w-8 p-0"
          @click="handlePageChange(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
        >
          <span class="sr-only">Go to previous page</span>
          <ChevronLeft class="h-4 w-4" />
        </Button>

        <Button
          v-for="(page, index) in visiblePages"
          :key="index"
          :variant="pagination.current_page === page ? 'default' : 'outline'"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="page === '...'"
          @click="page !== '...' && handlePageChange(Number(page))"
        >
          {{ page }}
        </Button>

        <Button
          variant="outline"
          size="icon"
          class="h-8 w-8 p-0"
          @click="handlePageChange(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
        >
          <span class="sr-only">Go to next page</span>
          <ChevronRight class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="icon"
          class="hidden h-8 w-8 p-0 lg:flex"
          @click="handlePageChange(pagination.last_page)"
          :disabled="pagination.current_page === pagination.last_page"
        >
          <span class="sr-only">Go to last page</span>
          <ChevronsRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.list-leave-active {
  position: absolute;
}
</style>
