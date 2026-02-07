<script setup lang="ts">
import PictogramCard from '@/presentation/components/PictogramCard.vue'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import type { Pictogram } from '@/domain/entities/Pictogram'

const pictogramStore = usePictogramStore()
const categoryStore = useCategoryStore()
const phraseStore = usePhraseStore()

const emit = defineEmits<{
  select: [pictogram: Pictogram]
}>()
</script>

<template>
  <div v-if="!categoryStore.selectedCategoryId" class="flex items-center justify-center p-8">
    <p class="text-accessible-textLight">Selecciona una categoría para ver los pictogramas</p>
  </div>

  <div v-else-if="pictogramStore.loading" role="status" class="flex items-center justify-center p-8">
    <span class="text-accessible-textLight">Cargando pictogramas...</span>
  </div>

  <div v-else-if="pictogramStore.error" role="alert" class="flex items-center justify-center gap-2 p-8">
    <span class="text-red-600">{{ pictogramStore.error }}</span>
  </div>

  <div v-else-if="pictogramStore.pictograms.length === 0" class="flex items-center justify-center p-8">
    <p class="text-accessible-textLight">No hay pictogramas en esta categoría</p>
  </div>

  <div
    v-else
    role="grid"
    aria-label="Pictogramas"
    class="grid grid-cols-3 gap-4 p-4 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6"
  >
    <PictogramCard
      v-for="pictogram in pictogramStore.pictograms"
      :key="pictogram.id"
      :pictogram="pictogram"
      :category-color="categoryStore.selectedCategory?.colorHex ?? '#6B7280'"
      :disabled="phraseStore.isFull"
      @select="emit('select', $event)"
    />
  </div>
</template>
