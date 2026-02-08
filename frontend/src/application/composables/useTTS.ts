import { ref, computed } from 'vue'
import type { TTSProvider } from '@/domain/services/TTSProvider'

export function useTTS(provider: TTSProvider) {
  const speaking = ref(false)
  const error = ref<string | null>(null)
  const isSupported = computed(() => provider.isSupported)

  function speak(text: string): void {
    error.value = null
    provider.speak(text, {
      onStart: () => {
        speaking.value = true
      },
      onEnd: () => {
        speaking.value = false
      },
      onError: () => {
        speaking.value = false
        error.value = 'No se pudo reproducir el audio.'
      },
    })
  }

  function stop(): void {
    speaking.value = false
    provider.stop()
  }

  return { speak, stop, speaking, error, isSupported }
}
