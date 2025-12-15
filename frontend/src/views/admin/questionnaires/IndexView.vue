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
    <div
      class="flex flex-col gap-4 py-4 md:flex-row md:items-center md:justify-between"
    >
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Manajemen Kuesioner</h1>
        <p class="text-muted-foreground">
          Buat dan kelola kuesioner tracer study
        </p>
      </div>
      <div class="flex flex-col gap-2 md:flex-row md:items-center">
        <!-- Search -->
        <div class="relative w-full md:w-[200px] lg:w-[300px]">
          <Search
            class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground"
          />
          <Input
            placeholder="Cari kuesioner..."
            v-model="search"
            class="pl-8 h-9"
          />
        </div>

        <!-- Sort Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="h-9 justify-between md:w-[160px]">
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
          <SelectTrigger class="h-9 w-[70px] w-100">
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
          class="h-9"
          @click="router.push('/admin/questionnaires/create')"
        >
          <Plus class="mr-2 h-4 w-4" />
          <span class="hidden md:inline">Buat Baru</span>
          <span class="md:hidden">Baru</span>
        </Button>
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
        class="flex flex-col transition-all hover:shadow-lg"
      >
        <CardHeader>
          <div class="flex justify-between items-start">
            <Badge variant="outline">{{ q.year?.name || q.year_id }}</Badge>
            <Badge
              :variant="q.is_active ? 'default' : 'secondary'"
              :class="q.is_active ? 'bg-green-600 hover:bg-green-700' : ''"
            >
              {{ q.is_active ? "Aktif" : "Draft" }}
            </Badge>
          </div>
          <CardTitle class="mt-2 line-clamp-1">{{ q.title }}</CardTitle>
          <CardDescription class="line-clamp-2 h-10">{{
            q.description
          }}</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 text-sm text-muted-foreground">
          <div class="flex flex-col gap-1 py-1">
            <span>Target Prodi:</span>
            <div class="flex flex-wrap gap-1">
              <Badge
                v-for="prodi in q.prodis.slice(0, 3)"
                :key="prodi.id"
                variant="secondary"
                class="text-xs"
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
          <div class="flex justify-between py-1">
            <span>Wajib:</span>
            <Badge
              :variant="q.is_mandatory ? 'destructive' : 'secondary'"
              class="font-normal"
            >
              {{ q.is_mandatory ? "Wajib" : "Tidak Wajib" }}
            </Badge>
          </div>
          <div class="flex justify-between py-1">
            <span>Publik:</span>
            <Badge
              :variant="q.is_public ? 'destructive' : 'secondary'"
              class="font-normal"
            >
              {{ q.is_public ? "Publik" : "Tidak Publik" }}
            </Badge>
          </div>
          <div class="flex justify-between py-1">
            <span>Pertanyaan:</span>
            <span class="font-medium">{{ q.questions_count || 0 }}</span>
          </div>
        </CardContent>
        <CardFooter class="flex gap-2">
          <Button
            variant="secondary"
            class="flex-1"
            @click="router.push(`/admin/questionnaires/${q.id}/results`)"
          >
            <BarChart2 class="mr-2 h-4 w-4" /> Hasil
          </Button>
          <Button
            variant="outline"
            class="flex-1"
            @click="router.push(`/admin/questionnaires/${q.id}/edit`)"
          >
            <Edit class="mr-2 h-4 w-4" /> Edit
          </Button>
          <Dialog>
            <DialogTrigger as-child>
              <Button
                variant="destructive"
                size="icon"
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
