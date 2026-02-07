import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import type { CategoryRepository } from '@/domain/repositories/CategoryRepository'
import type { Category } from '@/domain/entities/Category'

function createMockRepository(
  overrides: Partial<CategoryRepository> = {},
): CategoryRepository {
  return {
    findAll: vi.fn().mockResolvedValue([]),
    ...overrides,
  }
}

const categoryFixtures: Category[] = [
  { id: '1', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 2 },
  { id: '2', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
  { id: '3', name: 'Emociones', icon: 'heart', colorHex: '#3B82F6', displayOrder: 3 },
]

describe('useCategoryStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('initial state', () => {
    it('has empty categories', () => {
      const store = useCategoryStore()

      expect(store.categories).toEqual([])
    })

    it('has no selected category', () => {
      const store = useCategoryStore()

      expect(store.selectedCategoryId).toBeNull()
    })

    it('is not loading', () => {
      const store = useCategoryStore()

      expect(store.loading).toBe(false)
    })

    it('has no error', () => {
      const store = useCategoryStore()

      expect(store.error).toBeNull()
    })
  })

  describe('fetchCategories', () => {
    it('sets loading to true while fetching', async () => {
      // Arrange
      let resolvePromise!: (value: Category[]) => void
      const repository = createMockRepository({
        findAll: vi.fn().mockReturnValue(
          new Promise<Category[]>((resolve) => {
            resolvePromise = resolve
          }),
        ),
      })
      const store = useCategoryStore()

      // Act
      const promise = store.fetchCategories(repository)

      // Assert
      expect(store.loading).toBe(true)

      resolvePromise(categoryFixtures)
      await promise
    })

    it('loads categories from repository', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockResolvedValue(categoryFixtures),
      })
      const store = useCategoryStore()

      // Act
      await store.fetchCategories(repository)

      // Assert
      expect(store.categories).toEqual(categoryFixtures)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('sets error on network failure', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockRejectedValue(new Error('Network error')),
      })
      const store = useCategoryStore()

      // Act
      await store.fetchCategories(repository)

      // Assert
      expect(store.categories).toEqual([])
      expect(store.error).toBe('Network error')
      expect(store.loading).toBe(false)
    })

    it('sets generic error for non-Error exceptions', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockRejectedValue('something went wrong'),
      })
      const store = useCategoryStore()

      // Act
      await store.fetchCategories(repository)

      // Assert
      expect(store.error).toBe('Error loading categories')
    })

    it('clears previous error on new fetch', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi
          .fn()
          .mockRejectedValueOnce(new Error('fail'))
          .mockResolvedValueOnce(categoryFixtures),
      })
      const store = useCategoryStore()

      // Act - first fetch fails
      await store.fetchCategories(repository)
      expect(store.error).toBe('fail')

      // Act - second fetch succeeds
      await store.fetchCategories(repository)

      // Assert
      expect(store.error).toBeNull()
      expect(store.categories).toEqual(categoryFixtures)
    })
  })

  describe('selectCategory', () => {
    it('sets the selected category id', () => {
      const store = useCategoryStore()

      store.selectCategory('1')

      expect(store.selectedCategoryId).toBe('1')
    })

    it('can change selection', () => {
      const store = useCategoryStore()

      store.selectCategory('1')
      store.selectCategory('2')

      expect(store.selectedCategoryId).toBe('2')
    })
  })

  describe('sortedCategories', () => {
    it('returns categories sorted by displayOrder', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockResolvedValue(categoryFixtures),
      })
      const store = useCategoryStore()

      // Act
      await store.fetchCategories(repository)

      // Assert
      expect(store.sortedCategories.map((c) => c.name)).toEqual([
        'Personas',
        'Acciones',
        'Emociones',
      ])
    })

    it('returns empty array when no categories', () => {
      const store = useCategoryStore()

      expect(store.sortedCategories).toEqual([])
    })
  })

  describe('selectedCategory', () => {
    it('returns the selected category object', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockResolvedValue(categoryFixtures),
      })
      const store = useCategoryStore()
      await store.fetchCategories(repository)

      // Act
      store.selectCategory('2')

      // Assert
      expect(store.selectedCategory).toEqual(categoryFixtures[1])
    })

    it('returns null when nothing is selected', () => {
      const store = useCategoryStore()

      expect(store.selectedCategory).toBeNull()
    })

    it('returns null when selected id does not match any category', async () => {
      // Arrange
      const repository = createMockRepository({
        findAll: vi.fn().mockResolvedValue(categoryFixtures),
      })
      const store = useCategoryStore()
      await store.fetchCategories(repository)

      // Act
      store.selectCategory('nonexistent')

      // Assert
      expect(store.selectedCategory).toBeNull()
    })
  })
})
