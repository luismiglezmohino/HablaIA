// Test setup file for Vitest
// Add global test configuration here

// Example: Mock window.matchMedia for components that use it
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: (query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: () => {},
    removeListener: () => {},
    addEventListener: () => {},
    removeEventListener: () => {},
    dispatchEvent: () => false,
  }),
})

// Mock speechSynthesis for TTS components
Object.defineProperty(window, 'speechSynthesis', {
  writable: true,
  value: {
    speak: () => {},
    cancel: () => {},
    pause: () => {},
    resume: () => {},
    getVoices: () => [],
    pending: false,
    speaking: false,
    paused: false,
  },
})

// Mock SpeechSynthesisUtterance (not available in happy-dom)
class MockSpeechSynthesisUtterance {
  text = ''
  lang = ''
  voice: SpeechSynthesisVoice | null = null
  rate = 1
  pitch = 1
  volume = 1
  onstart: (() => void) | null = null
  onend: (() => void) | null = null
  onerror: (() => void) | null = null

  constructor(text?: string) {
    if (text) this.text = text
  }
}

Object.defineProperty(window, 'SpeechSynthesisUtterance', {
  writable: true,
  value: MockSpeechSynthesisUtterance,
})
;(globalThis as Record<string, unknown>).SpeechSynthesisUtterance = MockSpeechSynthesisUtterance
