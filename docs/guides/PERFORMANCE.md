# Performance - HablaIA

> Resumen de postura de rendimiento. Informe detallado en [auditoría de rendimiento — Fase 1](../audits/phase1-performance-audit.md)

**Última revisión:** 21 de febrero de 2026<br>
**Scope:** Full stack - Backend Symfony 7.4 + Frontend Vue 3.5 + Docker/Nginx<br>
**Fase:** Fase 1 MVP<br>

---

## Resumen por área

| Área | Estado | Notas |
|------|--------|-------|
| Frontend Bundle | BUENO | Initial JS 92KB (~33KB gzip). Fuentes Inter optimizadas a latín subset. Sourcemaps ocultos |
| Frontend Runtime | BUENO | Stores ligeros, debounce 300ms, lazy loading imágenes. Sin virtual scroll (< 30 items/grid) |
| Backend API | BUENO | Caché SHA256 frases, rate limiting, índices SQL, N+1 corregido con `findByIds()` |
| Docker/Infra | BUENO | Gzip habilitado, cache headers correctos (assets 1y, pictograms 7d, index.html no-cache), OPcache |
| Core Web Vitals | MEDIDO | Lighthouse producción: Mobile 98, Desktop 100, Accessibility 100/100, Best Practices 100/100 |

**Veredicto: BUENO para Fase 1 MVP** - 7 hallazgos corregidos (PRs #71, #72). 0 hallazgos pendientes.

---

## Lighthouse producción

Medido el 21 de febrero de 2026 contra servidor Hetzner CX33 con HTTPS (Let's Encrypt). Evidencia en [`docs/audits/lighthouse/https/`](../audits/lighthouse/https/).

| Categoría | Mobile | Desktop |
|-----------|--------|---------|
| Performance | **98** | **100** |
| Accessibility | **100** | **100** |
| Best Practices | **100** | **100** |
| SEO | **91** | **91** |

### Core Web Vitals

| Métrica | Mobile | Desktop | Target | Estado |
|---------|--------|---------|--------|--------|
| LCP | 2.0s | 0.5s | < 2.5s | OK |
| TBT | 0ms | 0ms | < 200ms | Excelente |
| CLS | 0.018 | 0.001 | < 0.1 | Excelente |

---

## Optimizaciones implementadas

### Frontend

- **Bundle splitting:** Initial chunk 92KB + lazy HomeView 105KB. Total ~76KB gzip
- **Fuentes Inter:** Solo subset latín (4 archivos woff2, ~97KB vs 56 archivos ~898KB antes)
- **Lazy loading:** `loading="lazy"` en imágenes de pictogramas
- **Debounce:** 300ms en SearchBar para evitar peticiones excesivas

### Backend

- **Caché SHA256:** Frases cacheadas por hash de pictogramas seleccionados. Misma combinación no consume request LLM
- **Índices SQL:** En todas las columnas de búsqueda (migraciones V003/V004)
- **N+1 corregido:** `findByIds()` con query `WHERE IN` en validación de pictogramas

### Infraestructura

- **Gzip Nginx:** Habilitado para HTML, CSS, JS, JSON, XML, SVG
- **Cache headers:** Assets 1 año, pictogramas 7 días, index.html no-cache
- **OPcache:** Configurado con `validate_timestamps=0` en producción
- **Multi-stage Docker:** Node.js solo en build, ausente en imagen final

---

## Pendiente para fases posteriores

- **Load testing** con k6 o Artillery (p95 API response time)
- **PostgreSQL tuning** si el volumen de datos crece de forma significativa
