import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import type { PictogramRepository } from '@/domain/repositories/PictogramRepository'
import type { Pictogram } from '@/domain/entities/Pictogram'

const pictogramFixtures: Pictogram[] = [
  { id: 'p1', arasaacId: 2345, categoryId: 'cat-1', label: 'comer', imagePath: '/pictograms/2345.png' },
  { id: 'p2', arasaacId: 3456, categoryId: 'cat-1', label: 'beber', imagePath: '/pictograms/3456.png' },
  { id: 'p3', arasaacId: 4567, categoryId: 'cat-1', label: 'dormir', imagePath: '/pictograms/4567.png' },
]

function createMockRepository(overrides: Partial<PictogramRepository> = {}): PictogramRepository {
  return {
    findByCategory: vi.fn().mockResolvedValue(pictogramFixtures),
    search: vi.fn().mockResolvedValue([]),
    ...overrides,
  }
}

describe('usePictogramStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('initial state', () => {
    it('starts with empty pictograms', () => {
      const store = usePictogramStore()

      expect(store.pictograms).toEqual([])
    })

    it('starts with loading false', () => {
      const store = usePictogramStore()

      expect(store.loading).toBe(false)
    })

    it('starts with no error', () => {
      const store = usePictogramStore()

      expect(store.error).toBeNull()
    })
  })

  describe('fetchByCategory', () => {
    it('loads pictograms from repository', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository()

      await store.fetchByCategory('cat-1', repo)

      expect(store.pictograms).toEqual(pictogramFixtures)
      expect(repo.findByCategory).toHaveBeenCalledWith('cat-1')
    })

    it('sets loading to true while fetching', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        findByCategory: vi.fn().mockImplementation(
          () => new Promise((resolve) => setTimeout(() => resolve(pictogramFixtures), 100)),
        ),
      })

      const promise = store.fetchByCategory('cat-1', repo)

      expect(store.loading).toBe(true)

      await promise

      expect(store.loading).toBe(false)
    })

    it('clears previous error on new fetch', async () => {
      const store = usePictogramStore()
      const failingRepo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue(new Error('Network error')),
      })

      await store.fetchByCategory('cat-1', failingRepo)
      expect(store.error).toBe('Network error')

      const repo = createMockRepository()
      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBeNull()
    })

    it('sets error on repository failure', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue(new Error('Network error')),
      })

      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBe('Network error')
      expect(store.pictograms).toEqual([])
    })

    it('handles non-Error exceptions', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue('string error'),
      })

      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBe('Error loading pictograms')
    })

    it('replaces previous pictograms on new fetch', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository()

      await store.fetchByCategory('cat-1', repo)
      expect(store.pictograms).toHaveLength(3)

      const newPictograms: Pictogram[] = [
        { id: 'p4', arasaacId: 5678, categoryId: 'cat-2', label: 'casa', imagePath: '/pictograms/5678.png' },
      ]
      const repo2 = createMockRepository({
        findByCategory: vi.fn().mockResolvedValue(newPictograms),
      })

      await store.fetchByCategory('cat-2', repo2)

      expect(store.pictograms).toEqual(newPictograms)
    })

    it('clears pictograms on error', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository()
      await store.fetchByCategory('cat-1', repo)
      expect(store.pictograms).toHaveLength(3)

      const failingRepo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue(new Error('fail')),
      })

      await store.fetchByCategory('cat-2', failingRepo)

      expect(store.pictograms).toEqual([])
    })
  })

  describe('clearPictograms', () => {
    it('clears pictograms and error', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository()
      await store.fetchByCategory('cat-1', repo)

      store.clearPictograms()

      expect(store.pictograms).toEqual([])
      expect(store.error).toBeNull()
    })
  })
})
