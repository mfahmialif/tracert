<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

const props = defineProps<{
  initials: string;
}>();
</script>

<template>
  <Avatar class="h-8 w-8 cursor-pointer">
    <AvatarImage src="" />
    <AvatarFallback
      class="bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
    >
      {{ initials }}
    </AvatarFallback>
  </Avatar>
</template>
