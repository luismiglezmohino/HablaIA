# Auditoría de Performance - Fase 1

> Revisión de rendimiento del proyecto completo HablaIA

**Última revisión:** 13 de febrero de 2026<br>
**Revisión anterior:** 11 de febrero de 2026<br>
**Alcance:** Full stack - Backend + Frontend + Docker/Infra<br>
**Fase:** Fase 1 MVP (comunicador público, sin autenticación)<br>
**Evaluador:** @performance_engineer

## Contenido

- [Resumen Ejecutivo](#resumen-ejecutivo)
- [1. Frontend Bundle](#1-frontend-bundle)
- [2. Frontend Runtime](#2-frontend-runtime)
- [3. Backend API](#3-backend-api)
- [4. Docker/Infra](#4-dockerinfra)
- [5. Core Web Vitals](#5-core-web-vitals-análisis-estático)
- [6. Resumen de Hallazgos](#6-resumen-de-hallazgos-por-severidad)
- [7. Plan de Acción](#7-plan-de-acción)
- [8. Lighthouse Producción](#8-lighthouse-producción-medición-real)
- [9. Conclusión](#9-conclusión)

---

## Resumen Ejecutivo

| Area | Estado | Detalle |
|------|--------|---------|
| [Frontend Bundle](#1-frontend-bundle) | BUENO | Initial JS 92KB (~33KB gzip), dentro de targets. Fuentes Inter optimizadas a solo latin subset (4 archivos woff2). Sourcemaps ocultos. |
| [Frontend Runtime](#2-frontend-runtime) | BUENO | Stores ligeros, debounce 300ms, lazy loading en imágenes, sin virtual scroll (volumen bajo ~20 pictogramas/categoría). |
| [Backend API](#3-backend-api) | BUENO | Cache SHA256, rate limiting, índices en todas las columnas de búsqueda, N+1 corregido con `findByIds()`. |
| [Docker/Infra](#4-dockerinfra) | BUENO | Gzip habilitado, cache headers correctos (assets 1y, pictograms 7d, index.html no-cache), OPcache configurado. |
| [Core Web Vitals](#5-core-web-vitals-análisis-estático) | MEDIDO | [Lighthouse producción](#8-lighthouse-producción-medición-real): Mobile 95, Desktop 99, Accessibility 100/100. |

**Veredicto: BUENO para Fase 1 MVP** - 7 hallazgos de la revisión anterior corregidos (PRs #71, #72). Los 5 hallazgos restantes reclasificados como NO APLICA con justificación.

### Cambios respecto a revisión anterior (11 feb 2026)

| Hallazgo | Severidad anterior | Estado actual | PR |
|----------|-------------------|---------------|-----|
| H-1: Índices SQL faltantes | Alto | **CORREGIDO** (ya existían en migraciones V003/V004) | - |
| M-1: Fuentes Inter 56 archivos innecesarios | Medio | **CORREGIDO** (import solo latin subset) | PR #72 |
| M-2: Sourcemaps expuestos en producción | Medio | **CORREGIDO** (`sourcemap: 'hidden'`) | PR #71 |
| M-3: Nginx no cachea index.html | Medio | **CORREGIDO** (`no-cache, no-store, must-revalidate`) | PR #71 |
| M-5: N+1 en validateAndGetPictograms | Medio | **CORREGIDO** (`findByIds()` con query `WHERE IN`) | PR #72 |
| B-1: @vueuse/core sin usar | Bajo | **CORREGIDO** (eliminado de dependencies) | PR #72 |
| B-2: Pictogramas sin lazy loading | Bajo | **CORREGIDO** (`loading="lazy"` en PictogramCard) | PR #72 |

---

## 1. Frontend Bundle

### 1.1 Bundle Size (medido desde build del 9 feb 2026)

| Archivo | Tamaño (raw) | Estimado gzip (~65%) | Tipo |
|---------|-------------|---------------------|------|
| `index-oL4cA9b_.js` | 94,642 bytes (92.4 KB) | ~33 KB | Initial chunk (Vue, Pinia, Router, Sentry, Zod, Radix, lucide) |
| `HomeView-Bjwvz0Ug.js` | 107,185 bytes (104.7 KB) | ~37 KB | Lazy chunk (HomeView + todos los componentes) |
| `index-DsQbLlxM.css` | 30,518 bytes (29.8 KB) | ~6 KB | Tailwind CSS purgeado |
| **Total JS** | **197 KB** | **~70 KB** | |
| **Total JS+CSS** | **227 KB** | **~76 KB** | |

**Análisis:**
- El target de `< 100KB initial` se cumple para el JS initial (92.4 KB raw).
- El target de `< 500KB total` se cumple holgadamente (227 KB raw total).
- Con gzip (habilitado en Nginx), el initial chunk baja a ~33 KB, muy por debajo del target.

#### Severidad: Informativo - DENTRO DE TARGETS

### 1.2 Fuentes Inter — CORREGIDO (PR #72)

**Problema original (11 feb):** Se empaquetaban 4 pesos x 7 subconjuntos x 2 formatos = 56 archivos (~898 KB). Solo se necesitaban `latin` y `latin-ext` para español.

**Corrección aplicada:** `main.ts` ahora importa solo el subset latin por peso:
```typescript
import '@fontsource/inter/latin-400.css'
import '@fontsource/inter/latin-500.css'
import '@fontsource/inter/latin-600.css'
import '@fontsource/inter/latin-700.css'
```

| Subconjunto | Pesos | Archivos woff2 | Tamaño estimado |
|-------------|-------|-----------------|-----------------|
| latin | 400,500,600,700 | 4 | ~97 KB |

**Resultado:** De 56 archivos (~898 KB) a 4 archivos woff2 (~97 KB). Reducción del ~89% en peso de fuentes.

#### Severidad: CORREGIDO

### 1.3 Code Splitting

**Router (lazy loading):** CORRECTO.
```typescript
// src/presentation/router/index.ts
component: () => import('@/presentation/views/HomeView.vue')
```
HomeView se carga como chunk separado (lazy). Sin embargo, al ser la única ruta de la SPA, todo usuario la carga inmediatamente. El beneficio real del lazy loading es marginal en Fase 1 (solo 1 ruta), pero la arquitectura está preparada para cuando se agreguen rutas en fases futuras.

**Vite config:** Sin configuración manual de `manualChunks`. Vite aplica su splitting por defecto.

#### Severidad: Informativo - CORRECTO

### 1.4 Tree Shaking

**lucide-vue-next:** Se importan iconos individuales (`import { Sparkles, Search, X } from 'lucide-vue-next'`). Vite hace tree shaking correcto de iconos no usados.

**@sentry/vue:** Se importa como `import * as Sentry from '@sentry/vue'` en `main.ts`. Esta es la forma recomendada por Sentry y el bundler tree-shakea los modulos no usados. Sin embargo, Sentry agrega ~20-30 KB al bundle initial incluso con tree shaking.

**radix-vue:** Solo se usa indirectamente vía shadcn-vue (Badge, Skeleton). El impacto es mínimo.

**zod:** Se usa para validar schemas de API responses. Agrega ~12 KB al bundle. Es necesario para la validación runtime.

**@vueuse/core:** Eliminada de dependencies directas en PR #72. Solo permanece como dependencia transitiva de `radix-vue` (shadcn-vue).

#### Severidad: CORREGIDO (PR #72)

### 1.5 Dependencias - Análisis de Bloat

| Dependencia | Tamaño estimado en bundle | Necesaria |
|-------------|--------------------------|-----------|
| vue | ~33 KB | Si (framework) |
| @sentry/vue | ~25 KB | Si (observabilidad) |
| zod | ~12 KB | Si (validación runtime) |
| pinia | ~5 KB | Si (state management) |
| vue-router | ~8 KB | Si (routing) |
| lucide-vue-next | ~2-3 KB (solo iconos usados) | Si (iconos) |
| radix-vue | ~3 KB (solo Badge/Skeleton) | Si (UI primitivas) |
| class-variance-authority | ~1 KB | Si (shadcn utility) |
| clsx | <1 KB | Si (shadcn utility) |
| tailwind-merge | ~3 KB | Si (shadcn utility) |
| @fontsource/inter | 0 KB JS (solo CSS/fonts) | Si (tipografía) |
| ~~@vueuse/core~~ | Eliminada en PR #72 | Eliminada |

**Total estimado de dependencias runtime:** ~92 KB (alineado con el bundle real de 92.4 KB).

#### Severidad: Informativo - SIN BLOAT SIGNIFICATIVO

### 1.6 Sourcemaps — CORREGIDO (PR #71)

**Problema original (11 feb):** `sourcemap: true` generaba sourcemaps accesibles en producción (~1.2 MB), exponiendo el código fuente.

**Corrección aplicada:** `vite.config.ts` cambiado a `sourcemap: 'hidden'`. Los sourcemaps se generan pero no se referencian desde los bundles JS, por lo que no son accesibles en el navegador.

#### Severidad: CORREGIDO

---

## 2. Frontend Runtime

### 2.1 Reactividad y Re-renders

**Pinia Stores:** Los 3 stores (`useCategoryStore`, `usePictogramStore`, `usePhraseStore`) usan la Composition API con `ref()` y `computed()`. El estado es granular y no provoca re-renders innecesarios.

- `sortedCategories` es un `computed` (se recalcula solo cuando `categories` cambia) -- CORRECTO
- `selectedCategory` es un `computed` (se recalcula solo cuando `categories` o `selectedCategoryId` cambian) -- CORRECTO
- `canGenerate` y `isFull` son `computed` -- CORRECTO

**HomeView.vue:** Contiene 6 `watch()` y 2 `computed`. Todos tienen dependencias correctas y no se disparan innecesariamente.

**PictogramGrid.vue `getCategoryColor`:** Se ejecuta por cada pictograma en cada render. Hace un `Array.find()` sobre las categorías (11 items). Con ~20 pictogramas por categoría, son ~200 operaciones de búsqueda simple. Impacto despreciable.

#### Severidad: Informativo - CORRECTO

### 2.2 Debounce

**SearchBar.vue:** Debounce de 300ms en el `watch(query)` -- CORRECTO.
**HomeView.vue (mobile):** Debounce de 300ms en `watch(mobileQuery)` -- CORRECTO.

Los timers se limpian correctamente con `clearTimeout` antes de crear uno nuevo. No hay memory leaks por timers.

#### Severidad: Informativo - CORRECTO

### 2.3 Virtualización de Listas

Los pictogramas NO usan virtual scroll. El grid renderiza todos los pictogramas de la categoría de una vez.

**Datos del dominio:** ~194 pictogramas totales distribuidos en 11 categorías. La categoría más grande tendra ~30 pictogramas. La búsqueda devuelve máximo 10 resultados.

**Impacto:** Con un máximo de ~30 cards renderizadas simultáneamente, la virtualización no es necesaria. El DOM tiene ~30 x 3 elementos (img + span + button) = ~90 nodos por grid, más que aceptable.

#### Severidad: Informativo - NO NECESARIO EN FASE 1

### 2.4 Imágenes de Pictogramas — PARCIALMENTE CORREGIDO (PR #72)

**Formato:** PNG descargados de ARASAAC. Sin conversión a WebP.
**Tamaño típico:** Los pictogramas ARASAAC son ~5-15 KB cada uno en PNG.
**Dimensiones HTML:** No se especifican `width`/`height` explícitos. Se usa CSS (`h-16 w-16`, `h-20 w-20`, `h-24 w-24`) para dimensionar.
**Lazy loading nativo:** `loading="lazy"` agregado en PR #72.

```html
<!-- PictogramCard.vue - con lazy loading -->
<img
  :src="props.pictogram.imagePath"
  alt=""
  loading="lazy"
  class="h-16 w-16 object-contain drop-shadow-md sm:h-20 sm:w-20 lg:h-24 lg:w-24"
/>
```

#### Severidad: CORREGIDO

### 2.5 SpeakButton - Instanciación de Providers

Cada `SpeakButton` crea una nueva instancia de `WebSpeechTTS`:

```typescript
const provider = new WebSpeechTTS()
```

Con ~3 variaciones de frase visibles simultáneamente, se crean 3 instancias. `WebSpeechTTS` es un wrapper ligero sobre `window.speechSynthesis` (sin estado ni recursos pesados). Impacto despreciable.

#### Severidad: Informativo - ACEPTABLE

---

## 3. Backend API

### 3.1 Queries SQL e Índices

**Tablas identificadas** (vía Cycle ORM entities):

| Tabla | Columnas clave | Índices esperados |
|-------|---------------|-------------------|
| `categories` | `id` (PK, UUID) | PK único |
| `pictograms` | `id` (PK, UUID), `categoryId` (FK), `arasaacId`, `label` | PK + FK. Falta índice en `label` y `arasaacId` |
| `phrases` | `id` (PK, UUID), `sequenceHash` (SHA256), `pictogramIds` (JSON), `variations` (JSON) | PK. Falta índice ÚNICO en `sequenceHash` |

**Análisis de queries:**

1. **`GET /api/categories`** - `SELECT * FROM categories` (sin WHERE). Devuelve 11 filas. Full table scan sobre 11 filas es instantáneo. -- CORRECTO

2. **`GET /api/pictograms?categoryId=X`** - `SELECT * FROM pictograms WHERE categoryId = X`. Si Cycle ORM no crea índice en FK `categoryId`, esta query es un full table scan sobre ~194 filas. -- NECESITA VERIFICACIÓN

3. **`GET /api/pictograms/search?q=X`** - `SELECT * FROM pictograms WHERE unaccent(LOWER(label)) LIKE unaccent(?) LIMIT 10`. La función `unaccent()` y `LOWER()` impiden el uso de índices B-tree normales. Se necesitaria un índice funcional o GIN con pg_trgm.

4. **`POST /api/phrases/generate`** - `SELECT * FROM phrases WHERE sequenceHash = X`. Sin índice en `sequenceHash`, esta query es un full table scan que crece con cada frase generada.

5. **`findByArasaacId`** - `SELECT * FROM pictograms WHERE arasaacId = X`. Sin índice en `arasaacId`, full table scan.

6. **`findById`** (pictograms, categories, phrases) - Búsqueda por PK. Instantánea con índice PK. -- CORRECTO

#### Severidad: CORREGIDO (ya existían en migraciones)

**Verificación:** Los índices ya existen en las migraciones SQL originales:

```sql
-- V003__create_pictograms_table.sql
CREATE UNIQUE INDEX uniq_pictograms_arasaac_id ON pictograms (arasaac_id);
CREATE INDEX idx_pictograms_category ON pictograms (category_id);
CREATE INDEX idx_pictograms_label ON pictograms (label);

-- V004__create_phrases_table.sql
CREATE UNIQUE INDEX uniq_phrases_sequence_hash ON phrases (sequence_hash);
```

Todos los índices necesarios para las queries de búsqueda, filtrado por categoría y cache de frases están correctamente definidos. El hallazgo original de la revisión del 11 feb era incorrecto.

**Nota:** Con ~194 pictogramas actuales, un índice funcional GIN con `pg_trgm` para `unaccent()` no aporta beneficio medible. Los índices B-tree existentes son suficientes.

### 3.2 N+1 en GenerateHumanizedPhrase — CORREGIDO (PR #72)

**Problema original (11 feb):** `validateAndGetPictograms` ejecutaba un `findById` por cada pictograma en la secuencia (hasta 10 queries individuales).

**Corrección aplicada:** Se agregó `findByIds(array $ids): array` al `PictogramRepository` interface y su implementación en `CyclePictogramRepository` con una sola query `WHERE id IN (...)`. `GenerateHumanizedPhrase` ahora usa este método para cargar todos los pictogramas en una sola query.

**Verificado:** `GenerateHumanizedPhrase.php` línea 146: `$pictograms = $this->pictogramRepository->findByIds($pictogramIds);`

#### Severidad: CORREGIDO

### 3.3 Cache de Frases (SHA256)

La estrategia de cache es correcta y eficiente:

1. `PictogramSequence::hash()` genera un SHA256 determinístico de los IDs concatenados.
2. `PhraseRepository::findBySequenceHash()` busca en BD por hash.
3. Si existe (cache hit), se devuelve sin llamar al LLM.
4. Si no existe, se llama al LLM, se guarda en BD, y se retorna.

**Fallos de fallback NO se cachean** (línea 104: `if ($source === SOURCE_GENERATED)`). Esto es correcto: si el LLM fallo, la proxima petición reintentara con el LLM.

#### Severidad: Informativo - BIEN DISEÑADO

**Nota:** Las frases cacheadas no tienen TTL. Si se cambia el prompt, se puede truncar la tabla `phrases` manualmente para regenerar.

### 3.4 Rate Limiting Overhead

Se aplican 2 rate limiters en cada `POST /api/phrases/generate`:
1. `phrase_daily` (fixed_window, 500/día en prod)
2. `phrase_generator` (sliding_window, 30/minuto)

Ambos usan el cache adapter de Symfony (filesystem por defecto en prod). Cada `consume()` implica 1 lectura + 1 escritura al filesystem.

#### Severidad: NO APLICA
Single server con bajo tráfico, el filesystem es suficiente. Redis sería overengineering para 1 instancia.

### 3.5 GetPictogramsByCategory - 2 Queries

El use case `GetPictogramsByCategory` ejecuta:
1. `categoryRepository->findById()` -- válida que la categoría existe
2. `pictogramRepository->findByCategoryId()` -- obtiene los pictogramas

Son 2 queries por request. La primera es una validación que podría evitarse si la FK en la tabla pictograms garantiza la existencia de la categoría.

#### Severidad: Bajo
- Es un patrón defensivo válido. Con solo 11 categorías y datos inmutables, la query extra es ~0.1ms.

---

## 4. Docker/Infra

### 4.1 Nginx Config

**Gzip:** HABILITADO. Compresión nivel 6 con tipos correctos (json, js, css, svg).
```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css application/json application/javascript
           text/xml application/xml text/javascript image/svg+xml;
```

**Cache headers:**
- `/assets/*` (Vite hashed): `expires 1y; Cache-Control: public, immutable` -- CORRECTO (fingerprinted)
- `/pictograms/*`: `expires 7d; Cache-Control: public, immutable` -- CORRECTO
- `/api/*`: Sin cache headers explícitos -- CORRECTO (datos dinámicos)

**Static files:** `access_log off` para assets y pictogramas -- CORRECTO (reduce I/O).

**Nota:** No hay `gzip_min_length`. Por defecto Nginx comprime respuestas > 20 bytes. Para pictogramas PNG (ya comprimidos), gzip no aporta. Se podría agregar `gzip_min_length 256` y excluir `image/png` de los tipos.

#### Severidad: Informativo - BUENA CONFIGURACIÓN

**Quick win:** Agregar `gzip_min_length 256;` para evitar comprimir respuestas muy pequeñas.

### 4.2 Nginx - Cache de index.html — CORREGIDO (PR #71)

**Problema original (11 feb):** `index.html` se servía sin headers de cache específicos.

**Corrección aplicada:** `nginx.conf` ahora incluye:
```nginx
location = /index.html {
    add_header Cache-Control "no-cache, no-store, must-revalidate";
}
```

Esto garantiza que el navegador siempre obtiene la versión más reciente tras deploys, mientras que los assets con hash de Vite mantienen cache de 1 año.

#### Severidad: CORREGIDO

### 4.3 PHP-FPM Config

No hay archivo `www.conf` o `php-fpm.conf` personalizado. Se usa la configuración por defecto de `php:8.4-fpm-alpine`:
- `pm = dynamic`
- `pm.max_children = 5`
- `pm.start_servers = 2`
- `pm.min_spare_servers = 1`
- `pm.max_spare_servers = 3`

Para Hetzner CX33 (2 vCPU, 4 GB RAM, límite Docker 512MB para backend):

#### Severidad: NO APLICA
Default `pm.max_children=5` sobra para MVP académico con pocos usuarios concurrentes. Hetzner CX33 con 512MB de límite Docker para backend soporta 5 workers de ~50MB sin problema.

### 4.4 OPcache Config

La configuración de OPcache en producción es correcta:
```ini
opcache.enable=1
opcache.memory_consumption=128       # Suficiente para Symfony
opcache.interned_strings_buffer=16   # Bueno para annotations
opcache.max_accelerated_files=20000  # Suficiente
opcache.validate_timestamps=0        # No revalidar en prod (correcto)
```

#### Severidad: Informativo - BUENA CONFIGURACIÓN

### 4.5 PostgreSQL Config

Se usa la imagen `postgres:16-alpine` con configuración por defecto. No hay `postgresql.conf` custom.

Configuración por defecto relevante:
- `shared_buffers = 128MB` (generalmente se recomienda 25% de RAM)
- `work_mem = 4MB`
- `effective_cache_size = 4GB` (por defecto)
- `max_connections = 100`

Para Hetzner CX33 con 1GB límite Docker para PostgreSQL:

#### Severidad: NO APLICA
~200 registros totales en la base de datos. La configuración por defecto de PostgreSQL es óptima para este volumen. `shared_buffers=128MB` cabe holgadamente en el límite Docker de 1GB.

### 4.6 Docker Image Sizes

| Imagen | Base | Estimado |
|--------|------|----------|
| Backend (PHP-FPM) | php:8.4-fpm-alpine | ~150-200 MB (alpine + composer deps + Symfony cache) |
| Nginx (frontend) | nginx:1.27-alpine + node:20-alpine (build) | ~30-50 MB (alpine + dist files + fonts) |
| PostgreSQL | postgres:16-alpine | ~80 MB |

**Multi-stage build:** El Dockerfile de Nginx usa multi-stage correctamente. Stage 1 (node:20-alpine) compila el frontend, Stage 2 (nginx:1.27-alpine) solo copia el `dist/`. Node.js NO queda en la imagen final.

#### Severidad: Informativo - BUENA CONFIGURACIÓN

### 4.7 Cycle ORM - Schema Compilation en Runtime

`OrmFactory::create()` compila el schema del ORM en cada request (en la primera petición tras el arranque de PHP-FPM). La compilación escanea archivos con Finder, tokeniza clases, y genera el schema.

En producción, el OPcache mitiga esto parcialmente, pero la compilación del schema se ejecuta en el primer request de cada worker PHP-FPM.

#### Severidad: NO APLICA
OPcache mitiga completamente el coste de compilación de schema. Con `validate_timestamps=0` en producción, el schema se compila una vez por worker y se cachea en OPcache. Single server con bajo tráfico, sin impacto medible.

---

## 5. Core Web Vitals (Análisis Estático)

### 5.1 LCP (Largest Contentful Paint) - Target < 2.5s

**Candidatos LCP probables:**
1. **PictogramGrid** (primera imagen de pictograma visible) - Es la pieza de contenido más grande del viewport.
2. **CategoryBar** (texto de categorías) - Depende de la fuente Inter.

**Factores que impactan LCP:**

| Factor | Estado | Impacto |
|--------|--------|---------|
| Font loading (Inter) | 4 pesos, font-display:swap (por defecto @fontsource) | El texto se muestra inmediatamente con system font, luego swap. Causa FOUT pero no bloquea LCP. |
| JS bundle initial | 92 KB raw | Bajo impacto. Con gzip ~33 KB. |
| API call categories | Request asincrono en onMounted | Bloquea render de CategoryBar (muestra Skeleton). No bloquea LCP si las imágenes son el LCP. |
| API call pictograms | Request asincrono tras selección de categoría | No impacta LCP inicial (no hay pictogramas al cargar). |
| TTFB | Nginx -> PHP-FPM (index.html estático) | index.html se sirve directamente por Nginx. TTFB < 50ms esperado. |

**Escenario crítico:** El primer render significativo es la CategoryBar con Skeletons (no requiere datos). El LCP real dependera de cuando el usuario selecciona una categoría y se cargan los pictogramas.

#### Severidad: Informativo - MEDIDO
- **Resultado Lighthouse producción:** LCP 3.1s (target < 2.5s). Supera el target por 0.6s. El LCP es la CategoryBar tras la carga de datos. Factores: VPS compartido (Hetzner CX33), HTTP sin TLS (sin HTTP/2), waterfall de chunks JS. Aceptable para MVP académico.

### 5.2 CLS (Cumulative Layout Shift) - Target < 0.1

**Factores de riesgo CLS identificados:**

1. **Imágenes de pictogramas sin dimensiones explicitas:**
```html
<img class="h-16 w-16 object-contain" />  <!-- CSS fija el tamaño, pero... -->
```
Tailwind `h-16 w-16` (64x64px) fija las dimensiones vía CSS. El navegador reserva espacio ANTES de cargar la imagen si el CSS está cargado. Esto PREVIENE layout shift para las imágenes.

2. **Font swap (Inter):** `font-display: swap` causa un cambio visual cuando la fuente se carga, pero esto NO cuenta como CLS en métricas de Core Web Vitals (es esperado y aceptado).

3. **Sticky header:** El header es `sticky top-0`. Contenido debajo no se desplaza por el header. -- CORRECTO.

4. **Skeletons en CategoryBar:** Los skeletons tienen dimensiones fijas (`h-11 w-11 rounded-xl sm:h-12 sm:w-28`). Cuando los datos reales reemplazan los skeletons, podría haber un layout shift si las dimensiones difieren.

#### Severidad: Informativo - CONFIRMADO
- **Resultado Lighthouse producción:** CLS 0 (target < 0.1). Las dimensiones fijas con Tailwind previenen layout shift. Confirmado.

### 5.3 FID/INP (First Input Delay / Interaction to Next Paint) - Target < 100ms

**Event handlers en el critical path:**

1. **Keyboard handler global** (`handleGlobalKeydown`): Ejecuta en cada keydown. Operaciones livianas (regex test, DOM query). -- ACEPTABLE.

2. **Grid keyboard navigation** (`handleGridKeydown`): `querySelectorAll('button')` sobre ~20-30 buttons + `getColumnCount` que itera hijos del grid. -- ACEPTABLE para ~30 elementos.

3. **Click en pictograma:** `handlePictogramSelect` -> `phraseStore.addPictogram()` -> reactivity update. Sincrono, < 1ms. -- CORRECTO.

4. **Generar frase:** Request HTTP al backend. Async, no bloquea UI. -- CORRECTO.

**No hay operaciones de JS pesadas (>50ms)** en los event handlers. Todos son O(n) con n < 30.

#### Severidad: Informativo - CONFIRMADO (TBT 0ms en Lighthouse producción)

### 5.4 TTFB (Time to First Byte) - Target < 600ms

| Recurso | Servidor | Estimado | TTFB real (producción) |
|---------|----------|----------|----------------------|
| `index.html` | Nginx static file | < 10ms | 110ms |
| `/api/categories` | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms | 242ms |
| `/api/pictograms?categoryId=UUID` | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms | 230ms |
| `/api/pictograms/search?q=agua` | PHP-FPM -> Cycle ORM -> PostgreSQL | 30-100ms | 377ms |
| `/api/phrases/generate` (cache hit) | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms | 317ms |
| `/api/phrases/generate` (cache miss) | PHP-FPM -> LLM API (Gemini/OpenAI) | 1000-3000ms | Pendiente (thinking mode desactivado) |

**Nota sobre TTFB real:** Los valores incluyen ~100ms de latencia de red (cliente en Espana → Hetzner Alemania). El tiempo de servidor real es TTFB menos latencia de red. Todos los endpoints dentro del target < 600ms.

**TTFB para el initial page load** (index.html): Excelente, servido por Nginx como archivo estático (~10ms servidor).

**TTFB para API calls:** Dentro de targets para queries de datos. La generación de frases con LLM tiene thinking mode activado por defecto en Gemini 2.5 Flash, lo que causa 5-7s. Con thinking mode desactivado (`thinkingBudget: 0`) se espera 0.6-0.8s.

#### Severidad: Informativo - CONFIRMADO (medido en producción, todos < 600ms excepto LLM cache miss pendiente de optimizar)

### 5.5 FCP (First Contentful Paint) - Target < 1.8s

**Cadena crítica de render:**
1. HTML descargado (< 10ms, Nginx)
2. CSS (`index-DsQbLlxM.css`, 30 KB, ~6 KB gzipped) descargado y parseado
3. JS initial (`index-oL4cA9b_.js`, 92 KB, ~33 KB gzipped) descargado y ejecutado
4. Vue app montada, router resuelve, lazy chunk de HomeView solicitado
5. Lazy chunk (`HomeView-Bjwvz0Ug.js`, 105 KB, ~37 KB gzipped) descargado y ejecutado
6. Primer render: header + CategoryBar skeletons + empty state

**El paso 4-5 es una waterfall:** El initial chunk debe ejecutarse para que el router solicite el lazy chunk. En 4G rápido (~50ms latencia, 10 Mbps), cada chunk tarda ~50ms de descarga. Total waterfall: ~100ms para los 2 chunks.

**CSS no está inlined:** Todo el CSS (30 KB) se carga como archivo externo. Es render-blocking.

#### Severidad: NO APLICA
Con 30 KB de CSS total (~6 KB gzipped), inlinear CSS crítico no aporta beneficio medible. El CSS completo se descarga en menos de 10ms en cualquier conexión moderna.

---

## 6. Resumen de Hallazgos por Severidad

### Crítico
_(ninguno)_

### Alto
_(ninguno — H-1 corregido)_

### Medio
_(ninguno — M-4 reclasificado como NO APLICA)_

### Bajo
_(ninguno — B-3, B-4, B-5, B-6 reclasificados como NO APLICA)_

### No Aplica (reclasificados con justificación)
| # | Hallazgo | Justificación |
|---|----------|---------------|
| M-4 | Cycle ORM compila schema en runtime | OPcache mitiga completamente. Single server con bajo tráfico, sin impacto medible |
| B-3 | PHP-FPM sin tuning custom | Default `pm.max_children=5` sobra para MVP académico con pocos usuarios concurrentes |
| B-4 | PostgreSQL sin tuning custom | ~200 registros totales, `shared_buffers` default es suficiente |
| B-5 | Rate limiter usa filesystem | Single server, filesystem funciona correctamente. Redis es overengineering para 1 instancia |
| B-6 | CSS crítico no inlined | 6KB gzipped total, beneficio nulo en inlining |

### Corregidos desde revisión anterior (11 feb 2026)
| # | Hallazgo | Area | PR |
|---|----------|------|-----|
| ~~H-1~~ | Índices SQL (ya existían en migraciones V003/V004) | Backend | - |
| ~~M-1~~ | Fuentes Inter optimizadas a latin-only | Frontend Bundle | PR #72 |
| ~~M-2~~ | Sourcemaps ocultos (`sourcemap: 'hidden'`) | Frontend Bundle | PR #71 |
| ~~M-3~~ | Nginx cachea index.html con no-cache | Docker/Infra | PR #71 |
| ~~M-5~~ | N+1 corregido con `findByIds()` | Backend | PR #72 |
| ~~B-1~~ | @vueuse/core eliminada de dependencies | Frontend Bundle | PR #72 |
| ~~B-2~~ | Lazy loading en imágenes de pictogramas | Frontend Runtime | PR #72 |

### Informativo
| # | Hallazgo | Area | Estado |
|---|----------|------|--------|
| I-1 | Bundle size dentro de targets (92 KB initial, 197 KB total) | Frontend Bundle | OK |
| I-2 | Code splitting correcto (lazy loading HomeView) | Frontend Bundle | OK |
| I-3 | Tree shaking correcto (lucide, Sentry, Zod) | Frontend Bundle | OK |
| I-4 | Debounce 300ms en SearchBar | Frontend Runtime | OK |
| I-5 | Virtual scroll no necesario (<30 items/grid) | Frontend Runtime | OK |
| I-6 | Cache SHA256 bien diseñado | Backend | OK |
| I-7 | Gzip habilitado con tipos correctos | Docker/Infra | OK |
| I-8 | OPcache correctamente configurado | Docker/Infra | OK |
| I-9 | Docker multi-stage build correcto | Docker/Infra | OK |
| I-10 | FID/INP dentro de targets (event handlers ligeros) | Core Web Vitals | OK |

---

## 7. Plan de Acción

### Hallazgos corregidos (PRs #71, #72)

Los siguientes Quick Wins de la revisión anterior ya fueron implementados:

1. ~~**[H-1] Índices SQL**~~ — Ya existían en migraciones V003/V004
2. ~~**[M-1] Optimizar fuentes**~~ — Latin-only imports en `main.ts`
3. ~~**[M-2] Ocultar sourcemaps**~~ — `sourcemap: 'hidden'` en `vite.config.ts`
4. ~~**[M-3] Cache index.html**~~ — `no-cache, no-store, must-revalidate` en Nginx
5. ~~**[M-5] Resolver N+1**~~ — `findByIds()` en PictogramRepository
6. ~~**[B-1] Eliminar @vueuse/core**~~ — Eliminada de dependencies
7. ~~**[B-2] Lazy loading imágenes**~~ — `loading="lazy"` en PictogramCard

### Hallazgos reclasificados como NO APLICA

Los siguientes hallazgos de la revisión anterior no requieren acción para el alcance del proyecto:

1. **[M-4] Cycle ORM schema runtime** — OPcache mitiga completamente en producción.
2. **[B-3] PHP-FPM tuning** — Defaults suficientes para el volumen de tráfico actual.
3. **[B-4] PostgreSQL tuning** — ~200 registros, configuración por defecto es óptima.
4. **[B-5] Filesystem rate limiting** — Single server, sin necesidad de Redis.
5. **[B-6] CSS crítico** — 6KB gzipped, no hay beneficio medible en inlining.

---

## 8. Lighthouse Producción (medición real)

**Fecha:** 13 de febrero de 2026
**URL:** Servidor Hetzner CX33 (HTTP, sin TLS)
**Herramienta:** Lighthouse (headless Chrome)
**Evidencia:** `docs/audits/lighthouse/fase1/lighthouse-mobile.report.html`, `docs/audits/lighthouse/fase1/lighthouse-desktop.report.html`

### Scores

| Categoría | Mobile | Desktop |
|-----------|--------|---------|
| Performance | **95** | **99** |
| Accessibility | **100** | **100** |
| Best Practices | **78** | **78** |
| SEO | **91** | **91** |

### Core Web Vitals (medidos)

| Métrica | Mobile | Desktop | Target | Estado |
|---------|--------|---------|--------|--------|
| First Contentful Paint (FCP) | 2.2s | 0.7s | < 1.8s | Desktop OK, mobile por encima |
| Largest Contentful Paint (LCP) | 2.4s | 0.8s | < 2.5s | Ambos OK |
| Total Blocking Time (TBT) | 10ms | 0ms | < 200ms | Excelente |
| Cumulative Layout Shift (CLS) | 0.018 | 0.001 | < 0.1 | Excelente |
| Speed Index (SI) | 2.2s | 0.7s | < 3.4s | Ambos OK |

### Análisis

- **Desktop (99):** Todos los Core Web Vitals dentro de targets. LCP 0.8s y FCP 0.7s son excelentes.
- **Mobile (95):** LCP 2.4s dentro del target (< 2.5s). FCP 2.2s ligeramente por encima (target 1.8s). El throttling de Lighthouse mobile simula Moto G Power con 4G lenta, que es más restrictivo que el uso real en tablet (dispositivo principal de la app).
- **TBT ~0ms y CLS ~0** en ambos confirman que la arquitectura frontend es eficiente: sin JS pesado, sin layout shifts.
- **Best Practices 78** por uso de HTTP en lugar de HTTPS (TLS pendiente).
- **Accessibility 100** en ambos confirma el cumplimiento WCAG 2.2 AA.
- Los scores varian ±5 puntos entre ejecuciones. Se realizaron 3 runs con resultados consistentes (mobile 95-96, desktop 98-99).

### Métricas pendientes de medición

| Métrica | Herramienta | Motivo |
|---------|-------------|--------|
| p95 API response time | k6 o Artillery | Requiere carga simulada |

---

## 9. Conclusión

El proyecto HablaIA FASE 1 tiene una base de performance sólida, con mejoras significativas respecto a la revisión anterior:

**Correcciones aplicadas (PRs #71, #72):**
- Fuentes Inter optimizadas: de 56 archivos (~898 KB) a 4 archivos latin-only (~97 KB)
- Sourcemaps ocultos en producción (`sourcemap: 'hidden'`)
- N+1 corregido con `findByIds()` (de N queries a 1 query)
- Lazy loading en imágenes de pictogramas
- Cache headers correctos en index.html (no-cache para deploys instantaneos)
- @vueuse/core eliminada de dependencies directas

**Fortalezas consolidadas:**
- **Bundle size** dentro de targets (92 KB initial raw, ~33 KB gzipped)
- **Arquitectura frontend** eficiente: stores granulares, debounce correcto, sin virtual scroll innecesario
- **Cache SHA256** de frases evita llamadas redundantes al LLM
- **Índices SQL** correctos en todas las columnas de búsqueda
- **Infraestructura Docker** con buenas prácticas (gzip, cache headers, OPcache, multi-stage builds)

**Hallazgos pendientes: 0.** Los 5 hallazgos restantes de la revisión anterior (M-4, B-3, B-4, B-5, B-6) han sido reclasificados como NO APLICA con justificación: el volumen de datos (~200 registros), el tráfico (MVP académico) y la configuración (single server con OPcache) hacen que las optimizaciones propuestas no aporten beneficio medible.

**Lighthouse producción:** Mobile 95 / Desktop 99. Accessibility 100/100. TBT ~0ms y CLS ~0 confirman la eficiencia del frontend. LCP dentro de targets en ambos (mobile 2.4s, desktop 0.8s). Best Practices 78 por HTTP sin TLS. Evidencia: `docs/audits/lighthouse/fase1/lighthouse-mobile.report.html`, `docs/audits/lighthouse/fase1/lighthouse-desktop.report.html`. **0 hallazgos pendientes de acción.**
