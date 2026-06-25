<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useTheme } from "../composables/useTheme";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import {
  Sun,
  Moon,
  Eye,
  EyeOff,
  AlertCircle,
  ArrowLeft,
  ShieldCheck,
  Sparkles,
} from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();
const { isDark, toggleTheme } = useTheme();
const logoImage = "/logo_uiidalwa.png";

const nim = ref("");

async function handleLogin() {
  const success = await authStore.loginAlumni(nim.value);
  if (success) {
    router.push("/home");
  }
}
</script>

<template>
  <div
    class="relative flex min-h-screen items-center justify-center overflow-hidden bg-transparent p-4 text-slate-950 antialiased transition-colors duration-300 dark:text-white"
  >
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(20,184,166,0.16),transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.86),rgba(240,253,244,0.92))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.2),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(45,212,191,0.12),transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.96),rgba(6,30,24,0.94))]"
      />
      <div
        class="absolute -right-32 -top-32 h-[30rem] w-[30rem] rounded-full bg-emerald-500/14 blur-3xl dark:bg-emerald-400/10"
      />
      <div
        class="absolute -bottom-32 -left-32 h-[30rem] w-[30rem] rounded-full bg-teal-500/12 blur-3xl dark:bg-teal-400/10"
      />
    </div>

    <div class="absolute left-4 top-4 z-10 flex items-center gap-2">
      <Button
        variant="ghost"
        class="rounded-2xl bg-white/60 backdrop-blur-xl hover:bg-white dark:bg-white/5 dark:hover:bg-white/10"
        @click="router.push('/')"
      >
        <ArrowLeft class="mr-2 h-4 w-4" />
        Home
      </Button>
    </div>

    <Button
      variant="ghost"
      size="icon"
      class="absolute right-4 top-4 z-10 rounded-2xl bg-white/60 backdrop-blur-xl hover:bg-white dark:bg-white/5 dark:hover:bg-white/10"
      :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
      @click="toggleTheme"
    >
      <Sun v-if="isDark" class="h-5 w-5 transition-all" />
      <Moon v-else class="h-5 w-5 transition-all" />
    </Button>

    <div class="relative z-10 grid w-full max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
      <section class="hidden space-y-8 lg:block">
        <div
          class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"
        >
          <Sparkles class="h-4 w-4" />
          Portal resmi tracer study
        </div>
        <div class="space-y-5">
          <h1 class="max-w-2xl text-5xl font-black tracking-tight md:text-6xl">
            Selamat Datang di Portal Alumni
            <span class="block bg-gradient-to-r from-emerald-600 via-teal-600 to-green-700 bg-clip-text text-transparent dark:from-emerald-300 dark:via-teal-300 dark:to-lime-300">
              Tracer Study UII Dalwa.
            </span>
          </h1>
          <p class="max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300">
            Akses dashboard alumni untuk mengelola kuesioner dan memberikan feedback berharga bagi almamater tercinta.
          </p>
        </div>
        <div class="grid max-w-xl gap-4 sm:grid-cols-2">
          <div class="rounded-[2rem] border border-white/80 bg-white/72 p-5 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]">
            <ShieldCheck class="mb-4 h-7 w-7 text-emerald-600 dark:text-emerald-300" />
            <h3 class="font-black">Aman & Terpusat</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
              Data alumni dan kuesioner dikelola dalam satu sistem.
            </p>
          </div>
          <div class="rounded-[2rem] border border-white/80 bg-white/72 p-5 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]">
            <Sparkles class="mb-4 h-7 w-7 text-emerald-600 dark:text-emerald-300" />
            <h3 class="font-black">Modern & Responsif</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
              Nyaman digunakan di mode terang maupun gelap.
            </p>
          </div>
        </div>
      </section>

      <Card
        class="relative z-10 w-full overflow-hidden rounded-[2.5rem] border-white/80 bg-white/82 shadow-2xl shadow-slate-900/10 backdrop-blur-2xl dark:border-white/10 dark:bg-white/[0.06] dark:shadow-black/30"
      >
      <CardHeader class="space-y-1 text-center">
        <div class="mb-4 flex justify-center">
          <div class="grid h-24 w-24 place-items-center rounded-[2rem] border border-emerald-200/80 bg-white p-4 shadow-xl shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95">
            <img :src="logoImage" alt="UII Dalwa" class="h-16 w-auto object-contain" />
          </div>
        </div>
        <CardTitle
          class="bg-gradient-to-r from-emerald-600 via-teal-600 to-green-700 bg-clip-text text-3xl font-black tracking-tight text-transparent dark:from-emerald-300 dark:via-teal-300 dark:to-lime-300"
        >
          Portal Alumni
        </CardTitle>
        <CardDescription class="text-slate-600 dark:text-slate-300">
          Silakan masukkan NIM untuk masuk
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-2">
            <Label for="nim">Nomor Induk Mahasiswa (NIM)</Label>
            <Input
              id="nim"
              v-model="nim"
              placeholder="Masukkan NIM Anda"
              required
              class="h-12 rounded-2xl bg-white/70 dark:bg-slate-950/50"
            />
          </div>

          <Alert v-if="authStore.error" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ authStore.error }}</AlertDescription>
          </Alert>

          <Button
            type="submit"
            class="h-12 w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/20 hover:from-emerald-700 hover:to-teal-700 dark:from-emerald-400 dark:to-teal-400 dark:text-emerald-950 dark:hover:from-emerald-300 dark:hover:to-teal-300"
            :disabled="authStore.loading"
          >
            <span
              v-if="authStore.loading"
              class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
            ></span>
            {{ authStore.loading ? "Memproses..." : "Masuk" }}
          </Button>
        </form>
      </CardContent>
      <CardFooter>
        <p class="text-xs text-center text-muted-foreground w-full">
          &copy; 2026 Tracer Study • UII Dalwa
        </p>
      </CardFooter>
    </Card>
    </div>
  </div>
</template>
