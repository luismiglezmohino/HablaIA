<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { X, Trash2, Sparkles, Loader2, RefreshCw } from 'lucide-vue-next'
import SpeakButton from '@/presentation/components/SpeakButton.vue'
import { Badge } from '@/presentation/components/ui/badge'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import type { Pictogram } from '@/domain/entities/Pictogram'

const store = usePhraseStore()
const categoryStore = useCategoryStore()
const chipsRef = ref<HTMLElement | null>(null)

watch(
  () => store.selectedPictograms.length,
  (newLen, oldLen) => {
    if (newLen > oldLen) {
      nextTick(() => {
        if (chipsRef.value) {
          chipsRef.value.scrollTop = chipsRef.value.scrollHeight
          chipsRef.value.scrollLeft = chipsRef.value.scrollWidth
        }
      })
    }
  },
)

const emit = defineEmits<{
  generate: []
}>()

function getCategoryColor(pictogram: Pictogram): string {
  const category = categoryStore.categories.find((c) => c.id === pictogram.categoryId)
  return category?.colorHex ?? '#9CA3AF'
}
</script>

<template>
  <section aria-label="Barra de frases" class="border-b border-primary-200 bg-primary-50 px-4 py-3 sm:py-4">
    <!-- Empty state -->
    <div v-if="store.selectedPictograms.length === 0" class="py-2 text-center tablet-landscape-hide">
      <p class="text-base text-accessible-textLight">Selecciona pictogramas para construir una frase</p>
    </div>

    <!-- Selected pictograms -->
    <div v-else class="tablet-landscape-inline">
      <div class="flex items-center gap-2">
        <!-- Chips -->
        <div ref="chipsRef" aria-live="polite" class="flex flex-1 flex-wrap gap-1.5 overflow-y-auto max-h-24 sm:max-h-32 sm:gap-2 tablet-landscape-hstrip">
          <div
            v-for="(pictogram, index) in store.selectedPictograms"
            :key="`${pictogram.id}-${index}`"
            :style="{ borderLeftColor: getCategoryColor(pictogram), '--tw-border-left-color': getCategoryColor(pictogram) }"
            class="relative flex shrink-0 items-center gap-1 rounded-full border border-surface-200 border-l-4 bg-white shadow-sm sm:py-1 sm:pl-1 sm:pr-2 tablet-landscape-chip"
          >
            <img
              :src="pictogram.imagePath"
              :alt="pictogram.label"
              class="h-10 w-10 rounded-full bg-surface-50 object-contain p-0.5 sm:h-8 sm:w-8"
            />
            <span class="hidden text-sm text-accessible-text xl:inline">{{ pictogram.label }}</span>
            <button
              data-testid="remove-chip"
              :aria-label="`Eliminar ${pictogram.label}`"
              class="ml-0.5 min-h-7 min-w-7 rounded-full p-1 text-surface-400 hover:bg-surface-100 hover:text-accessible-text sm:ml-1 tablet-landscape-chip-x"
              @click="store.removePictogram(index)"
            >
              <X :size="16" aria-hidden="true" />
            </button>
          </div>
        </div>

        <!-- Count + actions -->
        <div class="tl-actions flex shrink-0 items-center gap-2">
          <span class="text-xs font-medium tabular-nums text-accessible-textLight">{{ store.selectedPictograms.length }}/10</span>
          <button
            data-testid="clear-btn"
            aria-label="Borrar todos los pictogramas"
            class="rounded-lg p-2.5 text-surface-400 hover:bg-white hover:text-accessible-text"
            @click="store.clearSelection()"
          >
            <Trash2 :size="20" aria-hidden="true" />
          </button>
          <!-- Compact generate — tablet landscape only -->
          <button
            :disabled="!store.canGenerate || store.loading"
            :aria-label="store.loading ? 'Generando frase' : 'Generar frase'"
            class="tl-generate-inline hidden items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-white shadow-md disabled:opacity-50"
            :class="store.error ? 'bg-red-600' : 'bg-primary-600'"
            @click="emit('generate')"
          >
            <Loader2 v-if="store.loading" :size="14" class="animate-spin" aria-hidden="true" />
            <Sparkles v-else :size="14" aria-hidden="true" />
            Generar
          </button>
        </div>
      </div>

      <!-- Generate button -->
      <div class="tl-generate mt-3">
        <button
          data-testid="generate-btn"
          :disabled="!store.canGenerate || store.loading"
          :aria-label="store.loading ? 'Generando frase' : store.error ? 'Reintentar generar frase' : 'Generar frase'"
          class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-base font-semibold text-white shadow-md transition-colors disabled:cursor-not-allowed disabled:opacity-50"
          :class="store.error ? 'bg-red-600 hover:bg-red-700 hover:shadow-lg' : 'bg-primary-600 hover:bg-primary-700 motion-safe:hover:shadow-glow motion-safe:active:scale-[0.98]'"
          @click="emit('generate')"
        >
          <Loader2 v-if="store.loading" :size="18" class="animate-spin" aria-hidden="true" />
          <RefreshCw v-else-if="store.error" :size="18" aria-hidden="true" />
          <Sparkles v-else :size="18" aria-hidden="true" />
          {{ store.loading ? 'Generando...' : store.error ? 'Reintentar' : 'Generar frase' }}
        </button>
      </div>
    </div>

    <!-- Loading status for screen readers -->
    <div v-if="store.loading" role="status" class="sr-only">
      Generando frase...
    </div>

    <!-- Error -->
    <div v-if="store.error" role="alert" class="mt-3 text-center">
      <span class="text-sm text-red-600">{{ store.error }}</span>
    </div>

    <!-- Phrase results (tablet+ only, mobile renders in HomeView) -->
    <div v-if="store.phraseResponse" class="mt-3 hidden space-y-2 sm:block">
      <div class="flex items-center gap-2">
        <Badge class="bg-accent-100 text-accent-800 border-transparent">
          {{ store.phraseResponse.source }}
        </Badge>
      </div>
      <ul class="space-y-2" role="list">
        <li
          v-for="(variation, index) in store.phraseResponse.variations"
          :key="index"
          class="flex items-center justify-between gap-2 rounded-xl border border-surface-200 bg-white px-4 py-3 shadow-card motion-safe:hover:shadow-card-hover"
        >
          <span class="text-accessible-text">{{ variation }}</span>
          <SpeakButton :data-testid="`speak-btn-${index}`" :text="variation" />
        </li>
      </ul>
    </div>
  </section>
</template>
