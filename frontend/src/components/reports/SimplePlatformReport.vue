<template>
  <div class="flex flex-col items-center justify-center py-20 px-6 text-center gap-4">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.25);">
      <svg class="w-7 h-7 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
      </svg>
    </div>
    <div>
      <p class="text-base font-semibold text-white">{{ data?.name }}</p>
      <p v-if="data?.note" class="text-sm text-white/45 mt-1 max-w-sm mx-auto">{{ data.note }}</p>
      <p v-else class="text-sm text-white/45 mt-1">Disponível nesta plataforma.</p>
    </div>
    <a
      v-if="data?.url"
      :href="data.url"
      target="_blank"
      class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium transition-all"
      style="background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.3);color:#a78bfa;"
    >
      Ver página
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
    </a>
  </div>
</template>

<script setup lang="ts">
defineProps<{ data: any }>()
</script>
