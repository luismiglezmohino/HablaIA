import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Pictogram } from '@/domain/entities/Pictogram'
import type { PictogramRepository } from '@/domain/repositories/PictogramRepository'

export const usePictogramStore = defineStore('pictograms', () => {
  const pictograms = ref<Pictogram[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchByCategory(categoryId: string, repository: PictogramRepository): Promise<void> {
    loading.value = true
    error.value = null
    try {
      pictograms.value = await repository.findByCategory(categoryId)
    } catch (e) {
      pictograms.value = []
      error.value = e instanceof Error ? e.message : 'Error loading pictograms'
    } finally {
      loading.value = false
    }
  }

  function clearPictograms(): void {
    pictograms.value = []
    error.value = null
  }

  return {
    pictograms,
    loading,
    error,
    fetchByCategory,
    clearPictograms,
  }
})
