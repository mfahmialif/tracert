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
    class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 transition-colors duration-300"
  >
    <RouterView />
    <Toaster position="top-center" :theme="toastTheme" richColors />
  </div>
</template>
