import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import type { PhraseRepository } from '@/domain/repositories/PhraseRepository'
import type { PhraseResponse } from '@/domain/entities/PhraseResponse'
import type { Pictogram } from '@/domain/entities/Pictogram'

const phraseFixture: PhraseResponse = {
  variations: ['Quiero comer pan', 'Me gustaría comer pan', 'Deseo comer pan'],
  source: 'generated',
  sequenceHash: 'abc123',
  pictogramIds: ['p1', 'p2'],
}

const pictogramFixtures: Pictogram[] = [
  { id: 'p1', arasaacId: 2345, categoryId: 'cat-1', label: 'comer', imagePath: '/pictograms/2345.png' },
  { id: 'p2', arasaacId: 3456, categoryId: 'cat-1', label: 'pan', imagePath: '/pictograms/3456.png' },
  { id: 'p3', arasaacId: 4567, categoryId: 'cat-1', label: 'agua', imagePath: '/pictograms/4567.png' },
]

function createMockRepository(overrides: Partial<PhraseRepository> = {}): PhraseRepository {
  return {
    generate: vi.fn().mockResolvedValue(phraseFixture),
    ...overrides,
  }
}

describe('usePhraseStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('initial state', () => {
    it('starts with empty selected pictograms', () => {
      const store = usePhraseStore()

      expect(store.selectedPictograms).toEqual([])
    })

    it('starts with no phrase response', () => {
      const store = usePhraseStore()

      expect(store.phraseResponse).toBeNull()
    })

    it('starts with loading false', () => {
      const store = usePhraseStore()

      expect(store.loading).toBe(false)
    })

    it('starts with no error', () => {
      const store = usePhraseStore()

      expect(store.error).toBeNull()
    })
  })

  describe('addPictogram', () => {
    it('adds a pictogram to the selection', () => {
      const store = usePhraseStore()

      store.addPictogram(pictogramFixtures[0]!)

      expect(store.selectedPictograms).toHaveLength(1)
      expect(store.selectedPictograms[0]?.id).toBe('p1')
    })

    it('adds multiple pictograms in order', () => {
      const store = usePhraseStore()

      store.addPictogram(pictogramFixtures[0]!)
      store.addPictogram(pictogramFixtures[1]!)

      expect(store.selectedPictograms).toHaveLength(2)
      expect(store.selectedPictograms[0]?.label).toBe('comer')
      expect(store.selectedPictograms[1]?.label).toBe('pan')
    })

    it('does not exceed 10 pictograms', () => {
      const store = usePhraseStore()

      for (let i = 0; i < 12; i++) {
        store.addPictogram({
          id: `p${i}`,
          arasaacId: i,
          categoryId: 'cat-1',
          label: `picto-${i}`,
          imagePath: `/pictograms/${i}.png`,
        })
      }

      expect(store.selectedPictograms).toHaveLength(10)
    })

    it('allows adding the same pictogram twice', () => {
      const store = usePhraseStore()

      store.addPictogram(pictogramFixtures[0]!)
      store.addPictogram(pictogramFixtures[0]!)

      expect(store.selectedPictograms).toHaveLength(2)
    })
  })

  describe('removePictogram', () => {
    it('removes a pictogram by index', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.addPictogram(pictogramFixtures[1]!)

      store.removePictogram(0)

      expect(store.selectedPictograms).toHaveLength(1)
      expect(store.selectedPictograms[0]?.label).toBe('pan')
    })

    it('does nothing for invalid index', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)

      store.removePictogram(5)

      expect(store.selectedPictograms).toHaveLength(1)
    })
  })

  describe('clearSelection', () => {
    it('removes all selected pictograms', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.addPictogram(pictogramFixtures[1]!)

      store.clearSelection()

      expect(store.selectedPictograms).toEqual([])
    })

    it('also clears phrase response and error', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)

      store.clearSelection()

      expect(store.phraseResponse).toBeNull()
      expect(store.error).toBeNull()
    })
  })

  describe('canGenerate', () => {
    it('returns false when no pictograms selected', () => {
      const store = usePhraseStore()

      expect(store.canGenerate).toBe(false)
    })

    it('returns true when 1-10 pictograms selected', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)

      expect(store.canGenerate).toBe(true)
    })

    it('returns false when loading', () => {
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.loading = true

      expect(store.canGenerate).toBe(false)
    })
  })

  describe('generatePhrase', () => {
    it('sends selected pictogram IDs to repository', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository()
      store.addPictogram(pictogramFixtures[0]!)
      store.addPictogram(pictogramFixtures[1]!)

      await store.generatePhrase(repo)

      expect(repo.generate).toHaveBeenCalledWith(['p1', 'p2'])
    })

    it('stores the phrase response', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository()
      store.addPictogram(pictogramFixtures[0]!)

      await store.generatePhrase(repo)

      expect(store.phraseResponse).toEqual(phraseFixture)
    })

    it('sets loading while generating', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository({
        generate: vi.fn().mockImplementation(
          () => new Promise((resolve) => setTimeout(() => resolve(phraseFixture), 100)),
        ),
      })
      store.addPictogram(pictogramFixtures[0]!)

      const promise = store.generatePhrase(repo)

      expect(store.loading).toBe(true)

      await promise

      expect(store.loading).toBe(false)
    })

    it('sets error on failure', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository({
        generate: vi.fn().mockRejectedValue(new Error('Network error')),
      })
      store.addPictogram(pictogramFixtures[0]!)

      await store.generatePhrase(repo)

      expect(store.error).toBe('Network error')
      expect(store.phraseResponse).toBeNull()
    })

    it('handles non-Error exceptions', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository({
        generate: vi.fn().mockRejectedValue('string error'),
      })
      store.addPictogram(pictogramFixtures[0]!)

      await store.generatePhrase(repo)

      expect(store.error).toBe('Error generating phrase')
    })

    it('clears previous error on new generation', async () => {
      const store = usePhraseStore()
      const failingRepo = createMockRepository({
        generate: vi.fn().mockRejectedValue(new Error('fail')),
      })
      store.addPictogram(pictogramFixtures[0]!)
      await store.generatePhrase(failingRepo)

      const repo = createMockRepository()
      await store.generatePhrase(repo)

      expect(store.error).toBeNull()
    })

    it('does nothing when no pictograms selected', async () => {
      const store = usePhraseStore()
      const repo = createMockRepository()

      await store.generatePhrase(repo)

      expect(repo.generate).not.toHaveBeenCalled()
      expect(store.phraseResponse).toBeNull()
    })
  })
})
