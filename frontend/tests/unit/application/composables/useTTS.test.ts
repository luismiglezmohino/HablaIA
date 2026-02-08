import { describe, it, expect, vi } from 'vitest'
import { useTTS } from '@/application/composables/useTTS'
import type { TTSProvider } from '@/domain/services/TTSProvider'

function createMockProvider(overrides: Partial<TTSProvider> = {}): TTSProvider {
  return {
    speak: vi.fn(),
    stop: vi.fn(),
    isSupported: true,
    ...overrides,
  }
}

describe('useTTS', () => {
  describe('speak', () => {
    it('delegates to the provider', () => {
      const provider = createMockProvider()
      const { speak } = useTTS(provider)

      speak('Hola mundo')

      expect(provider.speak).toHaveBeenCalledWith('Hola mundo', expect.any(Object))
    })
  })

  describe('stop', () => {
    it('delegates to the provider', () => {
      const provider = createMockProvider()
      const { stop } = useTTS(provider)

      stop()

      expect(provider.stop).toHaveBeenCalled()
    })

    it('sets speaking to false', () => {
      const provider = createMockProvider()
      const { speaking, stop } = useTTS(provider)

      stop()

      expect(speaking.value).toBe(false)
    })
  })

  describe('speaking', () => {
    it('starts as false', () => {
      const provider = createMockProvider()
      const { speaking } = useTTS(provider)

      expect(speaking.value).toBe(false)
    })

    it('becomes true when onStart callback fires', () => {
      const provider = createMockProvider({
        speak: vi.fn().mockImplementation((_text, callbacks) => {
          callbacks?.onStart?.()
        }),
      })
      const { speak, speaking } = useTTS(provider)

      speak('Hola')

      expect(speaking.value).toBe(true)
    })

    it('becomes false when onEnd callback fires', () => {
      const provider = createMockProvider({
        speak: vi.fn().mockImplementation((_text, callbacks) => {
          callbacks?.onStart?.()
          callbacks?.onEnd?.()
        }),
      })
      const { speak, speaking } = useTTS(provider)

      speak('Hola')

      expect(speaking.value).toBe(false)
    })
  })

  describe('error', () => {
    it('starts as null', () => {
      const provider = createMockProvider()
      const { error } = useTTS(provider)

      expect(error.value).toBeNull()
    })

    it('sets error message when onError callback fires', () => {
      const provider = createMockProvider({
        speak: vi.fn().mockImplementation((_text, callbacks) => {
          callbacks?.onError?.()
        }),
      })
      const { speak, error } = useTTS(provider)

      speak('Hola')

      expect(error.value).toBe('No se pudo reproducir el audio.')
    })

    it('clears error on next speak', () => {
      const provider = createMockProvider({
        speak: vi
          .fn()
          .mockImplementationOnce((_text, callbacks) => {
            callbacks?.onError?.()
          })
          .mockImplementationOnce((_text, callbacks) => {
            callbacks?.onStart?.()
          }),
      })
      const { speak, error } = useTTS(provider)

      speak('Hola')
      expect(error.value).not.toBeNull()

      speak('Adiós')
      expect(error.value).toBeNull()
    })

    it('sets speaking to false on error', () => {
      const provider = createMockProvider({
        speak: vi.fn().mockImplementation((_text, callbacks) => {
          callbacks?.onStart?.()
          callbacks?.onError?.()
        }),
      })
      const { speak, speaking } = useTTS(provider)

      speak('Hola')

      expect(speaking.value).toBe(false)
    })
  })

  describe('isSupported', () => {
    it('reflects provider support as true', () => {
      const provider = createMockProvider({ isSupported: true })
      const { isSupported } = useTTS(provider)

      expect(isSupported.value).toBe(true)
    })

    it('reflects provider support as false', () => {
      const provider = createMockProvider({ isSupported: false })
      const { isSupported } = useTTS(provider)

      expect(isSupported.value).toBe(false)
    })
  })
})
