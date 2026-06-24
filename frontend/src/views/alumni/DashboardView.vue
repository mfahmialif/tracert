<script setup lang="ts">
import { onMounted, computed, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { useQuestionnaireStore } from "../../stores/questionnaire";
import { useTheme } from "../../composables/useTheme";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuItem,
} from "@/components/ui/dropdown-menu";
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
  Sun,
  Moon,
  Menu,
  LogOut,
  FileText,
  CheckCircle,
  Clock,
  User,
} from "lucide-vue-next";

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { ArrowUpDown } from "lucide-vue-next"; // Import ArrowUpDown

const router = useRouter();
const authStore = useAuthStore();
const qStore = useQuestionnaireStore();
const { isDark, toggleTheme } = useTheme();
const logoImage = "/logo_uiidalwa.png";

import { Input } from "@/components/ui/input";
import { useDebounceFn } from "@vueuse/core"; // Try using vueuse if available or implement manual debounce

// Manual debounce if vueuse not available (safest bet without checking package.json)
function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
  let timeout: ReturnType<typeof setTimeout>;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
}

onMounted(() => {
  qStore.fetchCounts(); // Fetch counts independent of filters
  qStore.fetchQuestionnaires(
    1,
    false,
    sortBy.value,
    sortOrder.value,
    activeTab.value,
    searchQuery.value
  );
});

const sortBy = ref<"title" | "year" | "newest">("newest"); // Default to newest
const sortOrder = ref<"asc" | "desc">("desc"); // Default desc for newest
const activeTab = ref<"pending" | "completed">("pending");
const searchQuery = ref("");

function toggleSortOrder() {
  sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
}

// Watch sortBy, activeTab, and debounce search to refetch
const debouncedFetch = debounce(
  (newSortBy, newSortOrder, newActiveTab, newSearch) => {
    qStore.fetchQuestionnaires(
      1,
      false,
      newSortBy,
      newSortOrder,
      newActiveTab,
      newSearch
    );
  },
  500
);

watch(
  [sortBy, sortOrder, activeTab, searchQuery],
  ([newSortBy, newSortOrder, newActiveTab, newSearch], [oldSortBy, oldSortOrder, oldActiveTab, oldSearch]) => {
    // Instantly clear data to show spinner when switching tabs
    if (newActiveTab !== oldActiveTab) {
      qStore.questionnaires = [];
    }
    
    // If search changed, always debounce
    // For other changes, we might want immediate reaction, but keeping it simple with one debounced watcher is fine/smoother
    debouncedFetch(newSortBy, newSortOrder, newActiveTab, newSearch);
  }
);

// Use store data directly for display
const sortedQuestionnaires = computed(() => qStore.questionnaires);

const hasMore = computed(() => {
  return qStore.meta.current_page < qStore.meta.last_page;
});

async function loadMore() {
  if (!hasMore.value || qStore.loading) return;
  await qStore.fetchQuestionnaires(
    qStore.meta.current_page + 1,
    true,
    sortBy.value,
    sortOrder.value,
    activeTab.value,
    searchQuery.value // Pass search query
  );
}

// Initial fetch
onMounted(() => {
  qStore.fetchQuestionnaires(
    1,
    false,
    sortBy.value,
    sortOrder.value,
    activeTab.value
  );
});

async function handleLogout() {
  await authStore.logout();
  router.push("/login");
}

const userInitials = computed(
  () => authStore.user?.alumni?.nama?.charAt(0).toUpperCase() || "U"
);
const firstName = computed(
  () => authStore.user?.alumni?.nama?.split(" ")[0] || "User"
);
</script>

<template>
  <div class="min-h-screen overflow-hidden bg-transparent text-slate-950 dark:text-white">
    <div class="pointer-events-none fixed inset-0 -z-10">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(20,184,166,0.14),transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.88),rgba(240,253,244,0.9))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(45,212,191,0.1),transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.98),rgba(6,30,24,0.95))]"
      />
      <div class="absolute -top-32 left-1/4 h-[26rem] w-[26rem] rounded-full bg-emerald-500/12 blur-3xl dark:bg-emerald-400/10" />
      <div class="absolute bottom-0 right-0 h-[24rem] w-[24rem] rounded-full bg-teal-500/10 blur-3xl dark:bg-teal-400/10" />
    </div>

    <header
      class="sticky top-0 z-40 w-full border-b border-white/70 bg-white/82 shadow-lg shadow-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/82"
    >
      <div class="container flex h-20 items-center justify-between px-4">
        <Sheet>
          <SheetTrigger as-child>
            <Button variant="ghost" size="icon" class="rounded-2xl md:hidden">
              <Menu class="h-6 w-6" />
            </Button>
          </SheetTrigger>
          <SheetContent side="left" class="border-r border-emerald-100/80 bg-white/95 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/95">
            <SheetHeader>
              <SheetTitle class="flex items-center gap-2">
                <span class="grid h-10 w-10 place-items-center rounded-2xl border border-emerald-200 bg-white p-2 dark:border-emerald-400/20">
                  <img :src="logoImage" alt="UII Dalwa" class="h-7 w-auto object-contain" />
                </span>
                <span>Tracer Study</span>
              </SheetTitle>
            </SheetHeader>
            <div class="space-y-4 py-4">
              <div class="px-3 py-2">
                <h2 class="mb-2 px-4 text-lg font-semibold tracking-tight">
                  Menu
                </h2>
                <div class="space-y-1">
                  <Button variant="secondary" class="w-full justify-start">
                    <FileText class="mr-2 h-4 w-4" />
                    Kuesioner
                  </Button>
                </div>
              </div>
            </div>
          </SheetContent>
        </Sheet>

        <div class="flex items-center gap-3">
          <span
            class="grid h-12 w-12 place-items-center rounded-2xl border border-emerald-200/70 bg-white p-2 shadow-lg shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95"
          >
            <img :src="logoImage" alt="UII Dalwa" class="h-8 w-auto object-contain" />
          </span>
          <span class="hidden sm:block">
            <span class="block text-base font-black leading-tight">Tracer Study</span>
            <span class="block text-xs font-medium text-emerald-700 dark:text-emerald-300">Portal Alumni</span>
          </span>
        </div>

        <div class="flex items-center gap-2">
          <Button
            variant="ghost"
            size="icon"
            @click="toggleTheme"
            class="rounded-2xl"
            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          >
            <Sun v-if="isDark" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" class="relative h-10 w-10 rounded-2xl">
                <Avatar class="h-8 w-8">
                  <AvatarImage src="" alt="User" />
                  <AvatarFallback class="bg-emerald-500/10 font-black text-emerald-700 dark:text-emerald-300">{{
                    userInitials
                  }}</AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56 rounded-2xl" align="end">
              <DropdownMenuLabel>
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">
                    {{ authStore.user?.alumni?.nama }}
                  </p>
                  <p class="text-xs leading-none text-muted-foreground">
                    {{ authStore.user?.alumni?.nim }}
                  </p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                @click="handleLogout"
                class="text-destructive focus:text-destructive"
              >
                <LogOut class="mr-2 h-4 w-4" />
                <span>Log out</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8 space-y-8">
      <section
        class="overflow-hidden rounded-[2.5rem] border border-white/80 bg-white/72 p-6 shadow-2xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.05] md:p-8"
      >
      <div class="space-y-2">
        <h1 class="text-4xl font-black tracking-tight">
          Halo, {{ firstName }}! 👋
        </h1>
        <p class="text-slate-600 dark:text-slate-300">
          {{ authStore.user?.alumni?.prodi?.nama }} • Lulusan
          {{ authStore.user?.alumni?.tahun_lulus }}
        </p>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-3">
        <Card class="rounded-[2rem] border-slate-200/80 bg-white/80 shadow-lg shadow-slate-900/5 dark:border-white/10 dark:bg-white/[0.06]">
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Total Kuesioner</CardTitle>
            <FileText class="h-5 w-5 text-emerald-600 dark:text-emerald-300" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ qStore.counts.all }}
            </div>
          </CardContent>
        </Card>
        <Card class="rounded-[2rem] border-slate-200/80 bg-white/80 shadow-lg shadow-slate-900/5 dark:border-white/10 dark:bg-white/[0.06]">
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Sudah Diisi</CardTitle>
            <CheckCircle class="h-4 w-4 text-success text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ qStore.counts.completed }}
            </div>
          </CardContent>
        </Card>
        <Card class="rounded-[2rem] border-slate-200/80 bg-white/80 shadow-lg shadow-slate-900/5 dark:border-white/10 dark:bg-white/[0.06]">
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Belum Diisi</CardTitle>
            <Clock class="h-4 w-4 text-warning text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">
              {{ qStore.counts.pending }}
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="mt-8 space-y-5">
        <div
          class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
          <h2 class="text-2xl font-black tracking-tight">
            Kuesioner Tersedia
          </h2>
          <div
            class="flex flex-col gap-2 w-full md:w-auto md:flex-row md:items-center"
          >
            <div class="relative w-full md:max-w-sm items-center">
              <Input
                type="text"
                placeholder="Cari kuesioner..."
                class="rounded-2xl bg-white/80 pl-10 dark:bg-slate-950/50"
                v-model="searchQuery"
              />
              <span
                class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
              >
                <span class="text-sm text-muted-foreground">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="lucide lucide-search"
                  >
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                  </svg>
                </span>
              </span>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
              <Select v-model="sortBy">
                <SelectTrigger class="flex-1 rounded-2xl bg-white/80 md:w-[180px] dark:bg-slate-950/50">
                  <SelectValue placeholder="Urutkan" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="newest">Terbaru</SelectItem>
                  <SelectItem value="title">Nama</SelectItem>
                  <SelectItem value="year">Tahun</SelectItem>
                </SelectContent>
              </Select>
              <Button variant="outline" size="icon" class="rounded-2xl" @click="toggleSortOrder">
                <ArrowUpDown class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        <!-- Custom Tabs -->
        <div class="flex space-x-2">
          <Button
            :variant="activeTab === 'pending' ? 'default' : 'outline'"
            @click="activeTab = 'pending'"
            class="rounded-2xl"
          >
            Belum Diisi
          </Button>
          <Button
            :variant="activeTab === 'completed' ? 'default' : 'outline'"
            @click="activeTab = 'completed'"
            class="rounded-2xl"
          >
            Sudah Diisi
          </Button>
        </div>

        <div
          v-if="qStore.loading && qStore.questionnaires.length === 0"
          class="flex justify-center py-12"
        >
          <div
            class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
          ></div>
        </div>

        <div
          v-else-if="sortedQuestionnaires.length === 0"
          class="flex flex-col items-center justify-center py-12 text-center"
        >
          <FileText class="h-12 w-12 text-muted-foreground mb-4 opacity-20" />
          <h3 class="text-lg font-medium">Tidak Ada Kuesioner</h3>
          <p class="text-muted-foreground">
            Belum ada kuesioner yang tersedia untuk Anda.
          </p>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Card
            v-for="q in sortedQuestionnaires"
            :key="q.id"
            class="flex flex-col rounded-[2rem] border-slate-200/80 bg-white/82 shadow-xl shadow-slate-900/5 transition-all hover:-translate-y-1 hover:border-emerald-200 hover:shadow-2xl hover:shadow-emerald-700/10 dark:border-white/10 dark:bg-white/[0.06] dark:hover:border-emerald-400/30"
          >
            <CardHeader>
              <div class="flex justify-between items-start gap-2">
                <CardTitle class="line-clamp-1 font-black group-hover:text-emerald-700">{{ q.title }}</CardTitle>
                <div class="flex gap-1 shrink-0">
                  <Badge v-if="q.is_mandatory" variant="destructive"
                    >Wajib</Badge
                  >
                  <Badge
                    v-if="q.has_submitted"
                    variant="secondary"
                    class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                    >Selesai</Badge
                  >
                </div>
              </div>
              <CardDescription class="line-clamp-2 h-10">{{
                q.description
              }}</CardDescription>
            </CardHeader>
            <CardContent class="flex-1">
              <div
                class="flex items-center gap-4 text-sm text-muted-foreground"
              >
                <div class="flex items-center gap-1">
                  <FileText class="h-4 w-4" />
                  <span>{{ q.year?.name }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <div class="h-1 w-1 rounded-full bg-border"></div>
                  <span>{{ q.questions_count }} pertanyaan</span>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <Button
                v-if="!q.has_submitted"
                class="w-full rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
                @click="router.push(`/questionnaire/${q.id}`)"
              >
                Isi Kuesioner
              </Button>
              <Button
                v-else
                variant="secondary"
                class="w-full rounded-2xl"
                @click="router.push(`/questionnaire/${q.id}`)"
              >
                Lihat Jawaban
              </Button>
            </CardFooter>
          </Card>
        </div>

        <!-- Load More Button -->
        <div v-if="hasMore" class="flex justify-center pt-6 pb-8">
          <Button
            variant="outline"
            @click="loadMore"
            :disabled="qStore.loading"
            class="min-w-[200px] rounded-2xl"
          >
            <span
              v-if="qStore.loading"
              class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
            ></span>
            {{ qStore.loading ? "Memuat..." : "Load More" }}
          </Button>
        </div>
      </div>
      </section>
    </main>
  </div>
</template>
