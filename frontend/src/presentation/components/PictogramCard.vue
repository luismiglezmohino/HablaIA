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
    :style="{ borderTopColor: props.categoryColor, backgroundColor: props.categoryColor + '14' }"
    class="group rounded-2xl border border-surface-400 border-t-[5px] shadow-card motion-safe:transition-all motion-safe:duration-200 motion-safe:hover:shadow-card-hover motion-safe:hover:-translate-y-1 has-[:focus-visible]:z-30 has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-primary-500 has-[:focus-visible]:ring-offset-2 has-[:focus-visible]:shadow-card-hover has-[:focus-visible]:-translate-y-1"
  >
    <button
      :aria-label="`Pictograma ${props.pictogram.label}`"
      :disabled="props.disabled"
      class="min-h-touch min-w-touch flex w-full flex-col items-center overflow-hidden rounded-2xl text-center transition-colors focus:outline-none disabled:cursor-not-allowed disabled:opacity-40"
      @click="emit('select', props.pictogram)"
    >
      <div class="flex w-full items-center justify-center bg-white px-2 pt-4 pb-3 sm:px-3 sm:pt-5 sm:pb-4">
        <img
          :src="props.pictogram.imagePath"
          alt=""
          loading="lazy"
          class="h-16 w-16 object-contain drop-shadow-md sm:h-20 sm:w-20 lg:h-24 lg:w-24"
        />
      </div>
      <span
        class="block w-full border-t border-surface-100 px-2 py-2 text-sm font-semibold leading-tight text-accessible-text sm:py-2.5 sm:text-base"
      >{{ props.pictogram.label }}</span>
    </button>
  </div>
</template>
