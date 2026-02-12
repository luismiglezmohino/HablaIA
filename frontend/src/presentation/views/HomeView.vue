<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
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
const mobileSearchRef = ref<HTMLInputElement | null>(null)
const mobileResultsRef = ref<HTMLElement | null>(null)
const actionAnnouncement = ref('')
const pendingGridFocus = ref(false)

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
  if (isSearching.value && !pictogramStore.loading) {
    const count = pictogramStore.pictograms.length
    if (count === 0) return 'Sin resultados de búsqueda'
    return `${count} pictograma${count !== 1 ? 's' : ''} encontrado${count !== 1 ? 's' : ''}`
  }
  if (categoryStore.selectedCategory) {
    return `Categoría ${categoryStore.selectedCategory.name} seleccionada`
  }
  return ''
})

function handlePictogramSelect(pictogram: Pictogram) {
  phraseStore.addPictogram(pictogram)
  actionAnnouncement.value = `Pictograma ${pictogram.label} añadido a la frase`
}

function handleGenerate() {
  phraseStore.generatePhrase(phraseRepo)
}

function handleMobileEscape() {
  if (mobileQuery.value) {
    clearMobileSearch()
  } else {
    mobileSearchRef.value?.blur()
  }
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

watch(
  () => phraseStore.selectedPictograms.length,
  (newLen, oldLen) => {
    if (oldLen !== undefined && newLen < oldLen) {
      actionAnnouncement.value = 'Pictograma eliminado de la frase'
    }
  },
)

watch(
  () => phraseStore.phraseResponse,
  (newVal) => {
    if (newVal && !phraseStore.loading) {
      const count = newVal.variations.length
      actionAnnouncement.value = `Frase generada con ${count} ${count !== 1 ? 'variaciones' : 'variación'}`
      nextTick(() => mobileResultsRef.value?.focus())
    }
  },
)

function selectCategoryByShortcut(index: number) {
  const categories = categoryStore.sortedCategories
  if (index < categories.length) {
    pendingGridFocus.value = true
    categoryStore.selectCategory(categories[index]!.id)
  }
}

function handleCategoryClick() {
  pendingGridFocus.value = true
}

function handleGlobalKeydown(event: KeyboardEvent) {
  const isInInput = event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement

  if (event.key === '/' && !isInInput) {
    event.preventDefault()
    const searchInput = document.getElementById('search-pictograms') as HTMLInputElement | null
    searchInput?.focus()
    return
  }

  if (!isInInput && /^[0-9]$/.test(event.key)) {
    event.preventDefault()
    selectCategoryByShortcut(event.key === '0' ? 9 : parseInt(event.key) - 1)
    return
  }

  if (!isInInput && event.key === '?') {
    event.preventDefault()
    selectCategoryByShortcut(10)
    return
  }

  if (!isInInput && event.key === 'Backspace' && phraseStore.selectedPictograms.length > 0) {
    event.preventDefault()
    const chips = document.querySelectorAll<HTMLElement>('[data-testid="remove-chip"]')
    chips[chips.length - 1]?.focus()
  }
}

onMounted(() => {
  categoryStore.fetchCategories(categoryRepo)
  document.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalKeydown)
})

watch(
  () => pictogramStore.loading,
  (loading, wasLoading) => {
    if (wasLoading && !loading && pictogramStore.pictograms.length > 0 && pendingGridFocus.value) {
      pendingGridFocus.value = false
      nextTick(() => {
        const firstButton = document.querySelector('#main-content [role="grid"] button') as HTMLElement | null
        firstButton?.focus()
      })
    }
  },
)

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
      <h1 class="sr-only sm:not-sr-only sm:block tablet-landscape-hide text-2xl font-bold tracking-tight text-accessible-text">
        Habla<span class="text-primary-600">IA</span>
      </h1>

      <!-- Mobile + tablet landscape search in header -->
      <div class="relative flex-1 sm:hidden tablet-landscape-show">
        <Search
          :size="18"
          class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-accessible-textLight"
          aria-hidden="true"
        />
        <input
          ref="mobileSearchRef"
          id="mobile-search"
          v-model="mobileQuery"
          type="search"
          placeholder="Buscar pictogramas..."
          autocomplete="off"
          class="min-h-touch w-full rounded-xl border-2 border-surface-200 bg-surface-50 py-2 pl-10 pr-10 text-sm shadow-soft transition-all placeholder:text-surface-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:shadow-card"
          @keydown.escape="handleMobileEscape"
        />
        <button
          v-if="mobileQuery"
          aria-label="Borrar búsqueda"
          class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-full p-2 text-surface-400 hover:text-accessible-textLight focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
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
            @click="pendingGridFocus = true; categoryStore.selectCategory(category.id)"
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
    <div class="tablet-landscape-hide">
      <CategoryBar @select="handleCategoryClick" />
    </div>
  </div>

  <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    {{ statusMessage }}
  </div>
  <div role="status" aria-live="assertive" aria-atomic="true" class="sr-only">
    {{ actionAnnouncement }}
  </div>

  <!-- Phrase results — mobile only (outside sticky for more space) -->
  <section v-if="phraseStore.phraseResponse" aria-label="Frases generadas" class="mx-auto max-w-7xl px-3 pt-4 sm:hidden">
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <Badge class="bg-accent-100 text-accent-800 border-transparent">
          {{ phraseStore.phraseResponse.source }}
        </Badge>
      </div>
      <ul ref="mobileResultsRef" class="space-y-2" role="list" tabindex="-1">
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

  <main id="main-content" class="mx-auto max-w-7xl pb-8 xl:pb-14">
    <PictogramGrid @select="handlePictogramSelect" />
  </main>

  <!-- Keyboard shortcuts footer — desktop only -->
  <footer class="hidden xl:fixed xl:bottom-0 xl:left-0 xl:right-0 xl:z-20 xl:block border-t border-surface-100 bg-surface-50 px-4 py-2" aria-label="Atajos de teclado">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-accessible-textLight">
      <span><kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">1</kbd>–<kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">0</kbd> <kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">?</kbd> Categorías</span>
      <span><kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">/</kbd> Buscar</span>
      <span><kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">Esc</kbd> Cerrar búsqueda</span>
      <span><kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">←</kbd> <kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">→</kbd> <kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">↑</kbd> <kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">↓</kbd> Mover en pictogramas (con teclado)</span>
      <span><kbd class="rounded border border-surface-200 bg-white px-1.5 py-0.5 font-mono text-xs shadow-sm">⌫</kbd> Ir a selección</span>
    </div>
  </footer>
</template>
