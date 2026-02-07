<script setup lang="ts">
import { Volume2, VolumeX } from 'lucide-vue-next'
import { useTTS } from '@/application/composables/useTTS'
import { WebSpeechTTS } from '@/infrastructure/tts/WebSpeechTTS'

defineProps<{
  text: string
}>()

const provider = new WebSpeechTTS()
const { speak, stop, speaking, isSupported } = useTTS(provider)
</script>

<template>
  <button
    v-if="isSupported"
    :aria-label="`Escuchar: ${text.slice(0, 50)}`"
    class="min-h-touch min-w-touch rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-accessible-focus focus:ring-offset-1"
    @click="speaking ? stop() : speak(text)"
  >
    <VolumeX v-if="speaking" :size="20" aria-hidden="true" />
    <Volume2 v-else :size="20" aria-hidden="true" />
  </button>
</template>
