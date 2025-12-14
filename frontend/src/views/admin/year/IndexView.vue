<script setup lang="ts">
import { ref, onMounted, computed, h } from "vue";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useRouter } from "vue-router";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Search,
  MoreHorizontal,
  Plus,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  CheckCircle2,
  XCircle,
} from "lucide-vue-next";
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
  FlexRender,
} from "@tanstack/vue-table";
import { Card, CardContent } from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const router = useRouter();
const years = ref<any[]>([]);
const loading = ref(false);
const search = ref("");

// Pagination State
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
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

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.display({
    id: "no",
    header: "No",
    cell: ({ row }) => {
      const { current_page, per_page } = pagination.value;
      return (current_page - 1) * per_page + row.index + 1;
    },
  }),
  columnHelper.accessor("code", {
    header: "Kode",
    cell: (info) => info.getValue(),
  }),
  columnHelper.accessor("name", {
    header: "Tahun Akademik",
    cell: (info) => info.getValue(),
  }),
  columnHelper.accessor("smt", {
    header: "Semester",
    cell: (info) => info.getValue(),
  }),
  columnHelper.accessor("is_active", {
    header: "Status",
    cell: (info) => {
      const active = info.getValue();
      return h(
        "div",
        {
          class: `flex items-center gap-2 ${active ? "text-green-600" : "text-gray-500"}`,
        },
        [
          h(active ? CheckCircle2 : XCircle, { class: "h-4 w-4" }),
          active ? "Aktif" : "Tidak Aktif",
        ]
      );
    },
  }),
  columnHelper.display({
    id: "actions",
    cell: ({ row }) => {
      const year = row.original;
      return h(
        DropdownMenu,
        {},
        {
          default: () => [
            h(
              DropdownMenuTrigger,
              { asChild: true },
              {
                default: () =>
                  h(
                    Button,
                    { variant: "ghost", class: "h-8 w-8 p-0" },
                    { default: () => h(MoreHorizontal, { class: "h-4 w-4" }) }
                  ),
              }
            ),
            h(
              DropdownMenuContent,
              { align: "end" },
              {
                default: () => [
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () =>
                        router.push(`/admin/years/${year.id}/edit`),
                    },
                    () => "Edit"
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      class: "text-destructive",
                      onClick: () => handleDelete(year.id),
                    },
                    () => "Hapus"
                  ),
                ],
              }
            ),
          ],
        }
      );
    },
  }),
];

const table = useVueTable({
  get data() {
    return years.value;
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  manualPagination: true,
  pageCount: pagination.value.last_page,
});

onMounted(() => {
  fetchYears();
});

async function fetchYears() {
  loading.value = true;
  try {
    const response = await api.get("/admin/years", {
      params: {
        page: pagination.value.current_page,
        per_page: pagination.value.per_page,
        search: search.value,
      },
    });
    years.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error("Failed to fetch years", error);
  } finally {
    loading.value = false;
  }
}

function handlePageChange(newPage: number) {
  if (newPage < 1 || newPage > pagination.value.last_page) return;
  pagination.value.current_page = newPage;
  fetchYears();
}

function handleLimitChange(value: any) {
  pagination.value.per_page = parseInt(value.toString());
  pagination.value.current_page = 1;
  fetchYears();
}

async function handleDelete(id: number) {
  if (!confirm("Apakah Anda yakin ingin menghapus tahun akademik ini?")) return;

  try {
    await api.delete(`/admin/years/${id}`);
    fetchYears();
  } catch (error) {
    console.error("Failed to delete year", error);
    alert("Gagal menghapus tahun akademik");
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">
          Manajemen Tahun Akademik
        </h1>
        <p class="text-muted-foreground">Kelola data tahun akademik</p>
      </div>
      <Button @click="router.push('/admin/years/create')">
        <Plus class="mr-2 h-4 w-4" /> Tambah Tahun
      </Button>
    </div>

    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-4"
    >
      <div class="relative w-full max-w-sm">
        <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="search"
          placeholder="Cari tahun akademik..."
          class="pl-8"
          @input="fetchYears"
        />
      </div>

      <div class="w-full sm:w-[100px]">
        <Select
          :model-value="pagination.per_page.toString()"
          @update:model-value="handleLimitChange"
        >
          <SelectTrigger>
            <SelectValue placeholder="Limit" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="10">10</SelectItem>
            <SelectItem value="25">25</SelectItem>
            <SelectItem value="50">50</SelectItem>
            <SelectItem value="100">100</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow
                v-for="headerGroup in table.getHeaderGroups()"
                :key="headerGroup.id"
              >
                <TableHead
                  v-for="header in headerGroup.headers"
                  :key="header.id"
                >
                  <FlexRender
                    v-if="!header.isPlaceholder"
                    :render="header.column.columnDef.header"
                    :props="header.getContext()"
                  />
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="table.getRowModel().rows?.length">
                <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                  <TableCell
                    v-for="cell in row.getVisibleCells()"
                    :key="cell.id"
                  >
                    <FlexRender
                      :render="cell.column.columnDef.cell"
                      :props="cell.getContext()"
                    />
                  </TableCell>
                </TableRow>
              </template>
              <template v-else>
                <TableRow>
                  <TableCell :colspan="columns.length" class="h-24 text-center">
                    <div v-if="loading" class="flex justify-center">
                      <div
                        class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
                      ></div>
                    </div>
                    <span v-else>Tidak ada data.</span>
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <!-- Pagination -->
    <div class="flex items-center justify-end space-x-2 py-4">
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

      <div class="flex items-center space-x-2">
        <template v-for="(page, index) in visiblePages" :key="index">
          <Button
            v-if="page === '...'"
            variant="ghost"
            size="icon"
            class="h-8 w-8 p-0"
            disabled
          >
            ...
          </Button>
          <Button
            v-else
            :variant="pagination.current_page === page ? 'default' : 'outline'"
            size="icon"
            class="h-8 w-8 p-0"
            @click="handlePageChange(Number(page))"
          >
            {{ page }}
          </Button>
        </template>
      </div>

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
  </AdminLayout>
</template>
