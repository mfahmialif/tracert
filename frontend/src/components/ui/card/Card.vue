<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    :class="
      cn(
        'rounded-[1.35rem] border border-white/80 bg-white/[0.78] text-card-foreground shadow-xl shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-emerald-900/10 dark:border-white/10 dark:bg-white/[0.055] dark:shadow-black/20 dark:hover:shadow-black/30',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
