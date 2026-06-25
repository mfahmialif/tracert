<script setup lang="ts">
import { ref, onMounted, h, computed } from "vue";
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
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Search,
  MoreHorizontal,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  ArrowUpDown,
  Download as DownloadIcon,
  Upload,
  Plus,
  Users,
  Sparkles,
} from "lucide-vue-next";
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
  FlexRender,
} from "@tanstack/vue-table";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { toast } from "vue-sonner";
import { useSettingsStore } from '@/stores/settings';

const settingsStore = useSettingsStore();
const router = useRouter();
const alumni = ref<any[]>([]);
const loading = ref(false);
const showImportModal = ref(false);
const importFile = ref<File | null>(null);
const importing = ref(false);
const importResult = ref<any>(null);
const search = ref("");

const visiblePages = computed(() => {
  const { current_page, last_page } = pagination.value;
  const delta = 1;
  const range = [];
  const rangeWithDots: (number | string)[] = [];
  let l: number | undefined; // Explicitly type l as number | undefined

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
  // Ensure we don't have duplicates if total pages are small
  return [...new Set(rangeWithDots)];
});

// Pagination State
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

// Sorting State
const sorting = ref([{ id: "created_at", desc: true }]);

const columnHelper = createColumnHelper<any>();

const baseColumns = [
  columnHelper.display({
    id: "no",
    header: "No",
    cell: ({ row }) => {
      const { current_page, per_page } = pagination.value;
      return (current_page - 1) * per_page + row.index + 1;
    },
    enableSorting: false,
  }),
  columnHelper.accessor("nim", {
    header: () => {
      return h(
        Button,
        {
          variant: "ghost",
          onClick: () => handleSort("nim"),
        },
        () => ["NIM", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
      );
    },
    cell: (info) => info.getValue(),
  }),
  columnHelper.accessor("nama", {
    header: () => {
      return h(
        Button,
        {
          variant: "ghost",
          onClick: () => handleSort("nama"),
        },
        () => ["Nama", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
      );
    },
    cell: (info) => h("div", { class: "font-medium" }, info.getValue() || "-"),
  }),
  columnHelper.accessor("prodi.name", {
    header: "Prodi",
    cell: (info) => info.getValue() || "-",
  }),
  columnHelper.accessor("year.name", {
    header: () => {
      return h(
        Button,
        {
          variant: "ghost",
          onClick: () => handleSort("year.name"),
        },
        () => ["Tahun Lulus", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
      );
    },
    cell: (info) => info.getValue() || "-",
  }),
  columnHelper.accessor("no_hp", {
    header: "No. HP",
    cell: (info) => info.getValue() || "-",
  }),
];

// Dynamically add Status column if enabled
if (settingsStore.isAlumniStatusEnabled) {
  baseColumns.push(
    columnHelper.accessor("status", {
      header: "Status",
      cell: (info) => {
        const status = info.getValue();
        const variant =
          status === "Bekerja"
            ? "default" // Blue/Primary for working
            : status === "Wirausaha"
            ? "secondary" // Gray/Secondary for entrepreneur
            : status === "Studi Lanjut"
            ? "outline" // Outline for further studies
            : "destructive"; // Red for looking for job/unemployed

        return h(Badge, { variant, class: "rounded-lg" }, () => status || "Unknown");
      },
    })
  );
}

baseColumns.push(
  columnHelper.display({
    id: "actions",
    cell: ({ row }) => {
      const payment = row.original;
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
                        router.push(`/admin/alumni/${payment.alumni_id}/edit`),
                    },
                    () => "Edit"
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      class: "text-destructive",
                      onClick: () => handleDelete(payment.alumni_id),
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
  })
);

const table = useVueTable({
  get data() {
    return alumni.value;
  },
  columns: baseColumns,
  getCoreRowModel: getCoreRowModel(),
  manualPagination: true,
  manualSorting: true,
  pageCount: pagination.value.last_page,
});

const selectedProdi = ref("");
const selectedYear = ref("");
const selectedStatus = ref("");
const prodis = ref<any[]>([]);
const years = ref<any[]>([]);

onMounted(async () => {
  await settingsStore.fetchSettings();
  await Promise.all([fetchProdis(), fetchYears(), fetchAlumni()]);
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

async function fetchAlumni() {
  loading.value = true;
  try {
    const sortField = sorting.value[0]?.id || "created_at";
    const sortOrder = sorting.value[0]?.desc ? "desc" : "asc";

    const response = await api.get("/admin/alumni", {
      params: {
        page: pagination.value.current_page,
        per_page: pagination.value.per_page,
        search: search.value,
        sort_by: sortField,
        sort_order: sortOrder,
        prodi_id: selectedProdi.value === "all" ? "" : selectedProdi.value,
        year_id: selectedYear.value === "all" ? "" : selectedYear.value,
        status: selectedStatus.value === "all" ? "" : selectedStatus.value,
      },
    });
    alumni.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error("Failed to fetch alumni", error);
  } finally {
    loading.value = false;
  }
}

function handleSort(field: string) {
  const currentSort = sorting.value[0];
  if (currentSort?.id === field) {
    sorting.value = [{ id: field, desc: !currentSort.desc }];
  } else {
    sorting.value = [{ id: field, desc: true }];
  }
  fetchAlumni();
}

function handlePageChange(newPage: number) {
  if (newPage < 1 || newPage > pagination.value.last_page) return;
  pagination.value.current_page = newPage;
  fetchAlumni();
}

function handleLimitChange(value: any) {
  pagination.value.per_page = parseInt(value.toString());
  pagination.value.current_page = 1; // Reset to first page
  fetchAlumni();
}

function handleCreate() {
  router.push("/admin/alumni/create");
}

async function handleDelete(id: number) {
  if (!confirm("Apakah Anda yakin ingin menghapus alumni ini?")) return;

  try {
    await api.delete(`/admin/alumni/${id}`);
    toast.success("Data alumni berhasil dihapus");
    fetchAlumni();
  } catch (error) {
    console.error("Failed to delete alumni", error);
    toast.error("Gagal menghapus alumni");
  }
}

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    importFile.value = input.files[0];
  }
}

async function handleImport() {
  if (!importFile.value) return;

  importing.value = true;
  importResult.value = null;
  const formData = new FormData();
  formData.append("file", importFile.value);

  try {
    const response = await api.post("/admin/alumni/import", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    importResult.value = response.data;
    toast.success(`Berhasil import ${response.data.imported || 0} data alumni`);
    fetchAlumni();
    showImportModal.value = false;
  } catch (error: any) {
    importResult.value = {
      error: error.response?.data?.message || "Import failed",
    };
    toast.error(error.response?.data?.message || "Import gagal");
  } finally {
    importing.value = false;
  }
}

async function downloadTemplate() {
  try {
    const response = await api.get("/admin/alumni/template", {
      responseType: "blob",
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "template_import_alumni.xlsx");
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Failed to download template", error);
    toast.error("Gagal mengunduh template");
  }
}

const exporting = ref(false);

async function handleExport() {
  exporting.value = true;
  try {
    const params: any = {};

    // Apply current filters to export
    if (search.value) params.search = search.value;
    if (selectedProdi.value && selectedProdi.value !== "all")
      params.prodi_id = selectedProdi.value;
    if (selectedYear.value && selectedYear.value !== "all")
      params.year_id = selectedYear.value;
    if (selectedStatus.value && selectedStatus.value !== "all")
      params.status = selectedStatus.value;

    // Apply current sorting
    const sortField = sorting.value[0]?.id || "created_at";
    const sortOrder = sorting.value[0]?.desc ? "desc" : "asc";
    params.sort_by = sortField;
    params.sort_order = sortOrder;

    const response = await api.get("/admin/alumni/export", {
      params,
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    const filename = `alumni_${new Date().getTime()}.xlsx`;
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    toast.success("Data alumni berhasil diekspor");
  } catch (error) {
    console.error("Failed to export alumni", error);
    toast.error("Gagal mengekspor data alumni");
  } finally {
    exporting.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/[0.72] p-6 shadow-xl shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.055] md:p-8">
      <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="mb-3 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            <Users class="mr-2 h-3.5 w-3.5" />
            Alumni
          </p>
          <h1 class="text-3xl font-black tracking-tight md:text-4xl">Manajemen Alumni</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Kelola data alumni, program studi, tahun lulus, dan status alumni dalam tampilan admin yang konsisten.
          </p>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
          <Sparkles class="h-5 w-5" />
          {{ pagination.total }} data alumni
        </div>
      </div>
    </div>

    <div class="rounded-[1.75rem] border border-white/80 bg-white/[0.62] p-4 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.045]">
      <div class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_auto] 2xl:items-center">
      <div class="order-2 grid grid-cols-3 gap-2 sm:flex sm:items-center sm:justify-end 2xl:order-2">
        <Button @click="handleCreate" class="h-12 w-full rounded-2xl bg-emerald-600 px-5 font-bold shadow-lg shadow-emerald-700/20 hover:bg-emerald-700 sm:w-auto">
          <Plus class="h-4 w-4 sm:mr-2" />
          <span class="hidden sm:inline">Alumni</span>
        </Button>
        <Button
          variant="outline"
          @click="handleExport"
          :disabled="exporting"
          class="h-12 flex-1 rounded-2xl bg-white/70 sm:flex-none dark:bg-slate-950/40"
        >
          <span
            v-if="exporting"
            class="h-4 w-4 sm:mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"
          ></span>
          <DownloadIcon v-else class="h-4 w-4 sm:mr-2" />
          <span class="hidden sm:inline">{{
            exporting ? "Exporting..." : "Export"
          }}</span>
        </Button>
        <Dialog v-model:open="showImportModal">
          <DialogTrigger as-child>
            <Button variant="outline" class="h-12 flex-1 rounded-2xl bg-white/70 sm:flex-none dark:bg-slate-950/40">
              <Upload class="h-4 w-4 sm:mr-2" />
              <span class="hidden sm:inline">Import</span>
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Import Data Alumni</DialogTitle>
              <DialogDescription>
                Upload file Excel (.xlsx) atau CSV sesuai template.
              </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
              <Button
                variant="outline"
                @click="downloadTemplate"
                class="w-full"
              >
                <DownloadIcon class="mr-2 h-4 w-4" /> Download Template
              </Button>
              <div class="grid w-full max-w-sm items-center gap-1.5">
                <Input
                  id="file"
                  type="file"
                  accept=".xlsx,.xls,.csv"
                  @change="handleFileSelect"
                />
              </div>

              <Alert v-if="importResult?.error" variant="destructive">
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>{{ importResult.error }}</AlertDescription>
              </Alert>
              <Alert
                v-else-if="importResult"
                class="bg-green-50 text-green-800 border-green-200"
              >
                <AlertTitle>Sukses</AlertTitle>
                <AlertDescription>
                  {{ importResult.imported }} data baru,
                  {{ importResult.updated }} diupdate.
                </AlertDescription>
              </Alert>
            </div>
            <DialogFooter>
              <Button
                type="button"
                variant="secondary"
                @click="showImportModal = false"
                >Tutup</Button
              >
              <Button
                type="submit"
                @click="handleImport"
                :disabled="!importFile || importing"
              >
                <span
                  v-if="importing"
                  class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
                ></span>
                {{ importing ? "Importing..." : "Import" }}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      <div class="order-1 grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(280px,1fr)_190px_170px_170px_110px] 2xl:order-1">
        <div class="relative w-full sm:col-span-2 lg:col-span-1">
          <Search
            class="absolute left-4 top-3.5 h-4 w-4 text-slate-400"
          />
          <Input
            v-model="search"
            placeholder="Cari NIM atau nama..."
            class="h-12 w-full rounded-2xl bg-white/80 pl-11 dark:bg-slate-950/50"
            @input="fetchAlumni"
          />
        </div>

          <Select v-model="selectedProdi" @update:model-value="fetchAlumni">
            <SelectTrigger class="h-12 w-full rounded-2xl bg-white/70 dark:bg-slate-950/40">
              <SelectValue placeholder="Semua Prodi" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Prodi</SelectItem>
              <SelectItem
                v-for="prodi in prodis"
                :key="prodi.id"
                :value="prodi.id.toString()"
              >
                {{ prodi.name }}
              </SelectItem>
            </SelectContent>
          </Select>

          <Select v-model="selectedYear" @update:model-value="fetchAlumni">
            <SelectTrigger class="h-12 w-full rounded-2xl bg-white/70 dark:bg-slate-950/40">
              <SelectValue placeholder="Tahun Lulus" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Tahun</SelectItem>
              <SelectItem
                v-for="year in years"
                :key="year.id"
                :value="year.id.toString()"
              >
                {{ year.name }}
              </SelectItem>
            </SelectContent>
          </Select>

          <Select v-if="settingsStore.isAlumniStatusEnabled" v-model="selectedStatus" @update:model-value="fetchAlumni">
            <SelectTrigger class="h-12 w-full rounded-2xl bg-white/70 dark:bg-slate-950/40">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Status</SelectItem>
              <SelectItem value="Bekerja">Bekerja</SelectItem>
              <SelectItem value="Mencari Kerja">Mencari Kerja</SelectItem>
              <SelectItem value="Wirausaha">Wirausaha</SelectItem>
              <SelectItem value="Studi Lanjut">Studi Lanjut</SelectItem>
              <SelectItem value="Belum Bekerja">Belum Bekerja</SelectItem>
            </SelectContent>
          </Select>

        <Select
          :model-value="pagination.per_page.toString()"
          @update:model-value="handleLimitChange"
        >
          <SelectTrigger class="h-12 w-full rounded-2xl bg-white/70 dark:bg-slate-950/40">
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
    </div>

    <Card class="overflow-hidden">
      <CardContent class="p-0">
        <div class="overflow-x-auto">
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
                  <TableCell :colspan="baseColumns.length" class="h-24 text-center">
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
    <div class="flex items-center justify-end space-x-2 rounded-[1.5rem] border border-white/80 bg-white/[0.55] p-3 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.04]">
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
