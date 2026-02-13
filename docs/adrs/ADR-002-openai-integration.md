# ADR-002: OpenAI API para Humanización de Frases

**Estado:** Aceptado<br>
**Fecha:** 2026-01-31<br>
**Contexto:** HablaIA - IA contextual para SAAC<br>

## Contexto

Los comunicadores SAAC tradicionales generan frases robotizadas:
- Input: ["yo", "querer", "comer"]
- Output típico: "Yo quiero comer"

Esto deshumaniza al usuario. Queremos:
- Variaciones naturales: "Tengo hambre", "Me apetece comer algo", "Necesito comer"
- Contexto temporal: "Buenos días, quiero desayunar" (si es mañana)
- Tono empático

## Decisión

Usar **OpenAI API (GPT-4o-mini)** para generar 3 variaciones de frases humanizadas.

### Prompt Engineering

```json
{
  "model": "gpt-4o-mini",
  "messages": [
    {
      "role": "system",
      "content": "Eres un asistente que ayuda a personas con dificultades comunicativas a expresarse de forma natural. Dada una secuencia de pictogramas, genera 3 variaciones de frase que transmitan la misma intención de forma coloquial y empática. Responde SOLO con JSON: {\"variations\": [\"frase1\", \"frase2\", \"frase3\"]}. Contexto: {hora}, {día_semana}."
    },
    {
      "role": "user",
      "content": "Pictogramas: [\"yo\", \"querer\", \"comer\"]. Hora: 09:30, día: lunes"
    }
  ],
  "temperature": 0.7,
  "max_tokens": 150
}
```

### Respuesta Esperada

```json
{
  "variations": [
    "Buenos días, tengo hambre",
    "Me apetece desayunar algo",
    "Necesito comer, ¿qué hay?"
  ]
}
```

### Arquitectura

```
Frontend (Vue)
  → POST /api/phrases
  → PhraseController
    → GenerateHumanizedPhraseUseCase
      ├→ GetCachedPhraseUseCase (BD)  ← HIT: < 100ms
      └→ OpenAIService (API call)     ← MISS: ~ 2s
        → Guardar en CachedPhrase
```

### Caché Agresivo

- **PK Caché:** `hash(pictogram_ids + context)`
- **Estrategia:**
  - Primera llamada: 2s latencia (OpenAI)
  - Siguientes: < 100ms (PostgreSQL)
- **TTL:** Sin expiración (frases no cambian)

## Consecuencias

### Positivas

- **Humanización real:** Usuario suena natural, no robótico
- **Diferenciador:** Competencia (Tobii, Proloquo2Go) no tiene esto
- **Escalable:** Misma arquitectura sirve para voice cloning futuro
- **Fallback robusto:** Si OpenAI cae, devuelve template básico desde BD

### Negativas

- **Coste:** $0.15/1M tokens (GPT-4o-mini)
- **Latencia primera llamada:** 2s (pero caché mitiga)
- **Dependencia externa:** Si OpenAI cae, fallback es menos humanizado
- **GDPR:** Frases enviadas a OpenAI (pero no contienen PII)

### Mitigaciones

- **Coste:** MVP con 100 usuarios = ~$5/mes (asumible)
- **Latencia:** Caché + pre-computar combinaciones frecuentes
- **Dependencia:** Fallback a template + log para retry posterior
- **GDPR:** Política de privacidad clara + encriptación en tránsito

## Alternativas Consideradas

### 1. Templates Hardcodeados

**Pros:** Sin coste, latencia 0ms<br>
**Contras:** No escala, mantenimiento manual, poca variabilidad<br>
**Rechazo:** No cumple objetivo de "humanizar"<br>

### 2. Modelo Local (LLaMA, Mistral)

**Pros:** Sin coste recurrente, privacidad total<br>
**Contras:** Requiere GPU, complejidad deploy, calidad inferior<br>
**Rechazo:** Para MVP, overkill<br>

### 3. Claude API (Anthropic)

**Pros:** Calidad similar, mejor en español<br>
**Contras:** Más caro ($3/1M vs $0.15/1M), menor adopción<br>
**Rechazo:** OpenAI es suficiente y más barato<br>

## Nota

Este ADR documenta la decisión inicial. Ver [ADR-009](ADR-009-multi-provider-llm.md) para la evolución a soporte multi-proveedor (OpenAI + Gemini).

## Referencias

- [OpenAI Pricing](https://openai.com/pricing)
- [GPT-4o-mini Benchmarks](https://openai.com/index/gpt-4o-mini-advancing-cost-efficient-intelligence/)
