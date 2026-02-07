import type { TTSCallbacks, TTSProvider } from '@/domain/services/TTSProvider'

export class WebSpeechTTS implements TTSProvider {
  get isSupported(): boolean {
    return typeof window !== 'undefined' && 'speechSynthesis' in window && !!window.speechSynthesis
  }

  speak(text: string, callbacks?: TTSCallbacks): void {
    if (!this.isSupported) return

    window.speechSynthesis.cancel()

    const utterance = new window.SpeechSynthesisUtterance(text)
    utterance.lang = this.resolveLanguage()
    utterance.rate = 0.9

    const voice = this.findBestVoice()
    if (voice) {
      utterance.voice = voice
    }

    if (callbacks?.onStart) {
      utterance.onstart = callbacks.onStart
    }
    if (callbacks?.onEnd) {
      utterance.onend = callbacks.onEnd
    }

    window.speechSynthesis.speak(utterance)
  }

  stop(): void {
    if (!this.isSupported) return
    window.speechSynthesis.cancel()
  }

  private resolveLanguage(): string {
    const browserLang = navigator.language
    return browserLang.startsWith('es') ? browserLang : 'es-ES'
  }

  private findBestVoice(): SpeechSynthesisVoice | null {
    const voices = window.speechSynthesis.getVoices()
    const preferredLang = this.resolveLanguage()

    const exactMatch = voices.find((v) => v.lang === preferredLang)
    if (exactMatch) return exactMatch

    const spanishVoice = voices.find((v) => v.lang.startsWith('es'))
    if (spanishVoice) return spanishVoice

    return null
  }
}
