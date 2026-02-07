import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SpeakButton from '@/presentation/components/SpeakButton.vue'

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
    value: class {
      text = ''
      lang = ''
      voice: SpeechSynthesisVoice | null = null
      rate = 1
      pitch = 1
      volume = 1
      onstart: (() => void) | null = null
      onend: (() => void) | null = null
      constructor(text?: string) {
        if (text) this.text = text
      }
    },
  })
  return mock
}

describe('SpeakButton', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    mockSpeechSynthesis()
  })

  describe('rendering', () => {
    it('renders a button', () => {
      const wrapper = mount(SpeakButton, { props: { text: 'Hola mundo' } })

      expect(wrapper.find('button').exists()).toBe(true)
    })

    it('does not render when TTS is not supported', () => {
      Object.defineProperty(window, 'speechSynthesis', {
        writable: true,
        value: undefined,
      })

      const wrapper = mount(SpeakButton, { props: { text: 'Hola' } })

      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('has accessible aria-label', () => {
      const wrapper = mount(SpeakButton, { props: { text: 'Quiero comer pan' } })

      const button = wrapper.find('button')

      expect(button.attributes('aria-label')).toContain('Escuchar')
    })

    it('has minimum touch target size', () => {
      const wrapper = mount(SpeakButton, { props: { text: 'Hola' } })

      const button = wrapper.find('button')
      const classes = button.classes()

      expect(classes.some((c) => c.includes('min-h-') || c.includes('min-h-touch'))).toBe(true)
      expect(classes.some((c) => c.includes('min-w-') || c.includes('min-w-touch'))).toBe(true)
    })
  })

  describe('interaction', () => {
    it('calls speechSynthesis.speak when clicked', async () => {
      const mock = mockSpeechSynthesis()
      const wrapper = mount(SpeakButton, { props: { text: 'Hola mundo' } })

      await wrapper.find('button').trigger('click')

      expect(mock.speak).toHaveBeenCalledTimes(1)
    })

    it('calls speechSynthesis.cancel when clicked while speaking', async () => {
      const mock = mockSpeechSynthesis()
      const wrapper = mount(SpeakButton, { props: { text: 'Hola' } })

      await wrapper.find('button').trigger('click')
      const utterance = mock.speak.mock.calls[0]![0]
      utterance.onstart?.()
      await wrapper.vm.$nextTick()

      await wrapper.find('button').trigger('click')

      expect(mock.cancel).toHaveBeenCalled()
    })
  })
})
