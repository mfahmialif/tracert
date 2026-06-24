<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { Card, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { useTheme } from "@/composables/useTheme";
import PublicPageLoader from "./PublicPageLoader.vue";
import { ArrowRight, FileText, Home, Moon, Sparkles, Sun } from "lucide-vue-next";

const router = useRouter();
const { isDark, toggleTheme } = useTheme();
const loading = ref(true);
const questionnaires = ref<any[]>([]);
const logoImage = "/logo_uiidalwa.png";

onMounted(async () => {
  try {
    const { data } = await api.get("/public/questionnaires");
    questionnaires.value = data.questionnaires;
  } catch (error) {
    console.error("Failed to fetch questionnaires", error);
  } finally {
    loading.value = false;
  }
});

function startSurvey(id: number) {
  router.push(`/public/questionnaires/${id}`);
}
</script>

<template>
  <div
    class="min-h-screen overflow-hidden bg-transparent text-slate-950 antialiased dark:text-white"
  >
    <PublicPageLoader v-if="loading" message="Sedang memuat daftar kuesioner..." />

    <div class="pointer-events-none fixed inset-0 -z-10 hidden md:block">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(20,184,166,0.16),transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.86),rgba(240,253,244,0.92))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.2),transparent_34%),radial-gradient(circle_at_85%_0%,rgba(45,212,191,0.12),transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.96),rgba(6,30,24,0.94))]"
      />
      <div class="absolute -top-40 left-1/3 h-[28rem] w-[28rem] rounded-full bg-emerald-500/14 blur-3xl dark:bg-emerald-400/10" />
      <div class="absolute bottom-0 right-0 h-[24rem] w-[24rem] rounded-full bg-teal-500/12 blur-3xl dark:bg-teal-400/10" />
    </div>

    <header
      class="sticky top-0 z-50 border-b border-white/70 bg-white/82 shadow-lg shadow-slate-900/5 backdrop-blur-md md:backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/82"
    >
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              class="grid h-12 w-12 place-items-center rounded-2xl border border-emerald-200/70 bg-white p-2 shadow-lg shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95"
            >
              <img :src="logoImage" alt="UII Dalwa" class="h-8 w-auto object-contain" />
            </div>
            <div>
              <h1
                class="text-lg font-black tracking-tight text-slate-950 dark:text-white"
              >
                Kuesioner Publik
              </h1>
              <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                Tracer Study UII Dalwa
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="ghost"
              size="icon"
              class="rounded-2xl"
              :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
              @click="toggleTheme"
            >
              <Sun v-if="isDark" class="h-5 w-5" />
              <Moon v-else class="h-5 w-5" />
            </Button>
            <Button
              variant="outline"
              class="rounded-2xl border-emerald-200/80 bg-white/70 text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 dark:border-emerald-400/20 dark:bg-white/5 dark:text-emerald-200 dark:hover:bg-emerald-400/10"
              @click="router.push('/')"
            >
              <Home class="mr-2 h-4 w-4" />
              Home
            </Button>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
      <div class="mx-auto mb-12 max-w-3xl text-center">
        <div
          class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"
        >
          <Sparkles class="h-4 w-4" />
          Kontribusi alumni untuk pengembangan institusi
        </div>
        <h2 class="mb-4 text-4xl font-black tracking-tight md:text-6xl">
          <span
            class="bg-gradient-to-r from-emerald-600 via-teal-600 to-green-700 bg-clip-text text-transparent dark:from-emerald-300 dark:via-teal-300 dark:to-lime-300"
          >
            Kuesioner Tracer Study
          </span>
        </h2>
        <p class="mx-auto max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">
          Pilih kuesioner yang tersedia dan bantu kampus membaca perkembangan
          alumni, mutu layanan, serta kebutuhan pengembangan akademik.
        </p>
      </div>

      <div
        v-if="!loading && questionnaires.length === 0"
        class="mx-auto max-w-xl rounded-[2rem] border border-slate-200/80 bg-white/78 p-10 text-center shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
      >
        <div class="mx-auto mb-5 grid h-20 w-20 place-items-center rounded-3xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
          <FileText class="h-10 w-10" />
        </div>
        <h3 class="mb-2 text-xl font-black">Belum Ada Kuesioner</h3>
        <p class="text-slate-600 dark:text-slate-300">
          Saat ini belum ada kuesioner publik yang tersedia. Silakan cek kembali nanti.
        </p>
      </div>

      <div v-else-if="!loading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="q in questionnaires"
          :key="q.id"
          class="group cursor-pointer overflow-hidden rounded-[2rem] border-slate-200/80 bg-white/82 shadow-xl shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-2xl hover:shadow-emerald-700/10 dark:border-white/10 dark:bg-white/[0.06] dark:hover:border-emerald-400/30"
          @click="startSurvey(q.id)"
        >
          <CardHeader class="pb-5">
            <div class="mb-4 flex items-start justify-between gap-3">
              <Badge
                variant="outline"
                class="rounded-full border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"
              >
                {{ q.year }}
              </Badge>
              <Badge
                variant="secondary"
                class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600 dark:bg-white/10 dark:text-slate-300"
              >
                <FileText class="h-3 w-3 mr-1" />
                {{ q.questions_count }} pertanyaan
              </Badge>
            </div>
            <CardTitle
              class="text-xl font-black transition-colors group-hover:text-emerald-700 dark:group-hover:text-emerald-300"
            >
              {{ q.title }}
            </CardTitle>
            <CardDescription class="line-clamp-3 pt-2 leading-7 text-slate-600 dark:text-slate-300">
              {{ q.description || "Tidak ada deskripsi." }}
            </CardDescription>
          </CardHeader>
          <CardFooter class="pt-0">
            <Button
              class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/15 transition-all hover:from-emerald-700 hover:to-teal-700 group-hover:shadow-emerald-700/25 dark:from-emerald-400 dark:to-teal-400 dark:text-emerald-950 dark:hover:from-emerald-300 dark:hover:to-teal-300"
              @click.stop="startSurvey(q.id)"
            >
              Isi Kuesioner
              <ArrowRight
                class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform"
              />
            </Button>
          </CardFooter>
        </Card>
      </div>
    </main>

    <footer
      class="mt-20 border-t border-slate-200/80 bg-white/60 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/60"
    >
      <div
        class="container mx-auto px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400"
      >
        <p>&copy; 2026 Tracer Study UII Dalwa. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
