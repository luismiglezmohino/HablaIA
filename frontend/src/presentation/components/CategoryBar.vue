<script setup lang="ts">
import { type Component } from 'vue'
import { Users, Play, Heart, MapPin, Box, Utensils, Car, Shapes } from 'lucide-vue-next'
import { Skeleton } from '@/presentation/components/ui/skeleton'
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
  <div v-if="store.loading" role="status" class="flex items-center gap-2 bg-white px-3 py-3 border-b border-surface-200 sm:gap-3 sm:px-4 sm:py-4">
    <Skeleton class="h-11 w-11 rounded-xl sm:h-12 sm:w-28" />
    <Skeleton class="h-11 w-11 rounded-xl sm:h-12 sm:w-32" />
    <Skeleton class="h-11 w-11 rounded-xl sm:h-12 sm:w-24" />
    <Skeleton class="h-11 w-11 rounded-xl sm:h-12 sm:w-28" />
    <span class="sr-only">Cargando categorías...</span>
  </div>

  <div v-else-if="store.error" role="alert" class="flex items-center justify-center gap-2 p-4">
    <span class="text-red-600">{{ store.error }}</span>
  </div>

  <nav v-else aria-label="Categorías" class="bg-white border-b border-surface-200 shadow-soft xl:overflow-x-auto">
    <div role="tablist" class="flex flex-wrap gap-2 px-3 py-3 sm:gap-3 sm:px-4 sm:py-4 xl:flex-nowrap tablet-landscape-nowrap">
      <button
        v-for="(category, index) in store.sortedCategories"
        :key="category.id"
        role="tab"
        :aria-selected="store.selectedCategoryId === category.id ? 'true' : 'false'"
        :aria-label="`Categoría ${category.name}`"
        :style="{
          borderColor: category.colorHex,
          backgroundColor: store.selectedCategoryId === category.id ? category.colorHex + '18' : undefined,
        }"
        class="min-h-touch min-w-touch flex shrink-0 items-center justify-center rounded-xl border-2 px-2.5 py-2 font-semibold text-accessible-text shadow-card motion-safe:transition-all motion-safe:duration-200 motion-safe:hover:shadow-card-hover motion-safe:hover:-translate-y-0.5 motion-safe:active:translate-y-0 sm:justify-start sm:gap-2.5 sm:px-5 sm:py-2.5 sm:text-sm tablet-landscape-center"
        :class="
          store.selectedCategoryId === category.id
            ? 'ring-2 ring-primary-500 ring-offset-2'
            : 'bg-white'
        "
        @click="store.selectCategory(category.id)"
        @keydown="handleKeydown($event, index)"
      >
        <span
          :style="{ backgroundColor: category.colorHex + '25' }"
          class="flex h-6 w-6 items-center justify-center rounded-md sm:h-8 sm:w-8 sm:rounded-lg"
          aria-hidden="true"
        >
          <component
            :is="(category.icon && iconMap[category.icon]) || Shapes"
            :size="14"
            class="sm:!h-[18px] sm:!w-[18px]"
            :style="{ color: category.colorHex }"
          />
        </span>
        <span class="hidden sm:inline tablet-landscape-hide">{{ category.name }}</span>
      </button>
    </div>
  </nav>
</template>
