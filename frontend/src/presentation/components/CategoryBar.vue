<script setup lang="ts">
import { type Component } from 'vue'
import { Users, Play, Heart, MapPin, Box, Utensils, Car, Shapes } from 'lucide-vue-next'
import { useCategoryStore } from '@/application/stores/useCategoryStore'

const iconMap: Record<string, Component> = {
  users: Users,
  play: Play,
  heart: Heart,
  'map-pin': MapPin,
  box: Box,
  utensils: Utensils,
  car: Car,
}

const store = useCategoryStore()

function handleKeydown(event: KeyboardEvent, index: number) {
  const categories = store.sortedCategories
  let nextIndex = index

  if (event.key === 'ArrowRight') {
    nextIndex = (index + 1) % categories.length
  } else if (event.key === 'ArrowLeft') {
    nextIndex = (index - 1 + categories.length) % categories.length
  } else {
    return
  }

  const next = categories[nextIndex]
  if (next) {
    store.selectCategory(next.id)
  }
}
</script>

<template>
  <div v-if="store.loading" role="status" class="flex items-center justify-center p-4">
    <span class="text-accessible-textLight">Cargando categorías...</span>
  </div>

  <div v-else-if="store.error" role="alert" class="flex items-center justify-center gap-2 p-4">
    <span class="text-red-600">{{ store.error }}</span>
  </div>

  <nav v-else aria-label="Categorías" class="overflow-x-auto">
    <div role="tablist" class="flex gap-2 p-2">
      <button
        v-for="(category, index) in store.sortedCategories"
        :key="category.id"
        role="tab"
        :aria-selected="store.selectedCategoryId === category.id ? 'true' : 'false'"
        :aria-label="`Categoría ${category.name}`"
        :style="{ borderColor: category.colorHex }"
        class="min-h-touch min-w-touch flex items-center gap-2 rounded-lg border-2 bg-white px-3 py-2 text-sm font-medium text-accessible-text transition-colors hover:bg-gray-50"
        :class="{
          'ring-2 ring-accessible-focus ring-offset-1': store.selectedCategoryId === category.id,
        }"
        @click="store.selectCategory(category.id)"
        @keydown="handleKeydown($event, index)"
      >
        <component
          :is="(category.icon && iconMap[category.icon]) || Shapes"
          :size="18"
          aria-hidden="true"
        />
        <span>{{ category.name }}</span>
      </button>
    </div>
  </nav>
</template>
