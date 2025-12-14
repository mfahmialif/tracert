<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
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
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Plus,
  Trash2,
  Edit,
  ArrowUpDown,
  Check,
  ChevronLeft,
  ChevronRight,
} from "lucide-vue-next";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const router = useRouter();

const questionnaires = ref<any[]>([]);
const loading = ref(false);
const deleteId = ref<number | null>(null);

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

onMounted(() => {
  fetchQuestionnaires();
});

async function fetchQuestionnaires() {
  loading.value = true;
  try {
    const response = await api.get("/admin/questionnaires", {
      params: params.value,
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
    fetchQuestionnaires();
    deleteId.value = null;
  } catch (e) {
    console.error("Failed to delete");
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Manajemen Kuesioner</h1>
        <p class="text-muted-foreground">
          Buat dan kelola kuesioner tracer study
        </p>
      </div>
      <div class="flex gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline">
              <ArrowUpDown class="mr-2 h-4 w-4" />
              {{ currentSortLabel }}
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
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

        <Button @click="router.push('/admin/questionnaires/create')">
          <Plus class="mr-2 h-4 w-4" /> Buat Baru
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
            <Badge variant="outline">{{ q.year?.name || q.tahun_id }}</Badge>
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
            <span class="font-medium">{{
              q.is_mandatory ? "Ya" : "Tidak"
            }}</span>
          </div>
          <div class="flex justify-between py-1">
            <span>Pertanyaan:</span>
            <span class="font-medium">{{ q.questions_count || 0 }}</span>
          </div>
        </CardContent>
        <CardFooter class="flex gap-2">
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
    <div
      v-if="pagination.last_page > 1"
      class="flex items-center justify-end space-x-2 py-4"
    >
      <Button
        variant="outline"
        size="sm"
        @click="handlePageChange(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
      >
        <ChevronLeft class="h-4 w-4" />
        Previous
      </Button>
      <div class="text-sm font-medium">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </div>
      <Button
        variant="outline"
        size="sm"
        @click="handlePageChange(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
      >
        Next
        <ChevronRight class="h-4 w-4" />
      </Button>
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
