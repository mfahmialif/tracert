<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <th :class="cn('h-12 bg-emerald-50/70 px-4 text-left align-middle text-xs font-black uppercase tracking-[0.14em] text-emerald-900/70 first:rounded-l-2xl last:rounded-r-2xl dark:bg-emerald-400/10 dark:text-emerald-100/70 [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-0.5', props.class)">
    <slot />
  </th>
</template>
