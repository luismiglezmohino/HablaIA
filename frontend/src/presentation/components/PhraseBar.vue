<script setup lang="ts">
import { X, Trash2, Sparkles, Loader2, RefreshCw } from 'lucide-vue-next'
import SpeakButton from '@/presentation/components/SpeakButton.vue'
import { usePhraseStore } from '@/application/stores/usePhraseStore'

const store = usePhraseStore()

const emit = defineEmits<{
  generate: []
}>()
</script>

<template>
  <section aria-label="Barra de frases" class="border-b border-gray-200 bg-white p-3">
    <!-- Empty state -->
    <div v-if="store.selectedPictograms.length === 0" class="text-center">
      <p class="text-sm text-accessible-textLight">Selecciona pictogramas para construir una frase</p>
    </div>

    <!-- Selected pictograms -->
    <div v-else>
      <div class="flex items-center gap-2">
        <!-- Chips -->
        <div aria-live="polite" class="flex flex-1 flex-wrap gap-2">
          <div
            v-for="(pictogram, index) in store.selectedPictograms"
            :key="`${pictogram.id}-${index}`"
            class="flex items-center gap-1 rounded-full bg-gray-100 py-1 pl-1 pr-2"
          >
            <img
              :src="pictogram.imagePath"
              :alt="pictogram.label"
              class="h-8 w-8 rounded-full object-contain"
            />
            <span class="text-sm text-accessible-text">{{ pictogram.label }}</span>
            <button
              data-testid="remove-chip"
              :aria-label="`Eliminar ${pictogram.label}`"
              class="ml-1 min-h-6 min-w-6 rounded-full p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-700"
              @click="store.removePictogram(index)"
            >
              <X :size="14" aria-hidden="true" />
            </button>
          </div>
        </div>

        <!-- Count + actions -->
        <div class="flex items-center gap-2">
          <span class="text-xs text-accessible-textLight">{{ store.selectedPictograms.length }}/10</span>
          <button
            data-testid="clear-btn"
            aria-label="Borrar todos los pictogramas"
            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
            @click="store.clearSelection()"
          >
            <Trash2 :size="18" aria-hidden="true" />
          </button>
        </div>
      </div>

      <!-- Generate button -->
      <div class="mt-3">
        <button
          data-testid="generate-btn"
          :disabled="!store.canGenerate || store.loading"
          :aria-label="store.loading ? 'Generando frase' : store.error ? 'Reintentar generar frase' : 'Generar frase'"
          class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50"
          :class="store.error ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'"
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

    <!-- Phrase results -->
    <div v-if="store.phraseResponse" class="mt-3 space-y-2">
      <div class="flex items-center gap-2">
        <span class="text-xs font-medium uppercase text-accessible-textLight">
          {{ store.phraseResponse.source }}
        </span>
      </div>
      <ul class="space-y-2" role="list">
        <li
          v-for="(variation, index) in store.phraseResponse.variations"
          :key="index"
          class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-4 py-3"
        >
          <span class="text-accessible-text">{{ variation }}</span>
          <SpeakButton :data-testid="`speak-btn-${index}`" :text="variation" />
        </li>
      </ul>
    </div>
  </section>
</template>
