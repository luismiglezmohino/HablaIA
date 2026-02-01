# ADR-004: Estrategia de Text-to-Speech Progresiva

**Estado:** Aceptado
**Fecha:** 2026-01-31
**Contexto:** HablaIA - Síntesis de voz para SAAC

## Contexto

El comunicador debe convertir frases a audio. Usuarios con ELA, parálisis cerebral o afasia severa dependen 100% de TTS para expresarse.

**Requisitos:**
- Latencia < 500ms (crítico para UX)
- Voz natural (no robótica)
- Soporte español neutro + dialectos
- Funcionar offline (para entornos sin internet)

## Decisión

Estrategia **progresiva en 3 fases** (mismo código, backends intercambiables):

### Fase 1 (MVP): Web Speech API

```typescript
// infrastructure/tts/WebSpeechTTS.ts
const utterance = new SpeechSynthesisUtterance(phrase);
utterance.lang = 'es-ES';
utterance.rate = 1.0;
speechSynthesis.speak(utterance);
```

**Características:**
- Latencia: < 100ms (síntesis local)
- Gratuito
- Funciona offline
- Calidad variable (depende navegador/OS)
- Voces limitadas

### Fase 2 (Post-MVP): ElevenLabs API

```typescript
// infrastructure/tts/ElevenLabsTTS.ts
const audio = await fetch('https://api.elevenlabs.io/v1/text-to-speech/voice-id', {
  method: 'POST',
  headers: {
    'xi-api-key': process.env.ELEVENLABS_API_KEY,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    text: phrase,
    model_id: 'eleven_multilingual_v2',
    voice_settings: {
      stability: 0.5,
      similarity_boost: 0.75
    }
  })
});
const blob = await audio.blob();
const url = URL.createObjectURL(blob);
const audioEl = new Audio(url);
audioEl.play();
```

**Características:**
- Calidad premium (indistinguible de humano)
- Voces españolas nativas
- Latencia: 1-2s (API call + síntesis)
- Coste: $0.30/1,000 caracteres
- Requiere internet

### Fase 3 (Futuro): Voice Cloning

```typescript
// 1. Grabar voz del usuario (5 min audio)
// 2. Entrenar modelo ElevenLabs con voz del usuario
// 3. Síntesis con voz clonada

const clonedVoiceId = 'user-123-cloned-voice';
// Mismo código que Fase 2, pero con voice-id custom
```

**Características:**
- Voz del propio usuario (máxima humanización)
- Emocional (mantiene identidad vocal)
- Requiere 5 min grabación + 24h entrenamiento
- Coste: $99 one-time (Professional Plan)

### Arquitectura Abstracta

```typescript
// domain/service/TTSService.ts
interface TTSProvider {
  speak(text: string, options?: TTSOptions): Promise<void>;
  stop(): void;
  getAvailableVoices(): Promise<Voice[]>;
}

// Infrastructure implementa
class WebSpeechTTS implements TTSProvider { ... }
class ElevenLabsTTS implements TTSProvider { ... }
class VoiceCloningTTS implements TTSProvider { ... }

// Application usa
class PlayAudioUseCase {
  constructor(private ttsProvider: TTSProvider) {}

  async execute(phrase: string): Promise<void> {
    await this.ttsProvider.speak(phrase);
  }
}
```

**Cambio de provider:** 1 línea en DI container (sin tocar lógica)

## Consecuencias

### Positivas

- **Inicio rápido:** Web Speech gratis permite MVP funcional día 1
- **Mejora incremental:** Cada fase mejora calidad sin reescribir código
- **Fallback robusto:** Si ElevenLabs cae, fallback a Web Speech
- **Diferenciador:** Voice Cloning es único (competencia no lo tiene)

### Negativas

- **Costes escalan:** 1,000 usuarios activos con ElevenLabs = $300/mes
- **Dependencia externa:** ElevenLabs puede cambiar precios/discontinuar
- **Complejidad gestión voces clonadas:** Privacidad, storage (5 min audio/usuario)

### Mitigaciones

- **Costes:** Modelo freemium (Web Speech gratis, ElevenLabs premium)
- **Dependencia:** Web Speech como fallback permanente
- **Privacidad voces:** Consentimiento explícito + encriptación + GDPR

## Alternativas Consideradas

### 1. Google Cloud TTS

**Pros:** Calidad buena, $4/1M caracteres
**Contras:** Más caro que ElevenLabs, voces menos naturales
**Rechazo:** ElevenLabs superior en calidad/precio

### 2. Amazon Polly

**Pros:** Integración AWS, neural voices
**Contras:** $16/1M caracteres (4x más caro)
**Rechazo:** Precio prohibitivo

### 3. Coqui TTS (Open Source, local)

**Pros:** Gratuito, offline, privacidad total
**Contras:** Requiere GPU, calidad inferior, complejidad deploy
**Rechazo:** Overhead técnico no justificado para MVP

## Plan de Rollout

| Fase | Provider | Coste/mes (1K usuarios) |
|------|----------|-------------------------|
| MVP | Web Speech | $0 |
| Post-MVP | ElevenLabs | $300 |
| Avanzado | Voice Cloning | $99 one-time/usuario |

**Feature Toggle:** Usuarios deciden qué TTS usar (configuración en perfil)

## Referencias

- [Web Speech API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API)
- [ElevenLabs](https://elevenlabs.io/pricing)
- [Voice Cloning Demo](https://elevenlabs.io/voice-cloning)
