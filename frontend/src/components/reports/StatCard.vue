<template>
  <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
    <div class="flex items-center gap-2 mb-1">
      <span class="text-base">{{ icon }}</span>
      <p class="text-xs text-white/40">{{ label }}</p>
    </div>
    <p class="text-xl font-bold text-white">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string; value: string | number; icon?: string }>()
</script>
