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
    } catch {
      pictograms.value = []
      error.value = 'No se pudieron cargar los pictogramas. Inténtalo de nuevo.'
    } finally {
      loading.value = false
    }
  }

  async function searchPictograms(query: string, repository: PictogramRepository): Promise<void> {
    if (!query.trim()) {
      pictograms.value = []
      error.value = null
      return
    }

    loading.value = true
    error.value = null
    try {
      pictograms.value = await repository.search(query)
    } catch {
      pictograms.value = []
      error.value = 'No se pudieron cargar los resultados. Inténtalo de nuevo.'
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
    searchPictograms,
    clearPictograms,
  }
})
