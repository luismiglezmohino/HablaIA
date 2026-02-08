<script setup lang="ts">
import { ref, watch } from 'vue'
import { Search } from 'lucide-vue-next'

const query = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const emit = defineEmits<{
  search: [query: string]
}>()

watch(query, (value) => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', value)
  }, 300)
})
</script>

<template>
  <div class="relative">
    <label for="search-pictograms" class="sr-only">Buscar pictogramas</label>
    <Search
      :size="20"
      class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
      aria-hidden="true"
    />
    <input
      id="search-pictograms"
      v-model="query"
      type="search"
      role="searchbox"
      placeholder="Buscar pictogramas..."
      class="min-h-touch w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 text-base transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-accessible-focus"
    />
  </div>
</template>
