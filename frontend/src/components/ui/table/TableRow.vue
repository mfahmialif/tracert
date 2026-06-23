<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <tr :class="cn('border-b border-slate-200/70 transition-colors hover:bg-emerald-50/70 data-[state=selected]:bg-emerald-50 dark:border-white/10 dark:hover:bg-white/[0.07] dark:data-[state=selected]:bg-white/10', props.class)">
    <slot />
  </tr>
</template>
