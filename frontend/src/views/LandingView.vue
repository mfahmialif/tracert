<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/composables/useTheme";
import PublicPageLoader from "@/views/public/PublicPageLoader.vue";
import {
  ArrowRight,
  Award,
  BarChart3,
  BriefcaseBusiness,
  CheckCircle2,
  ClipboardList,
  Database,
  GraduationCap,
  LineChart,
  Menu,
  Moon,
  ShieldCheck,
  Sparkles,
  Sun,
  Target,
  Users,
  X,
} from "lucide-vue-next";

const router = useRouter();
const { isDark, toggleTheme } = useTheme();

const isVisible = ref(false);
const loading = ref(true);
const isScrolled = ref(false);
const mobileMenuOpen = ref(false);
const activeSection = ref("");
let aosObserver: IntersectionObserver | null = null;
let sectionObserver: IntersectionObserver | null = null;

const heroImage = new URL("@/assets/img/hero-professional.svg", import.meta.url).href;
const logoImage = "/logo_uiidalwa.png";

const navItems = [
  { label: "Home", href: "#home", id: "home" },
  { label: "Fitur", href: "#features", id: "features" },
  { label: "Proses", href: "#process", id: "process" },
  { label: "Kontak", href: "#contact", id: "contact" },
];

const sectionIds = navItems.map((item) => item.id);

const stats = [
  { number: "1000+", label: "Alumni Terdata", icon: Users },
  { number: "95%", label: "Response Rate", icon: LineChart },
  { number: "50+", label: "Survey Aktif", icon: ClipboardList },
  { number: "100%", label: "Data Terintegrasi", icon: ShieldCheck },
];

const features = [
  {
    icon: Users,
    title: "Alumni Tracking",
    description:
      "Pantau riwayat karier, status pekerjaan, dan perkembangan alumni dalam satu dashboard yang mudah dibaca.",
    image: new URL("@/assets/img/tracking-professional.svg", import.meta.url).href,
  },
  {
    icon: BarChart3,
    title: "Insight Analytics",
    description:
      "Visualisasi data membantu kampus membaca tren lulusan, kebutuhan kurikulum, dan performa program studi.",
    image: new URL("@/assets/img/analytics-professional.svg", import.meta.url).href,
  },
  {
    icon: ClipboardList,
    title: "Survey Management",
    description:
      "Kelola kuesioner tracer study, distribusi survey, dan progres pengisian dengan alur yang rapi.",
    image: new URL("@/assets/img/survey-professional.svg", import.meta.url).href,
  },
  {
    icon: Award,
    title: "Accreditation Ready",
    description:
      "Siapkan laporan ringkas dan akurat untuk akreditasi, evaluasi mutu, serta pengambilan keputusan institusi.",
    image: new URL("@/assets/img/accreditation-professional.svg", import.meta.url).href,
  },
];

const processSteps = [
  {
    step: "01",
    title: "Kumpulkan Data",
    description: "Alumni mengisi kuesioner melalui portal publik yang responsif.",
    icon: Database,
  },
  {
    step: "02",
    title: "Validasi & Analisis",
    description: "Admin memantau kualitas data dan membaca insight dari dashboard.",
    icon: CheckCircle2,
  },
  {
    step: "03",
    title: "Laporan Institusi",
    description: "Hasil tracer study siap digunakan untuk evaluasi dan akreditasi.",
    icon: BriefcaseBusiness,
  },
];

function preloadImage(src: string) {
  return new Promise<void>((resolve) => {
    const image = new Image();
    image.onload = () => resolve();
    image.onerror = () => resolve();
    image.src = src;

    if (image.complete) resolve();
  });
}

onMounted(async () => {
  window.setTimeout(() => {
    isVisible.value = true;
  }, 120);

  try {
    await Promise.all([
      nextTick(),
      preloadImage(heroImage),
      preloadImage(logoImage),
    ]);
    handleScroll();
    initAosFallback();
    initSectionObserver();
    window.addEventListener("scroll", handleScroll, { passive: true });
  } finally {
    loading.value = false;
  }
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  aosObserver?.disconnect();
  sectionObserver?.disconnect();
});

function handleScroll() {
  // Only do cheap read of scrollY, no layout thrashing DOM measurements
  isScrolled.value = window.scrollY > 24;
}

function initSectionObserver() {
  if (!("IntersectionObserver" in window)) return;
  
  sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          activeSection.value = entry.target.id;
        }
      });
    },
    {
      rootMargin: "-40% 0px -40% 0px",
    }
  );

  sectionIds.forEach((id) => {
    const element = document.getElementById(id);
    if (element) {
      sectionObserver?.observe(element);
    }
  });
}

function navigateToCheck() {
  mobileMenuOpen.value = false;
  router.push("/check");
}

function handleNavClick() {
  mobileMenuOpen.value = false;
}

function navItemClass(item: (typeof navItems)[number]) {
  const isActive = activeSection.value === item.id;

  return [
    "rounded-full px-4 py-2 text-sm font-medium transition",
    isActive
      ? "bg-emerald-600 text-white shadow-sm shadow-emerald-700/15 dark:bg-emerald-400 dark:text-emerald-950"
      : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white",
  ];
}

function mobileNavItemClass(item: (typeof navItems)[number]) {
  const isActive = activeSection.value === item.id;

  return [
    "block rounded-2xl px-4 py-3 text-sm font-medium transition",
    isActive
      ? "bg-emerald-600 text-white shadow-sm dark:bg-emerald-400 dark:text-emerald-950"
      : "text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10",
  ];
}

function initAosFallback() {
  const elements = document.querySelectorAll<HTMLElement>("[data-aos]");

  elements.forEach((element) => {
    const delay = element.dataset.aosDelay;
    const duration = element.dataset.aosDuration;

    if (delay) {
      element.style.transitionDelay = `${delay}ms`;
    }

    if (duration) {
      element.style.transitionDuration = `${duration}ms`;
    }
  });

  const revealVisibleElements = () => {
    elements.forEach((element) => {
      const rect = element.getBoundingClientRect();
      const isVisibleNow = rect.top < window.innerHeight * 0.92 && rect.bottom > 0;

      if (isVisibleNow) {
        element.classList.add("aos-animate");
        aosObserver?.unobserve(element);
      }
    });
  };

  if (!("IntersectionObserver" in window)) {
    elements.forEach((element) => element.classList.add("aos-animate"));
    return;
  }

  aosObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("aos-animate");
          aosObserver?.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.14,
      rootMargin: "0px 0px -8% 0px",
    },
  );

  elements.forEach((element) => aosObserver?.observe(element));
  requestAnimationFrame(revealVisibleElements);
  window.setTimeout(revealVisibleElements, 160);
}
</script>

<template>
  <div
    class="min-h-screen overflow-hidden bg-transparent text-slate-950 antialiased dark:text-white"
  >
    <PublicPageLoader v-if="loading" message="Sedang menyiapkan halaman utama..." />

    <div class="pointer-events-none fixed inset-0 -z-10">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_34%),radial-gradient(circle_at_80%_10%,rgba(20,184,166,0.18),transparent_28%),linear-gradient(180deg,rgba(255,255,255,0.84),rgba(240,253,244,0.9))] dark:bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_34%),radial-gradient(circle_at_80%_10%,rgba(45,212,191,0.14),transparent_28%),linear-gradient(180deg,rgba(2,6,23,0.96),rgba(6,30,24,0.94))]"
      />
      <div
        class="absolute left-1/2 top-0 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full bg-emerald-500/14 blur-3xl dark:bg-emerald-400/12"
      />
      <div
        class="absolute bottom-0 right-0 h-[28rem] w-[28rem] rounded-full bg-teal-500/12 blur-3xl dark:bg-teal-400/10"
      />
    </div>

    <header class="fixed inset-x-0 top-0 z-50 px-4 pt-4 transition-all duration-300 md:px-8">
      <nav
        class="mx-auto flex max-w-7xl items-center justify-between rounded-3xl border px-4 py-3 backdrop-blur-md md:backdrop-blur-2xl transition-all duration-300 md:px-5"
        :class="
          isScrolled
            ? 'border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/5 dark:border-white/10 dark:bg-slate-950/90'
            : 'border-white/70 bg-white/60 shadow-lg shadow-slate-900/5 dark:border-white/10 dark:bg-slate-950/60'
        "
      >
        <button class="flex items-center gap-3 text-left" @click="router.push('/')">
          <span
            class="grid h-12 w-12 place-items-center rounded-2xl border border-emerald-200/70 bg-white p-2 shadow-lg shadow-emerald-900/10 dark:border-emerald-400/20 dark:bg-white/95"
          >
            <img :src="logoImage" alt="UII Dalwa" class="h-8 w-auto object-contain" />
          </span>
          <span class="hidden sm:block">
            <span class="block text-sm font-bold tracking-tight">Tracer Study</span>
            <span class="block text-xs text-emerald-700 dark:text-emerald-300">UII Dalwa</span>
          </span>
        </button>

        <div class="hidden items-center gap-1 rounded-full border border-slate-200/80 bg-white/60 p-1 dark:border-white/10 dark:bg-white/5 md:flex">
          <a
            v-for="item in navItems"
            :key="item.label"
            :href="item.href"
            :class="navItemClass(item)"
          >
            {{ item.label }}
          </a>
        </div>

        <div class="hidden items-center gap-2 md:flex">
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
          <router-link
            to="/public/questionnaires"
            class="rounded-2xl px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-emerald-300"
          >
            Kuesioner
          </router-link>
          <Button
            class="rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-700/20 hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300"
            @click="navigateToCheck"
          >
            Login
            <ArrowRight class="ml-2 h-4 w-4" />
          </Button>
        </div>

        <div class="flex items-center gap-1 md:hidden">
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
            variant="ghost"
            size="icon"
            class="rounded-2xl"
            aria-label="Toggle navigation menu"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <X v-if="mobileMenuOpen" class="h-5 w-5" />
            <Menu v-else class="h-5 w-5" />
          </Button>
        </div>
      </nav>

      <div
        v-if="mobileMenuOpen"
        class="mx-auto mt-3 max-w-7xl rounded-3xl border border-slate-200/80 bg-white/90 p-3 shadow-2xl shadow-slate-900/10 backdrop-blur-md md:backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/90 md:hidden"
      >
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          :class="mobileNavItemClass(item)"
          @click="handleNavClick"
        >
          {{ item.label }}
        </a>
        <div class="mt-2 grid gap-2 border-t border-slate-200/80 pt-3 dark:border-white/10">
          <router-link
            to="/public/questionnaires"
            class="block rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10"
            @click="mobileMenuOpen = false"
          >
            Kuesioner
          </router-link>
          <Button class="w-full rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300" @click="navigateToCheck">
            Login
          </Button>
        </div>
      </div>
    </header>

    <main>
      <section id="home" class="relative scroll-mt-32 px-4 pb-20 pt-32 md:px-8 md:pb-28 md:pt-40">
        <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.02fr_0.98fr]">
          <div
            class="max-w-3xl space-y-8"
            :class="{ 'animate-fade-in-up': isVisible }"
          >
            <div
              class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"
              data-aos="zoom-in"
              data-aos-delay="80"
            >
              <Sparkles class="h-4 w-4" />
              Platform tracer study modern untuk institusi
            </div>

            <div class="space-y-5">
              <h1
                class="text-5xl font-black tracking-tight text-slate-950 dark:text-white md:text-7xl"
                data-aos="fade-up"
                data-aos-delay="140"
              >
                Kelola tracer study dengan data yang
                <span class="block bg-gradient-to-r from-emerald-600 via-teal-600 to-green-700 bg-clip-text text-transparent dark:from-emerald-300 dark:via-teal-300 dark:to-lime-300">
                  jelas, cepat, elegan.
                </span>
              </h1>
              <p
                class="max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300 md:text-xl"
                data-aos="fade-up"
                data-aos-delay="220"
              >
                Sistem terpadu untuk mengumpulkan data alumni, memantau survey,
                membaca insight, dan menyiapkan laporan institusi dengan tampilan
                yang nyaman di mode terang maupun gelap.
              </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row" data-aos="fade-up" data-aos-delay="300">
              <Button
                size="lg"
                class="h-12 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-7 text-white shadow-xl shadow-emerald-700/25 hover:from-emerald-700 hover:to-teal-700 dark:from-emerald-400 dark:to-teal-400 dark:text-emerald-950 dark:hover:from-emerald-300 dark:hover:to-teal-300"
                @click="navigateToCheck"
              >
                <Target class="mr-2 h-5 w-5" />
                Mulai Sekarang
                <ArrowRight class="ml-2 h-5 w-5" />
              </Button>
              <Button
                size="lg"
                variant="outline"
                class="h-12 rounded-2xl border-slate-300/80 bg-white/70 px-7 backdrop-blur hover:bg-white dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                as-child
              >
                <a href="#features">Lihat Fitur</a>
              </Button>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-4 sm:grid-cols-4">
              <div
                v-for="(stat, index) in stats"
                :key="stat.label"
                class="rounded-3xl border border-white/80 bg-white/72 p-4 shadow-lg shadow-slate-900/5 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
                :style="{ animationDelay: `${0.2 + index * 0.08}s` }"
                :class="{ 'animate-fade-in-up': isVisible }"
                data-aos="fade-up"
                :data-aos-delay="360 + index * 80"
              >
                <component :is="stat.icon" class="mb-3 h-5 w-5 text-emerald-600 dark:text-emerald-300" />
                <div class="text-2xl font-black tracking-tight">{{ stat.number }}</div>
                <div class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                  {{ stat.label }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="relative"
            :class="{ 'animate-fade-in-up': isVisible }"
            style="animation-delay: 0.16s"
          >
            <div class="absolute -inset-4 rounded-[2.5rem] bg-gradient-to-br from-emerald-600/24 via-teal-600/18 to-lime-500/16 blur-2xl" />
            <div
              class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white/76 p-3 shadow-2xl shadow-slate-900/10 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06] dark:shadow-black/30"
            >
              <div class="overflow-hidden rounded-[1.5rem] bg-slate-100 dark:bg-slate-900">
                <img
                  :src="heroImage"
                  alt="Tracer study dashboard illustration"
                  class="h-full w-full object-cover"
                />
              </div>
              <div
                class="absolute left-6 top-6 rounded-2xl border border-white/70 bg-white/80 p-4 shadow-xl shadow-slate-900/10 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/70"
              >
                <div class="flex items-center gap-3">
                  <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-500/12 text-emerald-600 dark:text-emerald-300">
                    <CheckCircle2 class="h-5 w-5" />
                  </span>
                  <div>
                    <p class="text-sm font-bold">Survey tervalidasi</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Data siap dianalisis</p>
                  </div>
                </div>
              </div>
              <div
                class="absolute bottom-6 right-6 rounded-2xl border border-white/70 bg-slate-950/90 p-4 text-white shadow-xl shadow-slate-900/20 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10"
              >
                <div class="flex items-center gap-3">
                  <LineChart class="h-9 w-9 text-emerald-300" />
                  <div>
                    <p class="text-xl font-black">+28%</p>
                    <p class="text-xs text-slate-300">engagement alumni</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="features" class="relative scroll-mt-32 px-4 py-20 md:px-8">
        <div class="mx-auto max-w-7xl">
          <div
            class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end"
            data-aos="fade-up"
          >
            <div class="max-w-2xl">
              <p class="mb-3 text-sm font-bold uppercase tracking-[0.3em] text-emerald-600 dark:text-emerald-300">
                Fitur utama
              </p>
              <h2 class="text-4xl font-black tracking-tight md:text-5xl">
                Dibangun untuk operasional tracer study yang rapi.
              </h2>
            </div>
            <p class="max-w-md text-slate-600 dark:text-slate-300">
              Dari pengumpulan data sampai laporan, setiap bagian dibuat fokus
              pada kejelasan informasi dan kemudahan kerja admin.
            </p>
          </div>

          <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article
              v-for="(feature, index) in features"
              :key="feature.title"
              class="group overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/78 shadow-xl shadow-slate-900/5 backdrop-blur-md md:backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-600/10 dark:border-white/10 dark:bg-white/[0.06]"
              data-aos="fade-up"
              :data-aos-delay="index * 90"
            >
              <div class="relative h-44 overflow-hidden">
                <img
                  :src="feature.image"
                  :alt="feature.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent" />
                <div class="absolute bottom-4 left-4 grid h-12 w-12 place-items-center rounded-2xl bg-white text-emerald-600 shadow-lg dark:bg-slate-950 dark:text-emerald-300">
                  <component :is="feature.icon" class="h-6 w-6" />
                </div>
              </div>
              <div class="p-6">
                <h3 class="text-xl font-black">{{ feature.title }}</h3>
                <p class="mt-3 leading-7 text-slate-600 dark:text-slate-300">
                  {{ feature.description }}
                </p>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section id="process" class="scroll-mt-32 px-4 py-20 md:px-8">
        <div
          class="mx-auto max-w-7xl rounded-[2.5rem] border border-slate-200/80 bg-white/72 p-6 shadow-2xl shadow-slate-900/5 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.05] md:p-10"
          data-aos="fade-up"
        >
          <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
            <div data-aos="fade-right" data-aos-delay="120">
              <p class="mb-3 text-sm font-bold uppercase tracking-[0.3em] text-teal-600 dark:text-teal-300">
                Alur kerja
              </p>
              <h2 class="text-4xl font-black tracking-tight md:text-5xl">
                Sederhana untuk alumni, kuat untuk institusi.
              </h2>
              <p class="mt-5 leading-8 text-slate-600 dark:text-slate-300">
                Landing page ini mengarahkan pengguna ke portal yang tepat,
                sementara proses internal tetap mudah diaudit dan siap menjadi
                dasar keputusan kampus.
              </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
              <div
                v-for="(item, index) in processSteps"
                :key="item.step"
                class="rounded-3xl border border-slate-200/80 bg-slate-50/80 p-5 dark:border-white/10 dark:bg-slate-950/50"
                data-aos="zoom-in"
                :data-aos-delay="180 + index * 100"
              >
                <div class="mb-5 flex items-center justify-between">
                  <span class="text-sm font-black text-slate-400">{{ item.step }}</span>
                  <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 dark:bg-emerald-400 dark:text-emerald-950">
                    <component :is="item.icon" class="h-5 w-5" />
                  </span>
                </div>
                <h3 class="text-lg font-black">{{ item.title }}</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                  {{ item.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="px-4 py-20 md:px-8">
        <div
          class="relative mx-auto max-w-5xl overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-emerald-950 via-slate-950 to-teal-950 px-6 py-14 text-center text-white shadow-2xl shadow-emerald-950/25 md:px-16 md:py-20"
          data-aos="zoom-in"
          data-aos-duration="760"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(52,211,153,0.3),transparent_36%),radial-gradient(circle_at_bottom_right,rgba(45,212,191,0.24),transparent_34%)]" />
          <div class="relative mx-auto max-w-3xl">
            <div class="mx-auto mb-6 grid h-14 w-14 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/20">
              <GraduationCap class="h-7 w-7" />
            </div>
            <h2 class="text-4xl font-black tracking-tight md:text-5xl">
              Siap mengelola data alumni dengan lebih profesional?
            </h2>
            <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-emerald-100">
              Akses platform tracer study dan mulai kelola survey, alumni, dan
              laporan institusi dalam satu sistem.
            </p>
            <Button
              size="lg"
              class="mt-8 rounded-2xl bg-white px-8 text-emerald-950 hover:bg-emerald-50"
              @click="navigateToCheck"
            >
              Masuk Platform
              <ArrowRight class="ml-2 h-5 w-5" />
            </Button>
          </div>
        </div>
      </section>
    </main>

    <footer
      id="contact"
      class="scroll-mt-32 border-t border-slate-200/80 bg-white/72 px-4 py-12 backdrop-blur-md md:backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/70 md:px-8"
      data-aos="fade-up"
    >
      <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
        <div>
          <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-2xl border border-emerald-200/70 bg-white p-2 shadow-sm dark:border-emerald-400/20">
              <img :src="logoImage" alt="UII Dalwa" class="h-7 w-auto object-contain" />
            </span>
            <div>
              <h3 class="font-black">Tracer Study UII Dalwa</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Alumni insight platform
              </p>
            </div>
          </div>
          <p class="mt-5 max-w-md leading-7 text-slate-600 dark:text-slate-300">
            Platform modern untuk pengelolaan tracer study, survey alumni, dan
            pelaporan institusi Universitas Islam Internasional Darullughah Wadda'wah.
          </p>
        </div>

        <div>
          <h4 class="font-black">Kontak</h4>
          <div class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
            <p class="flex gap-2">
              <ShieldCheck class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-300" />
              Universitas Islam Internasional Darullughah Wadda'wah
            </p>
            <p class="flex gap-2">
              <Target class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-300" />
              Raci, Bangil, Indonesia
            </p>
          </div>
        </div>

        <div>
          <h4 class="font-black">Akses Cepat</h4>
          <div class="mt-4 space-y-3 text-sm">
            <router-link
              to="/public/questionnaires"
              class="block text-slate-600 transition hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-300"
            >
              Kuesioner Publik
            </router-link>
            <a
              href="#features"
              class="block text-slate-600 transition hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-300"
            >
              Fitur
            </a>
            <button
              class="block text-left text-slate-600 transition hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-300"
              @click="navigateToCheck"
            >
              Login
            </button>
          </div>
        </div>
      </div>

      <div class="mx-auto mt-10 max-w-7xl border-t border-slate-200/80 pt-6 text-sm text-slate-500 dark:border-white/10 dark:text-slate-400">
        © 2026 Tracer Study UII Dalwa. All rights reserved.
      </div>
    </footer>
  </div>
</template>

<style scoped>
:global(html) {
  scroll-behavior: smooth;
}

@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

[data-aos] {
  opacity: 0;
  transition-property: opacity, transform;
  transition-duration: 720ms;
  transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
}

[data-aos="fade-up"] {
  transform: translate3d(0, 18px, 0);
}

[data-aos="fade-right"] {
  transform: translate3d(-18px, 0, 0);
}

[data-aos="fade-left"] {
  transform: translate3d(18px, 0, 0);
}

[data-aos="zoom-in"] {
  transform: scale(0.97);
}

[data-aos].aos-animate {
  opacity: 1;
  transform: translate3d(0, 0, 0) scale(1);
}

@media (prefers-reduced-motion: reduce) {
  [data-aos] {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
</style>
