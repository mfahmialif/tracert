<script setup lang="ts">
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useTheme } from "@/composables/useTheme";
import { Button } from "@/components/ui/button";
import {
  Sheet,
  SheetContent,
  SheetTrigger,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Sun,
  Moon,
  LayoutDashboard,
  Users,
  FileText,
  LogOut,
  Menu,
  GraduationCap,
  Database,
  Building2,
  BookOpen,
  CalendarDays,
  ChevronDown,
  User,
  UserCog,
} from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { isDark, toggleTheme } = useTheme();
const logoImage = "/logo_uiidalwa.png";

const sidebarItems = [
  { label: "Dashboard", icon: LayoutDashboard, route: "/admin" },
  { label: "Alumni", icon: Users, route: "/admin/alumni" },
  { label: "Kuesioner", icon: FileText, route: "/admin/questionnaires" },
];

const dashboardItem = sidebarItems[0];
const remainingItems = sidebarItems.slice(1);

const isActive = (path: string) => {
  if (path === "/admin") return route.path === path;
  return route.path === path || route.path.startsWith(`${path}/`);
};

function navButtonClass(path: string) {
  return [
    "justify-start gap-2 rounded-2xl text-sm font-semibold transition-all",
    isActive(path)
      ? "bg-emerald-600 text-white shadow-lg shadow-emerald-700/15 hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
      : "text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-emerald-300",
  ];
}

async function handleLogout() {
  await authStore.logout();
  router.push("/login");
}
</script>

<template>
  <div class="min-h-screen overflow-hidden bg-transparent text-slate-950 dark:text-white">
    <div class="pointer-events-none fixed inset-0 -z-10 hidden md:block">
      <div class="absolute left-[-12rem] top-28 h-[32rem] w-[32rem] rounded-full bg-emerald-400/10 blur-[130px] dark:bg-emerald-400/8" />
      <div class="absolute right-[-10rem] top-12 h-[28rem] w-[28rem] rounded-full bg-teal-300/8 blur-[140px] dark:bg-cyan-400/7" />
      <div class="absolute bottom-[-12rem] left-1/3 h-[30rem] w-[38rem] rounded-full bg-blue-500/6 blur-[150px] dark:bg-blue-700/10" />
    </div>

    <header
      class="sticky top-0 z-40 w-full border-b border-white/70 bg-white/80 shadow-lg shadow-slate-900/5 backdrop-blur-md md:backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/80"
    >
      <div class="mx-auto flex h-[4.5rem] max-w-[1440px] items-center justify-between px-4 md:h-20 md:px-6">
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
                <span>Tracer Admin</span>
              </SheetTitle>
            </SheetHeader>
            <nav class="mt-8 grid gap-2 text-lg font-medium">
              <template v-for="item in sidebarItems" :key="item.route">
                <Button
                  variant="ghost"
                  :class="['w-full', navButtonClass(item.route)]"
                  @click="router.push(item.route)"
                >
                  <component :is="item.icon" class="h-5 w-5" /> {{ item.label }}
                </Button>
              </template>
              <Button
                v-if="authStore.isSuperAdmin"
                variant="ghost"
                :class="['w-full', navButtonClass('/admin/users')]"
                @click="router.push('/admin/users')"
              >
                <UserCog class="h-5 w-5" /> User & Level
              </Button>
              <router-link
                to="/admin/faculties"
                custom
                v-slot="{ href, navigate, isActive: isLinkActive }"
              >
                <Button
                  variant="ghost"
                  :class="['w-full', navButtonClass('/admin/faculties')]"
                  :href="href"
                  @click="navigate"
                >
                  <Building2 class="h-5 w-5" />
                  Fakultas
                </Button>
              </router-link>
              <router-link
                to="/admin/prodis"
                custom
                v-slot="{ href, navigate, isActive: isLinkActive }"
              >
                <Button
                  variant="ghost"
                  :class="['w-full', navButtonClass('/admin/prodis')]"
                  :href="href"
                  @click="navigate"
                >
                  <BookOpen class="h-5 w-5" />
                  Prodi
                </Button>
              </router-link>
              <router-link
                to="/admin/years"
                custom
                v-slot="{ href, navigate, isActive: isLinkActive }"
              >
                <Button
                  variant="ghost"
                  :class="['w-full', navButtonClass('/admin/years')]"
                  :href="href"
                  @click="navigate"
                >
                  <CalendarDays class="h-5 w-5" />
                  Tahun
                </Button>
              </router-link>
              <Button
                variant="ghost"
                class="justify-start gap-3 rounded-2xl text-destructive hover:bg-destructive/10"
                @click="handleLogout"
              >
                <LogOut class="h-5 w-5" /> Logout
              </Button>
            </nav>
          </SheetContent>
        </Sheet>

        <div class="flex items-center gap-8">
          <div class="hidden items-center gap-3 md:flex">
            <span
              class="grid h-11 w-11 place-items-center rounded-2xl border border-emerald-200/70 bg-white/90 p-2 shadow-lg shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95"
            >
              <img :src="logoImage" alt="UII Dalwa" class="h-7 w-auto object-contain" />
            </span>
            <span>
              <span class="block text-base font-black leading-tight">Tracer Admin</span>
              <span class="block text-xs font-medium text-emerald-700 dark:text-emerald-300">UII Dalwa</span>
            </span>
          </div>

          <nav class="hidden md:flex items-center gap-1">
            <template v-if="dashboardItem">
              <Button
                variant="ghost"
                size="sm"
                :class="navButtonClass(dashboardItem.route)"
                @click="router.push(dashboardItem.route)"
              >
                <component :is="dashboardItem.icon" class="h-4 w-4" />
                {{ dashboardItem.label }}
              </Button>
            </template>

            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button
                  variant="ghost"
                  size="sm"
                  :class="[
                    'gap-2 rounded-2xl text-sm font-semibold',
                    route.path.startsWith('/admin/faculties') || route.path.startsWith('/admin/prodis') || route.path.startsWith('/admin/years')
                      ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-700/15 hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300'
                      : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-emerald-300',
                  ]"
                >
                  <Database class="h-4 w-4" /> Master
                  <ChevronDown class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="rounded-2xl">
                <DropdownMenuItem @click="router.push('/admin/faculties')">
                  <Building2 class="mr-2 h-4 w-4" /> Fakultas
                </DropdownMenuItem>
                <DropdownMenuItem @click="router.push('/admin/prodis')">
                  <BookOpen class="mr-2 h-4 w-4" /> Prodi
                </DropdownMenuItem>
                <DropdownMenuItem @click="router.push('/admin/years')">
                  <CalendarDays class="mr-2 h-4 w-4" /> Tahun
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <template v-for="item in remainingItems" :key="item.route">
              <Button
                variant="ghost"
                size="sm"
                :class="navButtonClass(item.route)"
                @click="router.push(item.route)"
              >
                <component :is="item.icon" class="h-4 w-4" /> {{ item.label }}
              </Button>
            </template>
            <Button
              v-if="authStore.isSuperAdmin"
              variant="ghost"
              size="sm"
              :class="navButtonClass('/admin/users')"
              @click="router.push('/admin/users')"
            >
              <UserCog class="h-4 w-4" /> User & Level
            </Button>
          </nav>
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
                <div
                  class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-500/10 font-black text-emerald-700 ring-1 ring-emerald-200/70 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20"
                >
                  {{ authStore.user?.username?.charAt(0).toUpperCase() || "A" }}
                </div>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56 rounded-2xl" align="end">
              <DropdownMenuLabel>
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">
                    {{ authStore.user?.username || "Admin" }}
                  </p>
                  <p class="text-xs leading-none text-muted-foreground">
                    {{ authStore.isSuperAdmin ? "Superadministrator" : "Administrator" }}
                  </p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="router.push('/admin/profile')">
                <User class="mr-2 h-4 w-4" />
                <span>Profile</span>
              </DropdownMenuItem>
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

    <main class="mx-auto w-full max-w-[1440px] space-y-8 px-4 py-8 md:px-6 lg:py-10">
      <div class="space-y-8">
      <slot />
      </div>
    </main>
  </div>
</template>
