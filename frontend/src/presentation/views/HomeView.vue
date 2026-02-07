<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import CategoryBar from '@/presentation/components/CategoryBar.vue'
import PictogramGrid from '@/presentation/components/PictogramGrid.vue'
import PhraseBar from '@/presentation/components/PhraseBar.vue'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import { ApiClient } from '@/infrastructure/http/ApiClient'
import { HttpCategoryRepository } from '@/infrastructure/http/HttpCategoryRepository'
import { HttpPictogramRepository } from '@/infrastructure/http/HttpPictogramRepository'
import { HttpPhraseRepository } from '@/infrastructure/http/HttpPhraseRepository'
import type { Pictogram } from '@/domain/entities/Pictogram'

const apiClient = new ApiClient()
const categoryRepo = new HttpCategoryRepository(apiClient)
const pictogramRepo = new HttpPictogramRepository(apiClient)
const phraseRepo = new HttpPhraseRepository(apiClient)

const categoryStore = useCategoryStore()
const pictogramStore = usePictogramStore()
const phraseStore = usePhraseStore()

const statusMessage = computed(() => {
  if (categoryStore.selectedCategory) {
    return `Categoría ${categoryStore.selectedCategory.name} seleccionada`
  }
  return ''
})

function handlePictogramSelect(pictogram: Pictogram) {
  phraseStore.addPictogram(pictogram)
}

function handleGenerate() {
  phraseStore.generatePhrase(phraseRepo)
}

onMounted(() => {
  categoryStore.fetchCategories(categoryRepo)
})

watch(
  () => categoryStore.selectedCategoryId,
  (categoryId) => {
    if (categoryId) {
      pictogramStore.fetchByCategory(categoryId, pictogramRepo)
    } else {
      pictogramStore.clearPictograms()
    }
  },
)
</script>

<template>
  <a href="#main-content" class="skip-link">Ir al contenido principal</a>

  <header class="border-b border-gray-200 bg-white px-4 py-3">
    <h1 class="text-xl font-bold text-accessible-text">HablaIA</h1>
  </header>

  <PhraseBar @generate="handleGenerate" />

  <CategoryBar />

  <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    {{ statusMessage }}
  </div>

  <main id="main-content" class="container mx-auto">
    <PictogramGrid @select="handlePictogramSelect" />
  </main>
</template>
