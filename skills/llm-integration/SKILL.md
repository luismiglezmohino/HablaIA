---
name: llm-integration
description: Multi-provider LLM integration for phrase generation (OpenAI, Gemini, extensible)
license: MIT
compatibility: opencode
metadata:
  type: ai-integration
  pattern: factory
---

# SKILL: LLM Integration (Multi-Provider)

## Arquitectura

El proyecto usa un patrón Factory para soportar múltiples proveedores LLM de forma intercambiable.
El Domain define el contrato, Infrastructure implementa cada provider.

```
Domain/
  Phrase/Service/PhraseGeneratorInterface.php    # Contrato agnóstico
Infrastructure/
  ExternalApi/
    PhraseGeneratorFactory.php                   # Factory (selecciona provider por env)
    Shared/PhrasePrompt.php                      # Prompt compartido entre providers
    OpenAI/
      RealOpenAIPhraseGenerator.php              # Provider OpenAI (GPT-4o-mini)
      Exception/OpenAIException.php
    Gemini/
      GeminiPhraseGenerator.php                  # Provider Gemini (Flash + fallback Flash Lite)
      Exception/GeminiException.php
  Phrase/
    FakePhraseGenerator.php                      # Fake (templates sin API, dev/test)
```

## Providers Disponibles

| Provider | Env `PHRASE_PROVIDER` | Modelo default | Uso |
|----------|-------------------|----------------|-----|
| OpenAI   | `openai`          | gpt-4o-mini    | Producción |
| Gemini   | `gemini`          | gemini-2.5-flash (fallback: flash-lite) | Producción (alternativa) |
| Fake     | `fake`            | ninguno        | Desarrollo/Testing (default) |

## Contrato Domain (Agnóstico)

```php
// Domain/Phrase/Service/PhraseGeneratorInterface.php
interface PhraseGeneratorInterface
{
    /** @return array<string> List of phrase variations (typically 3) */
    public function generate(PictogramSequence $sequence, array $labels): array;
}
```

El Domain NO conoce que LLM se usa. Solo define el contrato.

## Factory Pattern

```php
// Infrastructure/ExternalApi/PhraseGeneratorFactory.php
public function create(): PhraseGeneratorInterface
{
    return match ($this->provider) {   // $this->provider viene de env(PHRASE_PROVIDER)
        'gemini' => new GeminiPhraseGenerator(...),
        'openai' => new RealOpenAIPhraseGenerator(...),
        'fake'   => new FakePhraseGenerator(),
        default  => new FakePhraseGenerator(), // Fallback seguro
    };
}
```

## Prompt Compartido

Todos los providers usan el mismo prompt (consistencia para usuarios SAAC):

```php
// Infrastructure/ExternalApi/Shared/PhrasePrompt.php
final class PhrasePrompt
{
    public const string SYSTEM = <<<PROMPT
    Eres un asistente especializado en comunicación aumentativa y alternativa (SAAC).
    Tu tarea es convertir palabras clave de pictogramas en frases naturales en español.
    Genera exactamente 3 variaciones de la frase.
    Responde SOLO con un JSON válido: {"variations": ["frase 1", "frase 2", "frase 3"]}
    PROMPT;

    public const string USER_TEMPLATE = 'Genera 3 variaciones de frase natural para las siguientes palabras: %s';
    public const int VARIATIONS_COUNT = 3;
    public const int MAX_LABEL_LENGTH = 50;
}
```

## Añadir Nuevo Provider

1. Crear directorio: `Infrastructure/ExternalApi/{ProviderName}/`
2. Implementar `PhraseGeneratorInterface`
3. Crear excepción específica: `Exception/{ProviderName}Exception.php`
4. Reutilizar `PhrasePrompt::SYSTEM` y `PhrasePrompt::USER_TEMPLATE`
5. Añadir caso al `match()` en `PhraseGeneratorFactory`
6. Añadir env vars en `.env.example` y `docker-compose.yml`
7. Registrar en `config/packages/services.yaml` si necesita argumentos DI

### Patron de cada provider:
```php
final class NuevoProvider implements PhraseGeneratorInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly float $temperature,
        private readonly int $maxTokens,
        private readonly int $timeout,
    ) {}

    public function generate(PictogramSequence $sequence, array $labels): array
    {
        // 1. Sanitizar labels (prevenir prompt injection)
        // 2. Construir request body (formato específico del provider)
        // 3. Enviar request con timeout
        // 4. Parsear response JSON: {"variations": [...]}
        // 5. Devolver array<string> con max VARIATIONS_COUNT elementos
    }
}
```

## Security Checklist

- [ ] API Keys en `.env`, NUNCA en código
- [ ] Rate limiting activado (30 req/min en `/api/phrases/generate`)
- [ ] Input sanitization: labels sanitizados contra prompt injection (regex `[^\p{L}\p{N}\s\-]`)
- [ ] Max label length: 50 caracteres
- [ ] Timeout configurado (default 5s)
- [ ] Logs SIN incluir API responses completas (pueden contener PII)
- [ ] Provider `fake` como default en desarrollo (no consume API real)

## Variables de Entorno

```bash
# Provider selection
PHRASE_PROVIDER=fake              # fake | openai | gemini

# OpenAI
OPENAI_API_URL=https://api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

# Gemini
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models
GEMINI_API_KEY=AIza...
GEMINI_MODEL=gemini-2.5-flash

# Shared
PHRASE_TEMPERATURE=0.7
PHRASE_MAX_TOKENS=2048
PHRASE_TIMEOUT=10
```

## Errores Comunes

### 1. Provider no definido en .env
**Problema:** `PHRASE_PROVIDER` vacío o no definido.
**Solución:** El factory hace fallback a `fake`. En producción, asegurar que está definido explícitamente.

### 2. Timeout en LLM
**Problema:** Request tarda más de 5s, crítico para UX (latencia < 200ms objetivo).
**Solución:** Configurar timeout agresivo + cache de frases generadas para sequences repetidas.

### 3. Prompt injection via labels
**Problema:** Labels maliciosos pueden inyectar instrucciones al LLM.
**Solución:** Sanitizar con regex `[^\p{L}\p{N}\s\-]` y limitar a 50 chars (ya implementado en cada provider).
