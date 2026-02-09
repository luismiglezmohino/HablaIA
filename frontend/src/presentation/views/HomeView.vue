<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue'
import { Sparkles, Search, X, Users, Play, Heart, MapPin, Box, Utensils, Car, Shapes } from 'lucide-vue-next'
import type { Component } from 'vue'
import CategoryBar from '@/presentation/components/CategoryBar.vue'
import PictogramGrid from '@/presentation/components/PictogramGrid.vue'
import PhraseBar from '@/presentation/components/PhraseBar.vue'
import SearchBar from '@/presentation/components/SearchBar.vue'
import SpeakButton from '@/presentation/components/SpeakButton.vue'
import { Badge } from '@/presentation/components/ui/badge'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import { ApiClient } from '@/infrastructure/http/ApiClient'
import { HttpCategoryRepository } from '@/infrastructure/http/HttpCategoryRepository'
import { HttpPictogramRepository } from '@/infrastructure/http/HttpPictogramRepository'
import { HttpPhraseRepository } from '@/infrastructure/http/HttpPhraseRepository'
import type { Pictogram } from '@/domain/entities/Pictogram'

const apiClient = new ApiClient()
const categoryRepo = new HttpCategoryRepository(apiClient)
const pictogramRepo = new HttpPictogramRepository(apiClient)
const phraseRepo = new HttpPhraseRepository(apiClient)

const categoryIconMap: Record<string, Component> = {
  users: Users,
  play: Play,
  heart: Heart,
  'map-pin': MapPin,
  box: Box,
  utensils: Utensils,
  car: Car,
}

const categoryStore = useCategoryStore()
const pictogramStore = usePictogramStore()
const phraseStore = usePhraseStore()

const isSearching = ref(false)
const isSearchFocused = ref(false)
const searchBarRef = ref<InstanceType<typeof SearchBar> | null>(null)

const mobileQuery = ref('')
let mobileDebounceTimer: ReturnType<typeof setTimeout> | null = null

watch(mobileQuery, (value) => {
  if (mobileDebounceTimer) clearTimeout(mobileDebounceTimer)
  mobileDebounceTimer = setTimeout(() => {
    handleSearch(value)
  }, 300)
})

function clearMobileSearch() {
  mobileQuery.value = ''
  handleSearch('')
}

const statusMessage = computed(() => {
  if (isSearching.value) {
    return `Mostrando resultados de búsqueda`
  }
  if (categoryStore.selectedCategory) {
    return `Categoría ${categoryStore.selectedCategory.name} seleccionada`
  }
  return ''
})

function handlePictogramSelect(pictogram: Pictogram) {
  phraseStore.addPictogram(pictogram)
}

function handleGenerate() {
  phraseStore.generatePhrase(phraseRepo)
}

function handleSearch(query: string) {
  if (query.trim()) {
    isSearching.value = true
    categoryStore.clearSelection()
    pictogramStore.searchPictograms(query, pictogramRepo)
  } else {
    isSearching.value = false
    if (!categoryStore.selectedCategoryId) {
      pictogramStore.clearPictograms()
    }
  }
}

onMounted(() => {
  categoryStore.fetchCategories(categoryRepo)
})

watch(
  () => categoryStore.selectedCategoryId,
  (categoryId) => {
    if (categoryId) {
      isSearching.value = false
      if (mobileDebounceTimer) clearTimeout(mobileDebounceTimer)
      mobileQuery.value = ''
      searchBarRef.value?.clear()
      pictogramStore.fetchByCategory(categoryId, pictogramRepo)
    } else if (!isSearching.value) {
      pictogramStore.clearPictograms()
    }
  },
)
</script>

<template>
  <a href="#main-content" class="skip-link">Ir al contenido principal</a>

  <header class="sticky top-0 z-30 border-b border-primary-100 bg-white/95 px-4 py-2.5 shadow-soft backdrop-blur-sm sm:px-5 sm:py-3.5">
    <div class="flex items-center gap-3">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white shadow-md" aria-hidden="true">
        <Sparkles :size="22" />
      </div>
      <h1 class="hidden text-2xl font-bold tracking-tight text-accessible-text sm:block tablet-landscape-hide">
        Habla<span class="text-primary-600">IA</span>
      </h1>

      <!-- Mobile + tablet landscape search in header -->
      <div class="relative flex-1 sm:hidden tablet-landscape-show">
        <label for="mobile-search" class="sr-only">Buscar pictogramas</label>
        <Search
          :size="18"
          class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-accessible-textLight"
          aria-hidden="true"
        />
        <input
          id="mobile-search"
          v-model="mobileQuery"
          type="search"
          placeholder="Buscar pictogramas..."
          class="min-h-touch w-full rounded-xl border-2 border-surface-200 bg-surface-50 py-2 pl-10 pr-10 text-sm shadow-soft transition-all placeholder:text-surface-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:shadow-card"
        />
        <button
          v-if="mobileQuery"
          aria-label="Borrar búsqueda"
          class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-surface-400 hover:text-accessible-textLight"
          @click="clearMobileSearch"
        >
          <X :size="18" aria-hidden="true" />
        </button>
      </div>

      <!-- Compact category icons — tablet landscape only -->
      <nav v-if="categoryStore.sortedCategories.length" aria-label="Categorías" class="hidden tablet-landscape-show">
        <div class="flex items-center gap-1.5 overflow-x-auto">
          <button
            v-for="category in categoryStore.sortedCategories"
            :key="category.id"
            :aria-label="`Categoría ${category.name}`"
            :aria-pressed="categoryStore.selectedCategoryId === category.id ? 'true' : 'false'"
            :style="{ borderColor: category.colorHex, backgroundColor: categoryStore.selectedCategoryId === category.id ? category.colorHex + '18' : undefined }"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border-2 bg-white"
            :class="categoryStore.selectedCategoryId === category.id ? 'ring-2 ring-primary-500 ring-offset-1' : ''"
            @click="categoryStore.selectCategory(category.id)"
          >
            <component
              :is="(category.icon && categoryIconMap[category.icon]) || Shapes"
              :size="14"
              :style="{ color: category.colorHex }"
              aria-hidden="true"
            />
          </button>
        </div>
      </nav>
    </div>
  </header>

  <div
    :class="isSearchFocused ? 'z-20' : 'sticky top-[52px] z-20 max-h-[40vh] overflow-y-auto sm:max-h-none sm:overflow-visible sm:top-[56px]'"
  >
    <PhraseBar @generate="handleGenerate" />
    <div class="hidden sm:block tablet-landscape-hide">
      <SearchBar ref="searchBarRef" @search="handleSearch" @focus="isSearchFocused = true" @blur="isSearchFocused = false" />
    </div>
  </div>

  <div class="tablet-landscape-hide">
    <CategoryBar />
  </div>

  <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    {{ statusMessage }}
  </div>

  <!-- Phrase results — mobile only (outside sticky for more space) -->
  <section v-if="phraseStore.phraseResponse" aria-label="Frases generadas" class="mx-auto max-w-7xl px-3 pt-4 sm:hidden">
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <Badge class="bg-accent-100 text-accent-800 border-transparent">
          {{ phraseStore.phraseResponse.source }}
        </Badge>
      </div>
      <ul class="space-y-2" role="list">
        <li
          v-for="(variation, index) in phraseStore.phraseResponse.variations"
          :key="index"
          class="flex items-center justify-between gap-3 rounded-xl border border-surface-200 bg-white px-4 py-3 shadow-card motion-safe:hover:shadow-card-hover"
        >
          <span class="text-accessible-text">{{ variation }}</span>
          <SpeakButton :data-testid="`speak-btn-${index}`" :text="variation" />
        </li>
      </ul>
    </div>
  </section>

  <main id="main-content" class="mx-auto max-w-7xl pb-8">
    <PictogramGrid @select="handlePictogramSelect" />
  </main>
</template>
