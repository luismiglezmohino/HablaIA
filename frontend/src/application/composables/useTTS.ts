import { ref, computed } from 'vue'
import type { TTSProvider } from '@/domain/services/TTSProvider'

export function useTTS(provider: TTSProvider) {
  const speaking = ref(false)
  const isSupported = computed(() => provider.isSupported)

  function speak(text: string): void {
    provider.speak(text, {
      onStart: () => {
        speaking.value = true
      },
      onEnd: () => {
        speaking.value = false
      },
    })
  }

  function stop(): void {
    speaking.value = false
    provider.stop()
  }

  return { speak, stop, speaking, isSupported }
}
