export interface TTSCallbacks {
  onStart?: () => void
  onEnd?: () => void
}

export interface TTSProvider {
  speak(text: string, callbacks?: TTSCallbacks): void
  stop(): void
  readonly isSupported: boolean
}
