# Performance - HablaIA

> Resumen de postura de rendimiento. Informe detallado en [`docs/audits/phase1-performance-audit.md`](audits/phase1-performance-audit.md)

**Ultima revision:** 13 de febrero de 2026<br>
**Scope:** Full stack - Backend Symfony 7.4 + Frontend Vue 3.5 + Docker/Nginx<br>
**Fase:** Phase 1 MVP<br>

---

## Resumen por area

| Area | Estado | Notas |
|------|--------|-------|
| Frontend Bundle | BUENO | Initial JS 92KB (~33KB gzip). Fuentes Inter optimizadas a latin subset. Sourcemaps ocultos |
| Frontend Runtime | BUENO | Stores ligeros, debounce 300ms, lazy loading imagenes. Sin virtual scroll (< 30 items/grid) |
| Backend API | BUENO | Cache SHA256 frases, rate limiting, indices SQL, N+1 corregido con `findByIds()` |
| Docker/Infra | BUENO | Gzip habilitado, cache headers correctos (assets 1y, pictograms 7d, index.html no-cache), OPcache |
| Core Web Vitals | MEDIDO | Lighthouse produccion: Mobile 95, Desktop 99, Accessibility 100/100 |

**Veredicto: BUENO para Fase 1 MVP** - 7 hallazgos corregidos (PRs #71, #72). 0 hallazgos pendientes.

---

## Lighthouse produccion

Medido el 13 de febrero de 2026 contra servidor Hetzner CX33. Evidencia en `docs/audits/lighthouse/fase1/`.

| Categoria | Mobile | Desktop |
|-----------|--------|---------|
| Performance | **95** | **99** |
| Accessibility | **100** | **100** |
| Best Practices | 78 | 78 |
| SEO | 91 | 91 |

### Core Web Vitals

| Metrica | Mobile | Desktop | Target | Estado |
|---------|--------|---------|--------|--------|
| LCP | 2.4s | 0.8s | < 2.5s | OK |
| TBT | 10ms | 0ms | < 200ms | Excelente |
| CLS | 0.018 | 0.001 | < 0.1 | Excelente |
| FCP | 2.2s | 0.7s | < 1.8s | Desktop OK |

Best Practices 78 por uso de HTTP sin TLS (pendiente).

---

## Optimizaciones implementadas

### Frontend

- **Bundle splitting:** Initial chunk 92KB + lazy HomeView 105KB. Total ~76KB gzip
- **Fuentes Inter:** Solo subset latin (4 archivos woff2, ~97KB vs 56 archivos ~898KB antes)
- **Lazy loading:** `loading="lazy"` en imagenes de pictogramas
- **Debounce:** 300ms en SearchBar para evitar peticiones excesivas

### Backend

- **Cache SHA256:** Frases cacheadas por hash de pictogramas seleccionados. Misma combinacion no consume request LLM
- **Indices SQL:** En todas las columnas de busqueda (migraciones V003/V004)
- **N+1 corregido:** `findByIds()` con query `WHERE IN` en validacion de pictogramas

### Infraestructura

- **Gzip Nginx:** Habilitado para HTML, CSS, JS, JSON, XML, SVG
- **Cache headers:** Assets 1 ano, pictogramas 7 dias, index.html no-cache
- **OPcache:** Configurado con `validate_timestamps=0` en produccion
- **Multi-stage Docker:** Node.js solo en build, no en imagen final

---

## Pendiente para fases posteriores

- **HTTPS/TLS** (mejoraria Best Practices y habilitaria HTTP/2)
- **Load testing** con k6 o Artillery (p95 API response time)
- **PostgreSQL tuning** si el volumen de datos crece significativamente
