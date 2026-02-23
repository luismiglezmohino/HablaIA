<script setup lang="ts">
import PictogramCard from '@/presentation/components/PictogramCard.vue'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import type { Pictogram } from '@/domain/entities/Pictogram'

const DEFAULT_COLOR = '#9CA3AF'

const pictogramStore = usePictogramStore()
const categoryStore = useCategoryStore()
const phraseStore = usePhraseStore()

const emit = defineEmits<{
  select: [pictogram: Pictogram]
}>()

function getCategoryColor(pictogram: Pictogram): string {
  const category = categoryStore.categories.find((c) => c.id === pictogram.categoryId)
  return category?.colorHex ?? DEFAULT_COLOR
}

function getColumnCount(grid: HTMLElement): number {
  const children = Array.from(grid.children) as HTMLElement[]
  if (children.length < 2) return 1
  const firstTop = children[0]!.offsetTop
  for (let i = 1; i < children.length; i++) {
    if (children[i]!.offsetTop !== firstTop) return i
  }
  return children.length
}

const SCROLL_MARGIN = 12

function ensureCardVisible(card: HTMLElement) {
  const rect = card.getBoundingClientRect()

  const header = document.querySelector('header')
  const stickyBottom = header?.nextElementSibling?.getBoundingClientRect().bottom
    ?? header?.getBoundingClientRect().bottom
    ?? 0

  const footerRect = document.querySelector('footer')?.getBoundingClientRect()
  const footerTop = (footerRect && footerRect.height > 0) ? footerRect.top : window.innerHeight

  if (rect.top < stickyBottom + SCROLL_MARGIN) {
    window.scrollBy({ top: rect.top - stickyBottom - SCROLL_MARGIN, behavior: 'instant' })
  } else if (rect.bottom + SCROLL_MARGIN > footerTop) {
    window.scrollBy({ top: rect.bottom + SCROLL_MARGIN - footerTop, behavior: 'instant' })
  }
}

function handleGridKeydown(event: KeyboardEvent) {
  const grid = event.currentTarget as HTMLElement
  const buttons = Array.from(grid.querySelectorAll<HTMLButtonElement>('button'))
  const current = document.activeElement as HTMLButtonElement
  const index = buttons.indexOf(current)
  if (index === -1) return

  const cols = getColumnCount(grid)
  let next = index

  switch (event.key) {
    case 'ArrowRight': next = Math.min(index + 1, buttons.length - 1); break
    case 'ArrowLeft': next = Math.max(index - 1, 0); break
    case 'ArrowDown': next = Math.min(index + cols, buttons.length - 1); break
    case 'ArrowUp': next = Math.max(index - cols, 0); break
    default: return
  }

  event.preventDefault()
  buttons[next]?.focus({ preventScroll: true })
  requestAnimationFrame(() => {
    const card = buttons[next]?.parentElement
    if (card) ensureCardVisible(card)
  })
}
</script>

<template>
  <div v-if="pictogramStore.loading" role="status" class="flex flex-col items-center justify-center gap-3 p-12">
    <div class="h-8 w-8 motion-safe:animate-spin rounded-full border-2 border-surface-300 border-t-primary-500"></div>
    <span class="text-accessible-textLight">Cargando pictogramas...</span>
  </div>

  <div v-else-if="pictogramStore.error" role="alert" class="mx-4 rounded-xl bg-red-50 p-6 text-center">
    <span class="text-red-700">{{ pictogramStore.error }}</span>
  </div>

  <div
    v-else-if="!categoryStore.selectedCategoryId && pictogramStore.pictograms.length === 0"
    class="flex items-center justify-center p-12"
  >
    <p class="text-accessible-textLight">Selecciona una categoría o busca un pictograma</p>
  </div>

  <div v-else-if="pictogramStore.pictograms.length === 0" class="flex items-center justify-center p-12">
    <p class="text-accessible-textLight">No hay pictogramas en esta categoría</p>
  </div>

  <div
    v-else
    role="grid"
    aria-label="Pictogramas"
    class="grid grid-cols-3 gap-2.5 px-3 py-4 sm:grid-cols-4 sm:gap-3.5 sm:px-4 sm:py-5 md:grid-cols-5 md:gap-4 lg:grid-cols-6 lg:px-6"
    @keydown="handleGridKeydown"
  >
    <PictogramCard
      v-for="pictogram in pictogramStore.pictograms"
      :key="pictogram.id"
      :pictogram="pictogram"
      :category-color="getCategoryColor(pictogram)"
      :disabled="phraseStore.isFull"
      @select="emit('select', $event)"
    />
  </div>
</template>
