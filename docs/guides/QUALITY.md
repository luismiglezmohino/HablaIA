# Quality (QA) - HablaIA

> Resumen de postura de calidad. Informe detallado en [auditoría de calidad — Fase 1](../audits/phase1-qa-review.md)

**Última revisión:** 13 de febrero de 2026
**Scope:** Backend + Frontend + E2E
**Fase:** Fase 1 MVP

---

## Resumen de tests

| Stack | Tests | Detalle |
|-------|-------|---------|
| Backend (PestPHP) | 400 | 339 Unit + 58 Functional + 3 Shared |
| Frontend (Vitest) | 263 | 20 archivos, 4 capas |
| E2E (Playwright) | 21 | 6 archivos, 5 viewports |
| **Total** | **684** | |

**Veredicto: PASS** - Todos los gates de calidad cumplidos. 0 hallazgos pendientes.

---

## Gates de calidad (100/80/0)

### Backend

| Capa | Objetivo | Real | Estado |
|------|----------|------|--------|
| Domain (Core) | 100% | ~100% | PASS |
| Application (Features) | 80% | ~95% | PASS |
| Infrastructure | 0% min | ~87% | EXCEDE* |

### Frontend

| Capa | Objetivo | Real | Estado |
|------|----------|------|--------|
| Domain (Core) | 100% | 100% | PASS |
| Application (Features) | 80% | 100% | PASS |
| Infrastructure | 0% min | 100% | EXCEDE* |
| Presentation | 80% | ~85% | PASS |

*\*En un comunicador SAAC, un fallo en infraestructura (HTTP, TTS, API externa) deja al usuario sin capacidad de comunicarse. Por eso la cobertura excede deliberadamente el mínimo del gate.*

---

## Cobertura de flujos críticos

| Flujo | Unit | Functional | E2E |
|-------|------|------------|-----|
| Carga categorías | Stores + schemas | CategoryController (11) | app-load (3) |
| Selección pictogramas + frase | Stores + components | PhraseController (15) | pictogram-flow (5) |
| Búsqueda ARASAAC | Stores + SearchBar | PictogramController (21) | search (3) |
| Límite 10 pictogramas | PhraseBar + stores | PhraseController | phrase-limits (2) |
| Error handling + retry | Stores + components | ApiExceptionSubscriber (3) | error-handling (2) |
| Responsive 5 viewports | - | - | responsive (6) |
| TTS | useTTS + WebSpeechTTS (18) | - | No disponible en headless |
| Rate limiting | usePhraseStore | PhraseController (per-min + daily) | - |
| Keyboard navigation | HomeView + Grid | - | search (arrow keys) |

---

## Análisis estático

| Herramienta | Nivel | Integrado en CI |
|-------------|-------|-----------------|
| PHPStan | Level 8 | Sí |
| ESLint | Strict | Sí |
| vue-tsc | `--noEmit` | Sí |
| `composer audit` | 0 vulnerabilidades | Sí |
| `npm audit` | 0 vulnerabilidades | Sí |

---

## Pendiente para fases posteriores

- **Coverage numérico** con Istanbul/c8 (actualmente cobertura verificada por capa)
- **Mutation testing** con Stryker o Infection
- **Tests de carga** con k6 (p95 API response time)
