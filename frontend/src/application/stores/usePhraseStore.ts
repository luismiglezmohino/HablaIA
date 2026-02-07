import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Pictogram } from '@/domain/entities/Pictogram'
import type { PhraseResponse } from '@/domain/entities/PhraseResponse'
import type { PhraseRepository } from '@/domain/repositories/PhraseRepository'

const MAX_PICTOGRAMS = 10

export const usePhraseStore = defineStore('phrases', () => {
  const selectedPictograms = ref<Pictogram[]>([])
  const phraseResponse = ref<PhraseResponse | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const canGenerate = computed(
    () => selectedPictograms.value.length > 0 && !loading.value,
  )

  function addPictogram(pictogram: Pictogram): void {
    if (selectedPictograms.value.length >= MAX_PICTOGRAMS) return
    selectedPictograms.value.push(pictogram)
  }

  function removePictogram(index: number): void {
    if (index < 0 || index >= selectedPictograms.value.length) return
    selectedPictograms.value.splice(index, 1)
  }

  function clearSelection(): void {
    selectedPictograms.value = []
    phraseResponse.value = null
    error.value = null
  }

  async function generatePhrase(repository: PhraseRepository): Promise<void> {
    if (selectedPictograms.value.length === 0) return

    loading.value = true
    error.value = null
    try {
      const ids = selectedPictograms.value.map((p) => p.id)
      phraseResponse.value = await repository.generate(ids)
    } catch (e) {
      phraseResponse.value = null
      error.value = e instanceof Error ? e.message : 'Error generating phrase'
    } finally {
      loading.value = false
    }
  }

  return {
    selectedPictograms,
    phraseResponse,
    loading,
    error,
    canGenerate,
    addPictogram,
    removePictogram,
    clearSelection,
    generatePhrase,
  }
})
