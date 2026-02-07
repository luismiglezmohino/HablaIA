<script setup lang="ts">
import type { Pictogram } from '@/domain/entities/Pictogram'

const props = defineProps<{
  pictogram: Pictogram
  categoryColor: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  select: [pictogram: Pictogram]
}>()
</script>

<template>
  <div
    :style="{ borderTopColor: props.categoryColor }"
    class="border-t-4 rounded-xl bg-white shadow-sm hover:shadow-md transition-shadow"
  >
    <button
      :aria-label="`Pictograma ${props.pictogram.label}`"
      :disabled="props.disabled"
      class="min-h-touch min-w-touch flex w-full flex-col items-center gap-2 rounded-xl p-3 text-center transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-accessible-focus focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
      @click="emit('select', props.pictogram)"
    >
      <img
        :src="props.pictogram.imagePath"
        :alt="props.pictogram.label"
        class="h-20 w-20 object-contain"
      />
      <span class="text-sm font-medium text-accessible-text">{{ props.pictogram.label }}</span>
    </button>
  </div>
</template>
