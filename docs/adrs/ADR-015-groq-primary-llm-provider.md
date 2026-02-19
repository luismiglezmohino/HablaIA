# ADR-015: Groq como Proveedor LLM Principal

**Estado:** Aceptado<br>
**Fecha:** 2026-02-19<br>
**Contexto:** HablaIA - Comunicador SAAC con IA<br>
**Sustituye recomendación de:** ADR-009 (Gemini como default)

## Contexto

En ADR-009 se añadió Gemini como segundo proveedor LLM y se configuró como default
(Flash + Flash Lite, 40 RPD free tier). Tras validar el prompt con 126 pruebas y 378
variaciones usando tres modelos de dos proveedores distintos (ver `docs/testing/prompt-tuning-validation.md`),
se identificaron diferencias significativas en latencia, calidad ortográfica y coste que
justifican cambiar el proveedor principal.

### Hallazgos de la validación

| Aspecto | Gemini 2.5 Flash | Groq Llama 3.3 70B | Groq GPT-OSS 120B |
|---|---|---|---|
| Latencia media | 3.7s | 563ms | 982ms |
| Latencia máxima | 5.4s | 804ms | 2.6s |
| Ortografía española (¿¡, tildes) | Irregular | Correcta | Correcta |
| Calidad 1-7 pictogramas | Excelente | Excelente | Excelente |
| Calidad 8-10 pictogramas | Buena (errores menores) | Buena (errores menores) | Buena (errores menores) |
| Free tier | 40 RPD | 1.000 RPD / 12K TPM | 1.000 RPD / 8K TPM |
| Input / 1M tokens | $0.30 | $0.59 | $0.15 |
| Output / 1M tokens | $2.50 | $0.79 | $0.60 |

Gemini genera frases sin signos de apertura (`Hola! Que tal?` en vez de `¡Hola! ¿Qué tal?`)
y omite tildes frecuentemente. Groq genera ortografía española correcta en las 100 pruebas.

Gemini tiene una latencia media de 3.7s debido al razonamiento interno (thinking tokens).
En UX, < 1s se percibe como instantáneo, 1-3s es aceptable con indicador de carga y > 3s
el usuario pierde atención. Gemini está en el límite.

## Decisión

Adoptar **Groq GPT-OSS 120B** como proveedor LLM principal, manteniendo la arquitectura
multi-proveedor del ADR-009.

```
PHRASE_PROVIDER=openai    # Groq usa API OpenAI-compatible
OPENAI_BASE_URL=https://api.groq.com/openai/v1
OPENAI_MODEL=openai/gpt-oss-120b
OPENAI_API_KEY=gsk_...
```

### Por qué GPT-OSS 120B sobre Llama 70B

- **120B parámetros** (casi el doble) → frases más elaboradas y con cortesía natural
  ("¿Podrías llevarme?", "por favor"), útil en SAAC porque enseña pragmática comunicativa
- **4x más barato** en input ($0.15 vs $0.59 / M tokens)
- Latencia media ~1s (aceptable) vs 563ms (Llama) — diferencia no significativa para el usuario

### Cadena de fallback actualizada

```
1. Cache (BD)                         ← Hit: < 100ms
2. Groq GPT-OSS 120B (primario)      ← Miss: ~1s
3. Fake (concatenación de labels)     ← Si Groq falla (429 / error)
```

Se simplifica la cadena respecto a ADR-009: un solo modelo activo en vez de Flash + Flash Lite.
Si el volumen crece, cambiar a Llama 70B (12K TPM, mayor capacidad) es una variable de entorno.

### Sin cambios en código

Groq expone una API compatible con OpenAI (`/v1/chat/completions`). Se reutiliza
`RealOpenAIPhraseGenerator` existente apuntando a `OPENAI_BASE_URL=https://api.groq.com/openai/v1`.
No se necesita un `GroqPhraseGenerator` nuevo — esto valida de nuevo la extensibilidad de ADR-009.

### Impacto por capa

| Capa | Cambios |
|------|---------|
| **Domain** | 0 |
| **Application** | 0 |
| **Infrastructure** | 0 (solo variables de entorno) |

## Consecuencias

### Positivas

- **Latencia 5-10x menor** que Gemini (982ms vs 3.7s)
- **Ortografía española correcta** sin postprocesado
- **Free tier 25x mayor** (1.000 RPD vs 40 RPD)
- **Coste 4x menor** en output que Gemini ($0.60 vs $2.50 / M tokens)
- **Cero cambios en código** — solo configuración de entorno
- **Cortesía natural** en las frases generadas (pragmática comunicativa)

### Negativas

- **Dependencia de Groq:** Proveedor más reciente que Google/OpenAI
- **Latencia máxima puntual:** 2.6s en GPT-OSS (Llama no pasa de 804ms)

### Mitigaciones

- Cache en BD reduce las llamadas reales al LLM (misma combinación no repite petición)
- El mercado de LLM evoluciona rápidamente — nuevos modelos y proveedores pueden evaluarse
  en el futuro con la misma metodología de validación (126+ pruebas). Si el nuevo proveedor
  expone API compatible OpenAI, se cambia con variables de entorno. Si no, requiere un nuevo
  Generator en Infrastructure (Domain y Application sin cambios, como se demostró en ADR-009
  con Gemini)

## Alternativas Consideradas

### 1. Mantener Gemini como default

**Pros:** Ya configurado, proveedor consolidado (Google)<br>
**Contras:** Latencia 3.7s (límite de UX), ortografía española deficiente, free tier limitado (40 RPD)<br>
**Rechazo:** La validación con 126 pruebas demuestra que Groq es superior en todos los criterios medidos.

### 2. Groq Llama 3.3 70B como principal

**Pros:** Menor latencia absoluta (563ms media, 804ms máximo)<br>
**Contras:** Frases más directas (menos cortesía), 4x más caro que GPT-OSS en input<br>
**Rechazo:** GPT-OSS genera frases con cortesía natural que enseña pragmática comunicativa al usuario SAAC. La diferencia de latencia (400ms) no es perceptible.

## Referencias

- ADR-009: Soporte Multi-proveedor LLM (OpenAI + Gemini)
- [Validación del Prompt Tuning](../testing/prompt-tuning-validation.md) — 126 pruebas, 378 variaciones
- [Groq API](https://console.groq.com/docs/api-reference)
- [GPT-OSS 120B](https://console.groq.com/docs/models)
