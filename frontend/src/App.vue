<script setup lang="ts">
import { RouterView } from 'vue-router'
import { Smartphone, RotateCcw } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-surface-100 text-accessible-text antialiased">
    <RouterView />
  </div>

  <!-- Landscape blocker for small screens (mobile phones) -->
  <div class="landscape-blocker" role="alert">
    <div class="flex flex-col items-center gap-5 text-center">
      <div class="relative" aria-hidden="true">
        <Smartphone :size="56" class="text-primary-600" />
        <RotateCcw :size="22" class="absolute -right-5 top-1/2 -translate-y-1/2 text-primary-400" />
      </div>
      <p class="text-lg font-semibold text-accessible-text">Gira tu dispositivo</p>
      <p class="text-sm text-accessible-textLight">Esta aplicación funciona mejor en modo vertical</p>
    </div>
  </div>
</template>
