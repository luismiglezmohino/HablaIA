import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Pictogram } from '@/domain/entities/Pictogram'
import type { PictogramRepository } from '@/domain/repositories/PictogramRepository'

export const usePictogramStore = defineStore('pictograms', () => {
  const pictograms = ref<Pictogram[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  let currentRequestId = 0

  async function fetchByCategory(categoryId: string, repository: PictogramRepository): Promise<void> {
    const requestId = ++currentRequestId
    loading.value = true
    error.value = null
    try {
      const result = await repository.findByCategory(categoryId)
      if (requestId !== currentRequestId) return
      pictograms.value = result
    } catch {
      if (requestId !== currentRequestId) return
      pictograms.value = []
      error.value = 'No se pudieron cargar los pictogramas. Inténtalo de nuevo.'
    } finally {
      if (requestId === currentRequestId) {
        loading.value = false
      }
    }
  }

  async function searchPictograms(query: string, repository: PictogramRepository): Promise<void> {
    if (!query.trim()) {
      ++currentRequestId
      pictograms.value = []
      error.value = null
      return
    }

    const requestId = ++currentRequestId
    loading.value = true
    error.value = null
    try {
      const result = await repository.search(query)
      if (requestId !== currentRequestId) return
      pictograms.value = result
    } catch {
      if (requestId !== currentRequestId) return
      pictograms.value = []
      error.value = 'No se pudieron cargar los resultados. Inténtalo de nuevo.'
    } finally {
      if (requestId === currentRequestId) {
        loading.value = false
      }
    }
  }

  function clearPictograms(): void {
    ++currentRequestId
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
