<script setup lang="ts">
import { RouterView } from "vue-router";
import { useTheme } from "./composables/useTheme";
import { Toaster } from "vue-sonner";
import "vue-sonner/style.css";

useTheme(); // Initialize theme system
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 transition-colors duration-300"
  >
    <RouterView />
    <Toaster position="top-center" theme="light" richColors />
  </div>
</template>
