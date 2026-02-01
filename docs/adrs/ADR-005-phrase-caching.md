# ADR-005: Caché de Frases Generadas por IA

**Estado:** Aceptado
**Fecha:** 2026-01-31
**Contexto:** HablaIA - Performance y costes OpenAI

## Contexto

Generación de frases con OpenAI:
- Latencia: ~2 segundos
- Coste: $0.15/1M tokens (GPT-4o-mini)
- Problema: Misma combinación de pictogramas se repite constantemente

Ejemplo real de uso:
- Usuario selecciona ["yo", "querer", "comer"] 30 veces/día
- Sin caché: 30 llamadas a OpenAI = 60s latencia total + $0.0045/día
- Con caché: 1 llamada + 29 hits = 2s + 2.9s = 4.9s total + $0.00015/día

**30x menos latencia, 30x menos coste**

## Decisión

Implementar **caché agresivo en PostgreSQL** con las siguientes reglas:

### Estructura de Tabla

```sql
CREATE TABLE cached_phrases (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cache_key VARCHAR(64) UNIQUE NOT NULL,  -- hash(pictogram_ids + context)
    pictogram_ids INTEGER[] NOT NULL,       -- [1, 5, 23]
    context_hour INTEGER,                    -- 0-23 (NULL = sin contexto)
    context_day_of_week INTEGER,            -- 1-7 (NULL = sin contexto)
    variations JSONB NOT NULL,              -- ["frase1", "frase2", "frase3"]
    created_at TIMESTAMP DEFAULT NOW(),
    hit_count INTEGER DEFAULT 0,            -- Contador de uso
    INDEX idx_cache_key (cache_key),
    INDEX idx_hit_count (hit_count DESC)    -- Para analytics
);
```

### Algoritmo de Caché

```php
// application/usecase/GenerateHumanizedPhraseUseCase.php

public function execute(PhraseRequestDTO $request): PhraseResponseDTO
{
    // 1. Generar cache key
    $cacheKey = $this->generateCacheKey(
        $request->pictogramIds,
        $request->contextHour,
        $request->contextDayOfWeek
    );

    // 2. Buscar en caché
    $cached = $this->cachedPhraseRepository->findByCacheKey($cacheKey);

    if ($cached) {
        // CACHE HIT - Latencia < 100ms
        $this->cachedPhraseRepository->incrementHitCount($cached->id);

        return new PhraseResponseDTO(
            variations: $cached->variations,
            source: 'cache',
            latency: 50  // ms
        );
    }

    // 3. CACHE MISS - Llamar OpenAI
    try {
        $variations = $this->openAIService->generatePhraseVariations(
            pictogramIds: $request->pictogramIds,
            context: [
                'hour' => $request->contextHour,
                'dayOfWeek' => $request->contextDayOfWeek
            ]
        );

        // 4. Guardar en caché para futuras llamadas
        $this->cachedPhraseRepository->create([
            'cache_key' => $cacheKey,
            'pictogram_ids' => $request->pictogramIds,
            'context_hour' => $request->contextHour,
            'context_day_of_week' => $request->contextDayOfWeek,
            'variations' => $variations,
        ]);

        return new PhraseResponseDTO(
            variations: $variations,
            source: 'openai',
            latency: 2000  // ms
        );

    } catch (OpenAIException $e) {
        // 5. FALLBACK - Generar template básico
        $template = $this->generateBasicTemplate($request->pictogramIds);

        return new PhraseResponseDTO(
            variations: [$template],
            source: 'fallback',
            latency: 10
        );
    }
}

private function generateCacheKey(
    array $pictogramIds,
    ?int $contextHour,
    ?int $contextDayOfWeek
): string {
    $data = implode(',', $pictogramIds) .
            '|' . ($contextHour ?? 'null') .
            '|' . ($contextDayOfWeek ?? 'null');

    return hash('sha256', $data);
}
```

### Política de Invalidación

**Regla: NUNCA invalidar**

Razones:
1. Frases no cambian en el tiempo (["yo", "querer", "comer"] siempre significa lo mismo)
2. Contexto ya está en cache_key (hora/día ya diferenciado)
3. Espacio disco barato (1M entradas = ~500MB)

**Excepción:** Botón manual "Regenerar variaciones" (admin/power users)

## Consecuencias

### Positivas

- **Performance:** Cache hit < 100ms vs 2s de OpenAI (20x más rápido)
- **Costes:** Reduce llamadas a OpenAI en 95% después de 1 semana de uso
- **Resiliencia:** Si OpenAI cae, 95% de frases siguen funcionando
- **Analytics:** `hit_count` permite saber frases más usadas

### Negativas

- **Cold start:** Primera semana = muchos misses (pero se autocorrige)
- **Espacio disco:** 1M combinaciones = 500MB (asumible)
- **Frases obsoletas:** Si mejoramos prompt, caché tiene versiones viejas

### Mitigaciones

- **Cold start:** Pre-computar top 100 combinaciones frecuentes en seed
- **Espacio:** PostgreSQL compression + particionado por fecha
- **Obsoletas:** Botón admin "Regenerar todas" (async job)

## Alternativas Consideradas

### 1. Redis (in-memory cache)

**Pros:** Latencia < 1ms, muy rápido
**Contras:** Volátil (restart = pérdida), coste RAM (1M entries = 2GB)
**Rechazo:** PostgreSQL suficiente, persistencia > velocidad

### 2. Sin caché (always call OpenAI)

**Pros:** Siempre fresco, sin gestión caché
**Contras:** Latencia 2s constante, $300/mes para 1K usuarios
**Rechazo:** UX inaceptable + coste prohibitivo

### 3. Cache con TTL (expiración 7 días)

**Pros:** Frases siempre actualizadas
**Contras:** Cache misses recurrentes, no mejora con el tiempo
**Rechazo:** Frases no son time-sensitive

## Métricas de Éxito

Después de 1 mes en producción:
- [ ] Cache hit rate > 90%
- [ ] Latencia p95 < 200ms
- [ ] Coste OpenAI < $10/mes (1K usuarios)

## Referencias

- [PostgreSQL JSONB Performance](https://www.postgresql.org/docs/current/datatype-json.html)
- [Cache Patterns](https://aws.amazon.com/caching/best-practices/)
