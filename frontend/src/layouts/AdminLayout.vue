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
  Download,
  LogOut,
  Menu,
  GraduationCap,
  Database,
  Building2,
  BookOpen,
  CalendarDays,
  ChevronDown,
} from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { isDark, toggleTheme } = useTheme();

const sidebarItems = [
  { label: "Dashboard", icon: LayoutDashboard, route: "/admin" },
  { label: "Alumni", icon: Users, route: "/admin/alumni" },
  { label: "Kuesioner", icon: FileText, route: "/admin/questionnaires" },
];

const dashboardItem = sidebarItems[0];
const remainingItems = sidebarItems.slice(1);

const isActive = (path: string) => route.path === path;

async function handleLogout() {
  await authStore.logout();
  router.push("/login");
}
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
                Tracer Admin
              </SheetTitle>
            </SheetHeader>
            <nav class="grid gap-2 text-lg font-medium mt-8">
              <template v-for="item in sidebarItems" :key="item.route">
                <Button
                  :variant="isActive(item.route) ? 'secondary' : 'ghost'"
                  class="justify-start gap-3 w-full"
                  @click="router.push(item.route)"
                >
                  <component :is="item.icon" class="h-5 w-5" /> {{ item.label }}
                </Button>
              </template>
              <router-link
                to="/admin/faculties"
                custom
                v-slot="{ href, navigate, isActive: isLinkActive }"
              >
                <Button
                  :variant="isLinkActive ? 'secondary' : 'ghost'"
                  class="w-full justify-start gap-3"
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
                  :variant="isLinkActive ? 'secondary' : 'ghost'"
                  class="w-full justify-start gap-3"
                  :href="href"
                  @click="navigate"
                >
                  <BookOpen class="h-5 w-5" />
                  Prodi
                </Button>
              </router-link>
              <Button
                variant="ghost"
                class="justify-start gap-3 text-destructive"
                @click="handleLogout"
              >
                <LogOut class="h-5 w-5" /> Logout
              </Button>
            </nav>
          </SheetContent>
        </Sheet>

        <!-- Logo -->
        <div class="flex items-center gap-8">
          <div class="flex items-center gap-2 font-bold text-xl">
            <GraduationCap class="h-6 w-6 text-primary hidden md:block" />
            <span>Tracer Admin</span>
          </div>

          <!-- Desktop Nav -->
          <nav class="hidden md:flex items-center gap-1">
            <!-- First Item (Dashboard) -->
            <template v-if="dashboardItem">
              <Button
                :variant="isActive(dashboardItem.route) ? 'secondary' : 'ghost'"
                size="sm"
                class="justify-start gap-2"
                :class="{
                  'bg-primary/10 text-primary': isActive(dashboardItem.route),
                }"
                @click="router.push(dashboardItem.route)"
              >
                <component :is="dashboardItem.icon" class="h-4 w-4" />
                {{ dashboardItem.label }}
              </Button>
            </template>

            <!-- Master Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" class="gap-2">
                  <Database class="h-4 w-4" /> Master
                  <ChevronDown class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
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

            <!-- Remaining Items -->
            <template v-for="item in remainingItems" :key="item.route">
              <Button
                :variant="isActive(item.route) ? 'secondary' : 'ghost'"
                size="sm"
                class="justify-start gap-2"
                :class="{ 'bg-primary/10 text-primary': isActive(item.route) }"
                @click="router.push(item.route)"
              >
                <component :is="item.icon" class="h-4 w-4" /> {{ item.label }}
              </Button>
            </template>
          </nav>
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
                <div
                  class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
                >
                  {{ authStore.user?.username?.charAt(0).toUpperCase() || "A" }}
                </div>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56" align="end">
              <DropdownMenuLabel>
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">
                    {{ authStore.user?.username || "Admin" }}
                  </p>
                  <p class="text-xs leading-none text-muted-foreground">
                    Administrator
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

    <!-- Page Content -->
    <main class="container mx-auto px-4 py-8 space-y-8">
      <slot />
    </main>
  </div>
</template>
