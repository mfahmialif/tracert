<script setup lang="ts">
import { onMounted, computed } from "vue";
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
  GraduationCap,
  Menu,
  LogOut,
  FileText,
  CheckCircle,
  Clock,
  User,
} from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();
const qStore = useQuestionnaireStore();
const { isDark, toggleTheme } = useTheme();

onMounted(() => {
  qStore.fetchQuestionnaires();
});

async function handleLogout() {
  await authStore.logout();
  router.push("/login");
}

const completedCount = computed(
  () => qStore.questionnaires.filter((q) => q.has_submitted).length
);
const pendingCount = computed(
  () => qStore.questionnaires.filter((q) => !q.has_submitted).length
);
const userInitials = computed(
  () => authStore.user?.alumni?.nama?.charAt(0).toUpperCase() || "U"
);
const firstName = computed(
  () => authStore.user?.alumni?.nama?.split(" ")[0] || "User"
);
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Navbar -->
    <header
      class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
      <div class="container flex h-16 items-center justify-between px-4">
        <!-- Mobile Menu -->
        <Sheet>
          <SheetTrigger as-child>
            <Button variant="ghost" size="icon" class="md:hidden">
              <Menu class="h-6 w-6" />
            </Button>
          </SheetTrigger>
          <SheetContent side="left">
            <SheetHeader>
              <SheetTitle class="flex items-center gap-2">
                <GraduationCap class="h-6 w-6 text-primary" />
                Tracer Study
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

        <!-- Logo -->
        <div class="flex items-center gap-2 font-bold text-xl">
          <GraduationCap class="h-6 w-6 text-primary hidden md:block" />
          <span
            class="bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent"
            >Tracer Study</span
          >
        </div>

        <!-- Right Actions -->
        <div class="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            @click="toggleTheme"
            class="rounded-full"
          >
            <Sun v-if="isDark" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                <Avatar class="h-8 w-8">
                  <AvatarImage src="" alt="User" />
                  <AvatarFallback class="bg-primary/10 text-primary">{{
                    userInitials
                  }}</AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56" align="end">
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

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 space-y-8">
      <!-- Welcome -->
      <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">
          Halo, {{ firstName }}! 👋
        </h1>
        <p class="text-muted-foreground">
          {{ authStore.user?.alumni?.prodi?.nama }} • Lulusan
          {{ authStore.user?.alumni?.tahun_lulus }}
        </p>
      </div>

      <!-- Stats -->
      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Total Kuesioner</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ qStore.questionnaires.length }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Sudah Diisi</CardTitle>
            <CheckCircle class="h-4 w-4 text-success text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ completedCount }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Belum Diisi</CardTitle>
            <Clock class="h-4 w-4 text-warning text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">
              {{ pendingCount }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Questionnaires -->
      <div class="space-y-4">
        <h2 class="text-xl font-semibold tracking-tight">Kuesioner Tersedia</h2>

        <div v-if="qStore.loading" class="flex justify-center py-12">
          <div
            class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
          ></div>
        </div>

        <div
          v-else-if="qStore.questionnaires.length === 0"
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
            v-for="(q, index) in qStore.questionnaires"
            :key="q.id"
            class="flex flex-col transition-all hover:shadow-lg"
          >
            <CardHeader>
              <div class="flex justify-between items-start gap-2">
                <CardTitle class="line-clamp-1">{{ q.title }}</CardTitle>
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
                  <span>{{ q.type }}</span>
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
                class="w-full"
                @click="router.push(`/questionnaire/${q.id}`)"
              >
                Isi Kuesioner
              </Button>
              <Button v-else variant="outline" class="w-full" disabled>
                Sudah Diisi
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </main>
  </div>
</template>
