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
      expect(store.error).toBe('No se pudieron cargar los pictogramas. Inténtalo de nuevo.')

      const repo = createMockRepository()
      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBeNull()
    })

    it('sets user-friendly error on repository failure', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue(new Error('Network error')),
      })

      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBe('No se pudieron cargar los pictogramas. Inténtalo de nuevo.')
      expect(store.pictograms).toEqual([])
    })

    it('sets user-friendly error on non-Error exceptions', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        findByCategory: vi.fn().mockRejectedValue('string error'),
      })

      await store.fetchByCategory('cat-1', repo)

      expect(store.error).toBe('No se pudieron cargar los pictogramas. Inténtalo de nuevo.')
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

    it('ignores stale response when a newer request was made', async () => {
      const store = usePictogramStore()
      const staleResult: Pictogram[] = [
        { id: 'p-stale', arasaacId: 9999, categoryId: 'cat-1', label: 'stale', imagePath: '/stale.png' },
      ]
      const freshResult: Pictogram[] = [
        { id: 'p-fresh', arasaacId: 8888, categoryId: 'cat-2', label: 'fresh', imagePath: '/fresh.png' },
      ]

      let resolveStale!: (value: Pictogram[]) => void
      const slowRepo = createMockRepository({
        findByCategory: vi.fn().mockImplementation(
          () => new Promise((resolve) => { resolveStale = resolve }),
        ),
      })
      const fastRepo = createMockRepository({
        findByCategory: vi.fn().mockResolvedValue(freshResult),
      })

      // Start slow request (don't await)
      const stalePromise = store.fetchByCategory('cat-1', slowRepo)

      // Start fast request that completes first
      await store.fetchByCategory('cat-2', fastRepo)
      expect(store.pictograms).toEqual(freshResult)

      // Stale request resolves late — should be ignored
      resolveStale(staleResult)
      await stalePromise

      expect(store.pictograms).toEqual(freshResult)
      expect(store.error).toBeNull()
    })

    it('ignores stale error when a newer request succeeded', async () => {
      const store = usePictogramStore()
      const freshResult: Pictogram[] = [
        { id: 'p-fresh', arasaacId: 8888, categoryId: 'cat-2', label: 'fresh', imagePath: '/fresh.png' },
      ]

      let rejectStale!: (reason: Error) => void
      const slowRepo = createMockRepository({
        findByCategory: vi.fn().mockImplementation(
          () => new Promise((_resolve, reject) => { rejectStale = reject }),
        ),
      })
      const fastRepo = createMockRepository({
        findByCategory: vi.fn().mockResolvedValue(freshResult),
      })

      const stalePromise = store.fetchByCategory('cat-1', slowRepo)
      await store.fetchByCategory('cat-2', fastRepo)
      expect(store.pictograms).toEqual(freshResult)

      // Stale request fails late — error should be ignored
      rejectStale(new Error('stale failure'))
      await stalePromise

      expect(store.pictograms).toEqual(freshResult)
      expect(store.error).toBeNull()
    })
  })

  describe('searchPictograms', () => {
    it('loads pictograms from repository search', async () => {
      const store = usePictogramStore()
      const searchResults: Pictogram[] = [
        { id: 'p1', arasaacId: 2345, categoryId: 'cat-1', label: 'comer', imagePath: '/pictograms/2345.png' },
      ]
      const repo = createMockRepository({
        search: vi.fn().mockResolvedValue(searchResults),
      })

      await store.searchPictograms('comer', repo)

      expect(store.pictograms).toEqual(searchResults)
      expect(repo.search).toHaveBeenCalledWith('comer')
    })

    it('sets loading while searching', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        search: vi.fn().mockImplementation(
          () => new Promise((resolve) => setTimeout(() => resolve([]), 100)),
        ),
      })

      const promise = store.searchPictograms('agua', repo)

      expect(store.loading).toBe(true)

      await promise

      expect(store.loading).toBe(false)
    })

    it('sets error on search failure', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository({
        search: vi.fn().mockRejectedValue(new Error('Search failed')),
      })

      await store.searchPictograms('agua', repo)

      expect(store.error).toBe('No se pudieron cargar los resultados. Inténtalo de nuevo.')
      expect(store.pictograms).toEqual([])
    })

    it('clears pictograms when query is empty', async () => {
      const store = usePictogramStore()
      const repo = createMockRepository()
      await store.fetchByCategory('cat-1', repo)
      expect(store.pictograms).toHaveLength(3)

      await store.searchPictograms('', repo)

      expect(store.pictograms).toEqual([])
      expect(repo.search).not.toHaveBeenCalled()
    })

    it('ignores stale search response when a newer search was made', async () => {
      const store = usePictogramStore()
      const freshResult: Pictogram[] = [
        { id: 'p-fresh', arasaacId: 8888, categoryId: 'cat-2', label: 'pan', imagePath: '/pan.png' },
      ]

      let resolveStale!: (value: Pictogram[]) => void
      const slowRepo = createMockRepository({
        search: vi.fn().mockImplementation(
          () => new Promise((resolve) => { resolveStale = resolve }),
        ),
      })
      const fastRepo = createMockRepository({
        search: vi.fn().mockResolvedValue(freshResult),
      })

      const stalePromise = store.searchPictograms('agua', slowRepo)
      await store.searchPictograms('pan', fastRepo)
      expect(store.pictograms).toEqual(freshResult)

      resolveStale([{ id: 'p-stale', arasaacId: 9999, categoryId: 'cat-1', label: 'agua', imagePath: '/agua.png' }])
      await stalePromise

      expect(store.pictograms).toEqual(freshResult)
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
