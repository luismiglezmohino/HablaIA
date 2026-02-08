import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Category } from '@/domain/entities/Category'
import type { CategoryRepository } from '@/domain/repositories/CategoryRepository'

export const useCategoryStore = defineStore('categories', () => {
  const categories = ref<Category[]>([])
  const selectedCategoryId = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const sortedCategories = computed(() =>
    [...categories.value].sort((a, b) => a.displayOrder - b.displayOrder),
  )

  const selectedCategory = computed(
    () => categories.value.find((c) => c.id === selectedCategoryId.value) ?? null,
  )

  async function fetchCategories(repository: CategoryRepository): Promise<void> {
    loading.value = true
    error.value = null
    try {
      categories.value = await repository.findAll()
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error loading categories'
    } finally {
      loading.value = false
    }
  }

  function selectCategory(id: string): void {
    selectedCategoryId.value = id
  }

  function clearSelection(): void {
    selectedCategoryId.value = null
  }

  return {
    categories,
    selectedCategoryId,
    loading,
    error,
    sortedCategories,
    selectedCategory,
    fetchCategories,
    selectCategory,
    clearSelection,
  }
})
