<script setup lang="ts">
import { computed } from 'vue'
import CategoryBar from '@/presentation/components/CategoryBar.vue'
import { useCategoryStore } from '@/application/stores/useCategoryStore'

const store = useCategoryStore()

const statusMessage = computed(() => {
  if (store.selectedCategory) {
    return `Categoría ${store.selectedCategory.name} seleccionada`
  }
  return ''
})
</script>

<template>
  <a href="#main-content" class="skip-link">Ir al contenido principal</a>

  <header class="border-b border-gray-200 bg-white px-4 py-3">
    <h1 class="text-xl font-bold text-accessible-text">HablaIA</h1>
  </header>

  <CategoryBar />

  <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    {{ statusMessage }}
  </div>

  <main id="main-content" class="container mx-auto px-4 py-6">
    <p class="text-center text-accessible-textLight">
      Selecciona una categoría para ver los pictogramas
    </p>
  </main>
</template>
