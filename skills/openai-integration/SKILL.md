---
name: openai-integration
description: OpenAI API integration for pictogram prediction
license: MIT
compatibility: opencode
metadata:
  type: ai-integration
  api: openai
---

# SKILL: OpenAI API Integration

## 🎯 Uso en PictoSpeak AI
Predicción contextual de pictogramas usando GPT-4o-mini.

## ✅ Patrones

### A. Prompt Engineering para Predicción
```typescript
// application/service/PictogramPredictionService.ts
import OpenAI from 'openai';

const openai = new OpenAI({
  apiKey: import.meta.env.VITE_OPENAI_API_KEY,
});

export async function predictNextPictograms(
  context: string[],
  count: number = 5
): Promise<string[]> {
  const prompt = `Dado el contexto comunicativo: "${context.join(' ')}"
  
Sugiere ${count} palabras/conceptos que la persona probablemente quiera expresar a continuación.
Responde SOLO con un array JSON de strings, sin explicaciones.

Ejemplo: ["agua", "comer", "baño", "mamá", "dolor"]`;

  const response = await openai.chat.completions.create({
    model: 'gpt-4o-mini',
    messages: [{ role: 'user', content: prompt }],
    temperature: 0.3, // Baja creatividad, alta predictibilidad
    max_tokens: 100,
  });

  const suggestions = JSON.parse(response.choices[0].message.content);
  return suggestions;
}
```

### B. Error Handling & Fallbacks
```typescript
export async function predictWithFallback(
  context: string[]
): Promise<string[]> {
  try {
    return await predictNextPictograms(context);
  } catch (error) {
    console.error('OpenAI prediction failed:', error);
    
    // Fallback a predicciones frecuentes
    return getFrequentPictograms();
  }
}
```

### C. Rate Limiting & Caching
```typescript
import { rateLimit } from '@/infrastructure/cache/RateLimiter';

export const predictNextPictogramsLimited = rateLimit(
  predictNextPictograms,
  { maxCalls: 10, perMinutes: 1 }
);
```

## 🛡️ Security Checklist
- [ ] API Key en .env, NUNCA en código
- [ ] Rate limiting activado
- [ ] Input sanitization (máx 500 chars context)
- [ ] Timeout 5s para requests
- [ ] Logs SIN incluir API responses (pueden tener PII)