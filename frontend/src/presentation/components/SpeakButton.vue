<script setup lang="ts">
import { Volume2, VolumeX, AlertCircle } from 'lucide-vue-next'
import { useTTS } from '@/application/composables/useTTS'
import { WebSpeechTTS } from '@/infrastructure/tts/WebSpeechTTS'

defineProps<{
  text: string
}>()

const provider = new WebSpeechTTS()
const { speak, stop, speaking, error, isSupported } = useTTS(provider)
</script>

<template>
  <button
    v-if="isSupported"
    :aria-label="error ? 'Error de audio' : `Escuchar: ${text.slice(0, 50)}`"
    class="min-h-touch min-w-touch rounded-xl p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1"
    :class="error ? 'text-red-500 hover:bg-red-50' : 'text-primary-600 hover:bg-primary-50'"
    @click="speaking ? stop() : speak(text)"
  >
    <AlertCircle v-if="error" :size="20" aria-hidden="true" />
    <VolumeX v-else-if="speaking" :size="20" aria-hidden="true" />
    <Volume2 v-else :size="20" aria-hidden="true" />
  </button>
</template>
