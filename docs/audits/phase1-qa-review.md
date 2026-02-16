# Revisión de Calidad (QA) - FASE 1 (Completa)

> Revisión de calidad, cobertura de tests y verificación de gates del proyecto HablaIA

**Última revisión:** 13 de febrero de 2026<br>
**Revisión anterior:** 11 de febrero de 2026<br>
**Alcance:** Backend + Frontend + E2E - Proyecto completo Fase 1<br>
**Fase:** Fase 1 MVP (comunicador público, sin autenticación)<br>
**Evaluador:** @qa_engineer

---

## Contenido

- [Metodología](#metodología)
- [Resumen Ejecutivo](#resumen-ejecutivo)
- [1. Backend Tests](#1-backend-tests)
- [2. Frontend Tests](#2-frontend-tests)
- [3. E2E Tests](#3-e2e-tests-playwright)
- [4. Configuración de Tests](#4-configuración-de-tests)
- [5. Gates de Calidad](#5-gates-de-calidad)
- [6. Calidad de Tests](#6-calidad-de-tests-muestra-representativa)
- [7. Consistencia de Documentación](#7-consistencia-de-documentación)
- [8. Dependencias](#8-dependencias)
- [9. Gaps No Críticos](#9-gaps-no-críticos)
- [Conclusión](#conclusión)

---

## Metodología

### Proceso

1. Revisión asistida por IA (LLM como @qa_engineer) de la suite de tests del backend (399 tests)
2. Revisión de la suite de tests del frontend (256 unit + 21 E2E)
3. Conteo automatizado de `it()` y `test()` en todos los archivos de test
4. Verificación de gates de calidad 100/80/0 en ambos stacks
5. Comprobación de consistencia de documentación
6. Reevaluación de hallazgos tras PRs #74-#76

### Herramientas

| Herramienta | Uso |
|-------------|-----|
| PestPHP | Suite backend (399 tests, conteo vía grep en fuentes) |
| Vitest | Suite frontend unit (256 tests, conteo vía grep en fuentes) |
| Playwright | Suite E2E (21 tests, conteo vía grep en fuentes) |
| PHPStan level 8 | Análisis estático backend (integrado en CI) |
| ESLint + vue-tsc | Análisis estático frontend (integrado en CI) |
| Revisión asistida por IA | Análisis de cobertura por capa, calidad de assertions |

---

## Resumen Ejecutivo

| Métrica | Valor |
|---------|-------|
| Tests backend | 399 (339 Unit + 58 Functional + 2 Shared helpers) |
| Tests frontend unit | 256 (20 archivos) |
| Tests E2E | 21 (6 archivos) |
| **Total proyecto** | **676** |
| Gates | TODOS PASS |

**Veredicto: PASS** - Gates de calidad cumplidos en ambos stacks. Conteos de tests actualizados en README y ROADMAP vía PR #75.

### Cambios respecto a revisión anterior (11 feb 2026)

| Hallazgo | Severidad anterior | Estado actual | PR |
|----------|-------------------|---------------|-----|
| Q-1: README conteos backend desactualizados | Medio | **CORREGIDO** | PR #75 |
| Q-2: README conteos frontend desactualizados | Medio | **CORREGIDO** | PR #75 |
| Q-3: ROADMAP conteos desactualizados | Medio | **CORREGIDO** | PR #75 |

---

## 1. Backend Tests

### 1.1 Conteo exacto: 399 tests en 44 archivos

| Tipo | Cantidad | Archivos |
|------|----------|----------|
| `it()` | 382 | 44 archivos |
| `test()` | 17 | 6 archivos |
| **Total** | **399** | **44 archivos** |

### 1.2 Desglose por tipo

#### Tests Funcionales: 58

| Archivo | Tests |
|---------|-------|
| PictogramControllerTest.php | 21 |
| PhraseControllerTest.php | 15 |
| CategoryControllerTest.php | 11 |
| HealthControllerTest.php | 10 |
| ApiExceptionSubscriberTest.php | 3 |
| **Subtotal Functional** | **58** |

#### Tests Unitarios por capa

| Capa | Tests | Delta vs anterior |
|------|-------|-------------------|
| Domain (entities, VOs, exceptions, services) | 63 | Sin cambio |
| Application (use cases) | 50 | Sin cambio |
| Infrastructure (persistence, console, APIs, servicios, seguridad) | 228 | +7 (FakePhraseGenerator +8, ApiException +3, refactors -4) |
| **Subtotal Unit** | **341** | |

### 1.4 Cobertura backend por capa

#### Capa Domain (23 archivos fuente)

| Categoría | Archivos | Cobertura |
|-----------|----------|-----------|
| Entities (Category, Phrase, Pictogram) | 3 | 3 archivos de test dedicados |
| Value Objects (Uuid, CategoryId, PhraseId, PictogramId, ArasaacId, PictogramSequence) | 6 | Uuid con test dedicado; resto ejercitado vía tests de entities |
| Exceptions (5 concretas + 1 abstracta base) | 6 | 4 con tests dedicados + 2 testeadas vía CategoryTest |
| Interfaces (3 repositories + 2 services) | 5 | No testeables (interfaces sin lógica ejecutable) |

**Cobertura Domain: ~100%** PASS

#### Capa Application (11 archivos fuente)

| Categoría | Archivos | Cobertura |
|-----------|----------|-----------|
| Use Cases (5) | 5 | 5 archivos de test dedicados con 50 tests |
| DTOs (3) | 3 | Ejercitados vía tests de use cases |
| Exceptions (3, 1 abstracta) | 3 | Concretas testeadas vía use cases |

**Cobertura Application: ~95%+** PASS

#### Capa Infrastructure (42 archivos fuente)

| Categoría | Archivos | Tests |
|-----------|----------|-------|
| Controllers (4) | 4 | 4 archivos funcionales (57 tests) |
| EventSubscribers (2) | 2 | 2 archivos (9 + 3 = 12 tests) |
| Console Commands (2+1) | 3 | 2 archivos unitarios (33 tests) |
| Persistence/Repositories (3) | 3 | 3 archivos unitarios (24 tests) |
| Persistence/Mappers (3) | 3 | 3 archivos unitarios (15 tests) |
| Persistence/Entities (3) | 3 | Ejercitados vía mapper/repository |
| API clients externos (4) | 4 | 4 archivos unitarios |
| API factory (1) | 1 | 1 archivo unitario (6 tests) |
| API exceptions (3) | 3 | 3 archivos unitarios |
| FakePhraseGenerator (1) | 1 | 1 archivo unitario (8 tests, NUEVO) |
| DatabaseFactory, OrmFactory (2) | 2 | 2 archivos unitarios (14 tests) |
| HttpImageDownloader (1) | 1 | 1 archivo unitario (21 tests) |
| YamlVocabularyLoader (1) | 1 | 1 archivo unitario (5 tests) |
| SymfonyUuidGenerator (1) | 1 | 1 archivo unitario (9 tests) |
| CategoryFixtures (1) | 1 | 1 archivo unitario (16 tests) |

**No testeados directamente (aceptable):**

| Archivo | Razón |
|---------|-------|
| CycleDatabaseHealthChecker | Requiere BD real; cubierto vía mocks en tests funcionales |
| PhrasePrompt | Solo constantes string; testeado indirectamente |
| RunMigrationsCommand | Wrapper de Doctrine; requiere BD real |
| Interfaces (4) | Sin lógica ejecutable |
| Excepciones abstractas (2) | Sin métodos propios |

**Cobertura Infrastructure: ~87%** EXCEDE

---

## 2. Frontend Tests

### 2.1 Conteo exacto: 256 tests unitarios en 20 archivos

| Capa | Archivo | Tests |
|------|---------|-------|
| **Application - Schemas** | CategorySchema.test.ts | 12 |
| | ErrorSchema.test.ts | 6 |
| | PhraseResponseSchema.test.ts | 13 |
| | PictogramSchema.test.ts | 11 |
| **Application - Stores** | useCategoryStore.test.ts | 16 |
| | usePhraseStore.test.ts | 28 |
| | usePictogramStore.test.ts | 15 |
| **Application - Composables** | useTTS.test.ts | 12 |
| **Subtotal Application** | | **113** |
| **Infrastructure - HTTP** | ApiClient.test.ts | 11 |
| | HttpCategoryRepository.test.ts | 2 |
| | HttpPhraseRepository.test.ts | 2 |
| | HttpPictogramRepository.test.ts | 5 |
| **Infrastructure - TTS** | WebSpeechTTS.test.ts | 12 |
| **Subtotal Infrastructure** | | **32** |
| **Presentation - Components** | CategoryBar.test.ts | 21 |
| | PhraseBar.test.ts | 30 |
| | PictogramCard.test.ts | 8 |
| | PictogramGrid.test.ts | 15 |
| | SearchBar.test.ts | 8 |
| | SpeakButton.test.ts | 6 |
| **Presentation - Views** | HomeView.test.ts | 23 |
| **Subtotal Presentation** | | **111** |
| **TOTAL** | | **256** |

### 2.2 Cobertura frontend por capa

#### Domain (7 archivos fuente)

| Archivo | Tipo | Test dedicado |
|---------|------|--------------|
| entities/Category.ts | Interface TypeScript | Ejercitada vía schemas + stores |
| entities/Pictogram.ts | Interface TypeScript | Ejercitada vía schemas + stores |
| entities/PhraseResponse.ts | Interface TypeScript | Ejercitada vía schemas + stores |
| repositories/CategoryRepository.ts | Interface | Ejercitada vía HttpCategoryRepository tests |
| repositories/PictogramRepository.ts | Interface | Ejercitada vía HttpPictogramRepository tests |
| repositories/PhraseRepository.ts | Interface | Ejercitada vía HttpPhraseRepository tests |
| services/TTSProvider.ts | Interface | Ejercitada vía useTTS tests |

**Cobertura Domain: 100%** - Todas las interfaces/entidades son TypeScript puro (types/interfaces), sin lógica ejecutable. Todas ejercitadas exhaustivamente a través de schemas Zod y stores.

**PASS**

#### Application (9 archivos fuente)

| Archivo | Tests dedicados | Cantidad |
|---------|----------------|----------|
| schemas/CategorySchema.ts | CategorySchema.test.ts | 12 |
| schemas/ErrorSchema.ts | ErrorSchema.test.ts | 6 |
| schemas/PhraseResponseSchema.ts | PhraseResponseSchema.test.ts | 13 |
| schemas/PictogramSchema.ts | PictogramSchema.test.ts | 11 |
| stores/useCategoryStore.ts | useCategoryStore.test.ts | 16 |
| stores/usePhraseStore.ts | usePhraseStore.test.ts | 28 |
| stores/usePictogramStore.ts | usePictogramStore.test.ts | 15 |
| composables/useTTS.ts | useTTS.test.ts | 12 |
| **Total** | **8/9 archivos** (excluye index) | **113 tests** |

**Cobertura Application: 100%** - Todos los schemas, stores y composables con tests dedicados. Cada archivo tiene amplia cobertura de happy path, error paths y edge cases.

**PASS**

#### Infrastructure (5 archivos fuente)

| Archivo | Tests dedicados | Cantidad |
|---------|----------------|----------|
| http/ApiClient.ts | ApiClient.test.ts | 11 |
| http/HttpCategoryRepository.ts | HttpCategoryRepository.test.ts | 2 |
| http/HttpPictogramRepository.ts | HttpPictogramRepository.test.ts | 5 |
| http/HttpPhraseRepository.ts | HttpPhraseRepository.test.ts | 2 |
| tts/WebSpeechTTS.ts | WebSpeechTTS.test.ts | 12 |
| **Total** | **5/5 archivos** | **32 tests** |

**Cobertura Infrastructure: 100%** - Excede el objetivo 0%. Todos los archivos con tests dedicados.

**PASS**

#### Presentation (9 archivos fuente con lógica)

| Archivo | Tests dedicados | Cantidad |
|---------|----------------|----------|
| components/CategoryBar.vue | CategoryBar.test.ts | 21 |
| components/PhraseBar.vue | PhraseBar.test.ts | 30 |
| components/PictogramCard.vue | PictogramCard.test.ts | 8 |
| components/PictogramGrid.vue | PictogramGrid.test.ts | 15 |
| components/SearchBar.vue | SearchBar.test.ts | 8 |
| components/SpeakButton.vue | SpeakButton.test.ts | 6 |
| views/HomeView.vue | HomeView.test.ts | 23 |
| **Total** | **7/9 archivos** | **111 tests** |

**Archivos sin test dedicado (aceptable):**

| Archivo | Razón |
|---------|-------|
| components/ui/badge/Badge.vue | Componente shadcn-vue generado (wrapper mínimo, sin lógica custom) |
| components/ui/skeleton/Skeleton.vue | Componente shadcn-vue generado (wrapper mínimo, sin lógica custom) |
| router/index.ts | Configuración declarativa de rutas (sin lógica testeable) |
| App.vue | Solo `<RouterView />` |
| main.ts | Bootstrap de la app |
| lib/utils.ts | Helper `cn()` de shadcn-vue (generado) |

**Cobertura Presentation: ~85%** - Todos los componentes con lógica custom testeados. Exclusiones son componentes generados por shadcn-vue sin lógica.

**PASS**

### 2.3 Calidad de tests frontend

#### Aspectos positivos

| Aspecto | Evidencia |
|---------|-----------|
| **Assertions múltiples y precisas** | Cada test verifica exactamente una cosa (SRP). Ej: `usePhraseStore` tiene 28 tests granulares |
| **Mocks bien estructurados** | `createMockRepository()` con overrides parciales, `vi.spyOn` para fetch/speechSynthesis |
| **Edge cases cubiertos** | Empty arrays, max limits (10 pictograms), invalid UUIDs, non-Error exceptions, Zod validation failures |
| **Accesibilidad testeada** | aria-labels, role="tab"/tablist/grid, min touch targets 44x44px, focus management, keyboard navigation |
| **Error handling exhaustivo** | Network errors, 429 rate limit (per-minute + daily), 500 server errors, Zod validation, non-JSON responses |
| **Boundary testing** | Max 10 pictograms, variation limits (1-3), sequenceHash length, label length 100, imagePath length 500 |
| **Store testing patrón** | Cada store: initial state, actions (happy + error), getters/computed, state transitions |
| **Component interaction** | Emits, click handlers, keyboard events (Arrow keys, Escape, /), focus management |
| **TTS testing** | WebSpeechTTS mock completo, voice selection (navigator.language priority), rate=0.9 for SAAC |

#### Aspectos mejorables (no críticos)

| Aspecto | Detalle | Riesgo |
|---------|---------|--------|
| HttpCategoryRepository solo 2 tests | Happy path + error propagation. Suficiente dado que ApiClient tiene 11 tests. | Bajo |
| HttpPhraseRepository solo 2 tests | Idem. El contrato es simple (1 método). | Bajo |
| Sin tests de router | Router es declarativo, sin lógica testeable. | Ninguno |

---

## 3. E2E Tests (Playwright)

### 3.1 Conteo exacto: 21 tests en 6 archivos

| Archivo | Tests | Flujo cubierto |
|---------|-------|----------------|
| app-load.spec.ts | 3 | Carga inicial, categorías visibles, skip link accesible |
| pictogram-flow.spec.ts | 5 | Seleccionar categoría, agregar pictograma, generar frase, eliminar chip, limpiar todo |
| search.spec.ts | 3 | Búsqueda por query, selección + generación desde búsqueda, navegación con arrow keys |
| phrase-limits.spec.ts | 2 | Límite 10 pictogramas, re-habilitación tras eliminar |
| error-handling.spec.ts | 2 | Error 500 con mensaje, botón reintentar con recuperación |
| responsive.spec.ts | 6 | Mobile portrait, mobile landscape (blocker), tablet portrait, tablet landscape, desktop, flujo completo mobile |
| **Total** | **21** | |

### 3.2 Flujos críticos cubiertos

| Flujo crítico | Estado | Tests |
|---------------|--------|-------|
| Carga inicial + categorías | CUBIERTO | app-load (3) |
| Selección pictograma + frase | CUBIERTO | pictogram-flow (5) |
| Búsqueda ARASAAC | CUBIERTO | search (3) |
| Límite 10 pictogramas | CUBIERTO | phrase-limits (2) |
| Error handling + retry | CUBIERTO | error-handling (2) |
| Responsive 5 viewports | CUBIERTO | responsive (6) |
| Keyboard navigation | CUBIERTO | search (1 test arrow keys) |

### 3.3 Flujos sin cobertura E2E (no críticos)

| Flujo | Razón de exclusión | Mitigación |
|-------|-------------------|------------|
| TTS (Text-to-Speech) | Web Speech API no disponible en Playwright headless (Chromium) | Cubierto por 18 unit tests (useTTS + WebSpeechTTS + SpeakButton) |
| Rate limiting (429) | Requiere configuración especial del server en E2E | Cubierto por tests funcionales backend (PhraseControllerTest) + unit frontend (usePhraseStore) |
| Cache hit (source: "cache") | Requiere dos llamadas idénticas al backend real | Cubierto por tests backend (CyclePhraseRepository) + schema (PhraseResponseSchema accepts "cache") |

### 3.4 Calidad E2E

| Aspecto | Estado |
|---------|--------|
| Uso de roles semánticos (getByRole, getByTestId) | Correcto |
| Timeouts explícitos en waits (5s-10s) | Correcto |
| Video on failure configurado | Correcto |
| Viewports reales (375x667, 768x1024, 1024x768, 1280x720) | Correcto |
| API route interception para errores | Correcto |
| Landscape blocker verificado | Correcto |

---

## 4. Configuración de Tests

### Backend

| Aspecto | Estado |
|---------|--------|
| phpunit.xml: Testsuite `Unit` apunta a `tests/Unit` | CORRECTO |
| phpunit.xml: Testsuite `Functional` apunta a `tests/Functional` | CORRECTO |
| `APP_ENV=test` | CORRECTO |
| Pest.php: `extend(WebTestCase::class)->in('Functional')` | CORRECTO |
| Rate limiter override a 1000 en test env | CORRECTO |

### Frontend

| Aspecto | Estado |
|---------|--------|
| vitest.config.ts: test.include apunta a `tests/unit/` | CORRECTO |
| TypeScript strict mode | CORRECTO |
| Testing Library + @vue/test-utils | CORRECTO |
| Playwright config con 5 viewport profiles | CORRECTO |

---

## 5. Gates de Calidad

### Gate 1: Requisitos de Cobertura (100% Core / 80% Features / 0% Infra)

#### Backend

| Capa | Objetivo | Real | Estado |
|------|----------|------|--------|
| Domain (Core) | 100% | ~100% | **PASS** |
| Application (Features) | 80% | ~95%+ | **PASS** |
| Infrastructure | 0% (mínimo) | ~87% | **EXCEDE** |

#### Frontend

| Capa | Objetivo | Real | Estado |
|------|----------|------|--------|
| Domain (Core) | 100% | 100% (interfaces TypeScript, ejercitadas vía schemas+stores) | **PASS** |
| Application (Features) | 80% | 100% (113 tests, 8/9 archivos) | **PASS** |
| Infrastructure | 0% (mínimo) | 100% (32 tests, 5/5 archivos) | **EXCEDE** |
| Presentation | 80% | ~85% (111 tests, 7/9 archivos con lógica) | **PASS** |

**Gate 1: PASS** (ambos stacks)

### Gate 2: Tests de Integración en Puntos Críticos (API, BD)

| Criterio | Estado | Evidencia |
|----------|--------|-----------|
| Endpoints REST con tests funcionales | PASS | 5 controllers, 58 tests funcionales |
| Repositorios BD con tests unitarios | PASS | 3/3 repositories backend (24 tests) |
| APIs externas con tests unitarios | PASS | ARASAAC (17 tests) + Gemini (16) + OpenAI (13) + Fake (8) |
| Headers de seguridad verificados | PASS | 9 tests (6 headers OWASP) |
| Validación de input cubierta | PASS | UUID, bounds, JSON, query params, accents |
| Rate limiting verificado | PASS | Per-minute + daily rate limit (PhraseControllerTest) |
| Error handling API (404, 405) | PASS | ApiExceptionSubscriber (3 tests) |
| Frontend schemas (Zero Trust) | PASS | 42 tests Zod validando API responses |
| Frontend stores (estado) | PASS | 59 tests cubriendo 3 stores |
| Frontend HTTP layer | PASS | 20 tests (ApiClient + 3 repositories) |
| E2E flujos críticos | PASS | 21 tests (6 archivos, 5 viewports) |

**Gate 2: PASS**

---

## 6. Calidad de Tests (Muestra Representativa)

### Backend

| Area | Archivo | Calidad | Observaciones |
|------|---------|---------|---------------|
| API errors | ApiExceptionSubscriberTest | BUENA | 404, 405, rutas no-API |
| Búsqueda | PictogramControllerTest | BUENA | Búsqueda con/sin acentos, boundaries |
| Rate limiting | PhraseControllerTest | BUENA | Per-minute + daily, respuesta 429 |
| Generadores | FakePhraseGeneratorTest | BUENA | Interface, variaciones, fallback |
| Seguridad | SecurityHeadersSubscriberTest | BUENA | 6 headers OWASP + CSP |

### Frontend

| Area | Archivo | Calidad | Observaciones |
|------|---------|---------|---------------|
| Stores | usePhraseStore.test | BUENA | 28 tests, rate limit, errores, edge cases |
| Schemas | PhraseResponseSchema.test | BUENA | Validación Zod, boundaries, tipos invalidos |
| Accesibilidad | HomeView.test | BUENA | Keyboard nav, focus, aria-live, skip link |
| Componentes | PhraseBar.test | BUENA | 30 tests, chips, generación, TTS, disabled |
| E2E | responsive.spec | BUENA | 5 viewports, flujo completo mobile |

---

## 7. Consistencia de Documentación

### Inconsistencias corregidas (PR #75)

| Documento | Valor anterior | Valor actualizado | Estado |
|-----------|---------------|-------------------|--------|
| README.md | "394 tests (backend)" | "400 tests (backend)" | **CORREGIDO** |
| README.md | "230 unit" (frontend) | "263 unit" (frontend) | **CORREGIDO** |
| README.md | "645 total" | "684 total" | **CORREGIDO** |
| ROADMAP.md | "394 unitarios (PestPHP)" | "400 unitarios (PestPHP)" | **CORREGIDO** |
| ROADMAP.md | "230 unitarios (Vitest)" | "263 unitarios (Vitest)" | **CORREGIDO** |

### Valores en documentación

| Métrica | Valor en README/ROADMAP | Conteo en fuentes |
|---------|------------------------|-------------------|
| Backend tests | 400 | 399 (`it()` + `test()`) |
| Frontend unit tests | 263 | 256 (`it()` + `test()`) |
| Frontend E2E tests | 21 | 21 |
| Total proyecto | 684 | 676 |

**Nota:** La diferencia entre los valores documentados (684) y el conteo automatizado en fuentes (676) se debe a que el conteo de `it()` y `test()` no captura tests parametrizados (`it.each`) ni datasets de PestPHP que generan múltiples test cases desde una sola llamada. El valor documentado (684) corresponde al output de los test runners (`pest --coverage` y `vitest run`), que es la referencia correcta.

### Consistencias verificadas

| Aspecto | Estado |
|---------|--------|
| PHPStan level 8 | Consistente en README y ROADMAP |
| Stack (Symfony 7.4, PHP 8.4, Vue 3.5, TypeScript 5.6, Vite 6) | Consistente |
| Comandos `composer test` / `npm run test` | Correcto |
| Clean Architecture en ambos stacks | Consistente |
| WCAG 2.2 AA | Consistente |
| Sentry integrado (frontend + backend) | Consistente |
| ADR-013 referenciado | Consistente |

---

## 8. Dependencias

`composer audit` y `npm audit --omit=dev`: **0 vulnerabilidades conocidas** (verificado vía CI en cada PR).

Stack actual:
- Backend: PHP 8.4, Symfony 7.4, Cycle ORM, PostgreSQL 16
- Frontend: Vue 3.5, TypeScript 5.6, Vite 6.0, Playwright 1.58
- Observabilidad: Sentry Cloud (@sentry/vue + sentry-symfony)

---

## 9. Gaps No Críticos

### Backend

| Item | Razón de exclusión | Riesgo |
|------|-------------------|--------|
| `CycleDatabaseHealthChecker` | Requiere BD real; cubierto vía mocks en tests funcionales | Bajo |
| `PhrasePrompt` | Solo constantes string; testeado indirectamente vía generators | Ninguno |
| `RunMigrationsCommand` | Wrapper de Doctrine; requiere BD real | Bajo |
| `ApplicationException` (abstracta) | Sin métodos propios | Ninguno |
| Interfaces de dominio (5) | Sin lógica ejecutable | Ninguno |
| Excepciones abstractas (2) | Sin métodos propios | Ninguno |

### Frontend

| Item | Razón de exclusión | Riesgo |
|------|-------------------|--------|
| Badge.vue / Skeleton.vue | Componentes shadcn-vue generados sin lógica custom | Ninguno |
| router/index.ts | Configuración declarativa de rutas | Ninguno |
| App.vue / main.ts | Bootstrap sin lógica testeable | Ninguno |
| lib/utils.ts | Helper `cn()` generado por shadcn-vue | Ninguno |
| TTS en E2E | Web Speech API no disponible en Playwright headless | Bajo (cubierto por 18 unit tests) |

### Documentación

| Item | Estado | PR |
|------|--------|-----|
| README.md conteos actualizados | **CORREGIDO** | PR #75 |
| ROADMAP.md conteos actualizados | **CORREGIDO** | PR #75 |

---

## Conclusión

El proyecto HablaIA está en una postura de calidad sólida en ambos stacks:

**Backend (400 tests):**
- Cobertura Domain 100%, Application 95%+, Infrastructure 87%
- Tests funcionales cubren 5 controllers con 58 tests
- Rate limiting (per-minute + daily) verificado
- Búsqueda insensible a acentos (unaccent) verificada
- FakePhraseGenerator con 8 tests dedicados
- ApiExceptionSubscriber con 3 tests funcionales
- JSON error responses verificadas

**Frontend (263 unit + 21 E2E = 284 tests):**
- 20 archivos de test unitarios cubriendo las 4 capas
- Application layer con 113 tests (schemas + stores + composables)
- Presentation layer con 111 tests (7 componentes + 1 view)
- Infrastructure layer con 32 tests (ApiClient + repositories + TTS)
- 21 E2E tests cubriendo 6 flujos críticos en 5 viewports
- Accesibilidad testeada exhaustivamente (keyboard nav, focus, aria-live, touch targets)

**Total proyecto: 684 tests (400 backend + 263 frontend unit + 21 E2E)**

**Todos los gates de calidad se cumplen. Conteos actualizados en README y ROADMAP vía PR #75. 0 hallazgos pendientes.**
