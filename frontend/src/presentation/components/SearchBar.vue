<script setup lang="ts">
import { ref, watch } from 'vue'
import { Search, X } from 'lucide-vue-next'

const query = ref('')
const inputRef = ref<HTMLInputElement | null>(null)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const emit = defineEmits<{
  search: [query: string]
  focus: []
  blur: []
}>()

function handleEscape() {
  if (query.value) {
    query.value = ''
    if (debounceTimer) clearTimeout(debounceTimer)
    emit('search', '')
  } else {
    inputRef.value?.blur()
  }
}

defineExpose({
  clear: () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    query.value = ''
  },
})

watch(query, (value) => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', value)
  }, 300)
})
</script>

<template>
  <div class="border-b border-surface-200 bg-white px-4 py-3">
    <div class="relative">
      <Search
        :size="20"
        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-accessible-textLight"
        aria-hidden="true"
      />
      <input
        ref="inputRef"
        id="search-pictograms"
        v-model="query"
        type="search"
        placeholder="Buscar pictogramas..."
        autocomplete="off"
        class="min-h-touch w-full rounded-xl border-2 border-surface-200 bg-surface-50 py-3 pl-12 pr-10 text-base shadow-soft transition-all placeholder:text-surface-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:shadow-card"
        @focus="emit('focus')"
        @blur="emit('blur')"
        @keydown.escape="handleEscape"
      />
      <button
        v-if="query"
        aria-label="Borrar búsqueda"
        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-2 text-surface-400 hover:text-accessible-textLight focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        @click="query = ''"
      >
        <X :size="20" aria-hidden="true" />
      </button>
    </div>
  </div>
</template>
