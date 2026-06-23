<script setup lang="ts">
withDefaults(
  defineProps<{
    message?: string;
  }>(),
  {
    message: "Sedang menyiapkan halaman...",
  }
);

const logoImage = "/logo_uiidalwa.png";
</script>

<template>
  <div
    class="fixed inset-0 z-[100] grid min-h-screen place-items-center overflow-hidden bg-slate-50/95 px-6 backdrop-blur-xl dark:bg-slate-950/95"
    role="status"
    aria-live="polite"
    aria-busy="true"
  >
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_75%_70%,rgba(20,184,166,0.14),transparent_32%)] dark:bg-[radial-gradient(circle_at_30%_20%,rgba(16,185,129,0.16),transparent_34%),radial-gradient(circle_at_75%_70%,rgba(45,212,191,0.1),transparent_32%)]"
      />
    </div>

    <div class="relative w-full max-w-sm text-center">
      <div
        class="mx-auto mb-6 grid h-20 w-20 place-items-center rounded-[1.75rem] border border-emerald-200/80 bg-white p-4 shadow-2xl shadow-emerald-900/15 dark:border-emerald-400/20 dark:bg-white/95"
      >
        <img :src="logoImage" alt="UII Dalwa" class="h-12 w-auto object-contain" />
      </div>

      <h1 class="text-xl font-black tracking-tight text-slate-950 dark:text-white">
        Tracer Study UII Dalwa
      </h1>
      <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">
        {{ message }}
      </p>

      <div
        class="mt-7 h-1.5 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-950"
        aria-hidden="true"
      >
        <div class="public-loader-progress h-full w-2/5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" />
      </div>

      <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500" />
        Mengambil data terbaru
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes public-loader-slide {
  from {
    transform: translateX(-120%);
  }
  to {
    transform: translateX(350%);
  }
}

.public-loader-progress {
  animation: public-loader-slide 1.25s ease-in-out infinite;
}
</style>
