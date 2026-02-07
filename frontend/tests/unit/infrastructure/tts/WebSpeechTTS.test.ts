import { describe, it, expect, beforeEach, vi } from 'vitest'
import { WebSpeechTTS } from '@/infrastructure/tts/WebSpeechTTS'

class MockUtterance {
  text = ''
  lang = ''
  voice: SpeechSynthesisVoice | null = null
  rate = 1
  pitch = 1
  volume = 1
  constructor(text?: string) {
    if (text) this.text = text
  }
}

function mockSpeechSynthesis() {
  const mock = {
    speak: vi.fn(),
    cancel: vi.fn(),
    pause: vi.fn(),
    resume: vi.fn(),
    getVoices: vi.fn().mockReturnValue([]),
    pending: false,
    speaking: false,
    paused: false,
  }
  Object.defineProperty(window, 'speechSynthesis', {
    writable: true,
    value: mock,
  })
  Object.defineProperty(window, 'SpeechSynthesisUtterance', {
    writable: true,
    value: MockUtterance,
  })
  return mock
}

describe('WebSpeechTTS', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    mockSpeechSynthesis()
  })

  describe('isSupported', () => {
    it('returns true when speechSynthesis is available', () => {
      const tts = new WebSpeechTTS()

      expect(tts.isSupported).toBe(true)
    })

    it('returns false when speechSynthesis is not available', () => {
      Object.defineProperty(window, 'speechSynthesis', {
        writable: true,
        value: undefined,
      })

      const tts = new WebSpeechTTS()

      expect(tts.isSupported).toBe(false)
    })
  })

  describe('speak', () => {
    it('calls speechSynthesis.cancel before speaking', () => {
      const mock = mockSpeechSynthesis()
      const tts = new WebSpeechTTS()

      tts.speak('Hola mundo')

      expect(mock.cancel).toHaveBeenCalled()
    })

    it('calls speechSynthesis.speak with an utterance', () => {
      const mock = mockSpeechSynthesis()
      const tts = new WebSpeechTTS()

      tts.speak('Quiero comer pan')

      expect(mock.speak).toHaveBeenCalledTimes(1)
      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.text).toBe('Quiero comer pan')
    })

    it('sets utterance lang based on navigator.language', () => {
      const mock = mockSpeechSynthesis()
      vi.spyOn(navigator, 'language', 'get').mockReturnValue('es-MX')
      const tts = new WebSpeechTTS()

      tts.speak('Hola')

      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.lang).toBe('es-MX')
    })

    it('defaults lang to es-ES when navigator.language is not Spanish', () => {
      const mock = mockSpeechSynthesis()
      vi.spyOn(navigator, 'language', 'get').mockReturnValue('en-US')
      const tts = new WebSpeechTTS()

      tts.speak('Hola')

      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.lang).toBe('es-ES')
    })

    it('sets rate to 0.9 for SAAC users', () => {
      const mock = mockSpeechSynthesis()
      const tts = new WebSpeechTTS()

      tts.speak('Hola')

      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.rate).toBe(0.9)
    })

    it('selects a Spanish voice when available', () => {
      const mock = mockSpeechSynthesis()
      const spanishVoice = { lang: 'es-ES', name: 'Monica' } as SpeechSynthesisVoice
      const englishVoice = { lang: 'en-US', name: 'Samantha' } as SpeechSynthesisVoice
      mock.getVoices.mockReturnValue([englishVoice, spanishVoice])
      const tts = new WebSpeechTTS()

      tts.speak('Hola')

      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.voice).toBe(spanishVoice)
    })

    it('prioritizes voice matching navigator.language dialect', () => {
      const mock = mockSpeechSynthesis()
      const esES = { lang: 'es-ES', name: 'Monica' } as SpeechSynthesisVoice
      const esMX = { lang: 'es-MX', name: 'Paulina' } as SpeechSynthesisVoice
      mock.getVoices.mockReturnValue([esES, esMX])
      vi.spyOn(navigator, 'language', 'get').mockReturnValue('es-MX')
      const tts = new WebSpeechTTS()

      tts.speak('Hola')

      const utterance = mock.speak.mock.calls[0]![0] as SpeechSynthesisUtterance
      expect(utterance.voice).toBe(esMX)
    })

    it('does nothing when speechSynthesis is not supported', () => {
      Object.defineProperty(window, 'speechSynthesis', {
        writable: true,
        value: undefined,
      })

      const tts = new WebSpeechTTS()
      expect(() => tts.speak('Hola')).not.toThrow()
    })
  })

  describe('stop', () => {
    it('calls speechSynthesis.cancel', () => {
      const mock = mockSpeechSynthesis()
      const tts = new WebSpeechTTS()

      tts.stop()

      expect(mock.cancel).toHaveBeenCalled()
    })

    it('does nothing when speechSynthesis is not supported', () => {
      Object.defineProperty(window, 'speechSynthesis', {
        writable: true,
        value: undefined,
      })

      const tts = new WebSpeechTTS()
      expect(() => tts.stop()).not.toThrow()
    })
  })
})
