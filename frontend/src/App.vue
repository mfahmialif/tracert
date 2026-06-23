<script setup lang="ts">
import { computed } from "vue";
import { RouterView } from "vue-router";
import { useTheme } from "./composables/useTheme";
import { Toaster } from "vue-sonner";
import "vue-sonner/style.css";

const { isDark } = useTheme(); // Initialize theme system and get isDark ref
const toastTheme = computed(() => (isDark.value ? "dark" : "light"));
</script>

<template>
  <div
    class="relative min-h-screen overflow-x-hidden bg-[#f8fafc] text-slate-950 transition-colors duration-300 dark:bg-[#090d14] dark:text-white"
  >
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(16,185,129,0.14),transparent_30%),radial-gradient(circle_at_82%_12%,rgba(20,184,166,0.1),transparent_28%),radial-gradient(circle_at_50%_55%,rgba(15,23,42,0.06),transparent_36%),linear-gradient(180deg,rgba(248,250,252,0.94),rgba(240,253,250,0.76))] dark:bg-[radial-gradient(circle_at_18%_18%,rgba(16,185,129,0.18),transparent_32%),radial-gradient(circle_at_82%_12%,rgba(45,212,191,0.1),transparent_30%),radial-gradient(circle_at_50%_50%,rgba(30,64,175,0.16),transparent_38%),linear-gradient(180deg,rgba(9,13,20,0.96),rgba(7,20,18,0.94))]"
      />
      <div
        class="ambient-blob ambient-blob-one absolute left-[8%] top-[18%] h-[22rem] w-[34rem] rotate-[-10deg] rounded-full bg-emerald-500/12 blur-[110px] dark:bg-emerald-400/12"
      />
      <div
        class="ambient-blob ambient-blob-two absolute right-[6%] top-[8%] h-[20rem] w-[28rem] rotate-[18deg] rounded-full bg-teal-400/10 blur-[110px] dark:bg-cyan-400/8"
      />
      <div
        class="ambient-blob ambient-blob-three absolute bottom-[8%] left-[35%] h-[18rem] w-[32rem] rounded-full bg-slate-900/7 blur-[120px] dark:bg-blue-700/12"
      />
      <div
        class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/80 to-transparent dark:from-black/40"
      />
    </div>

    <div class="relative z-10 min-h-screen">
      <RouterView v-slot="{ Component, route }">
        <Transition name="page" mode="out-in">
          <component :is="Component" :key="route.fullPath" class="route-screen" />
        </Transition>
      </RouterView>
    </div>
    <Toaster position="top-center" :theme="toastTheme" richColors />
  </div>
</template>
