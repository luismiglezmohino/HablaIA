# Auditoria de Performance - Phase 1

> Revision de rendimiento del proyecto completo HablaIA

**Ultima revision:** 11 de febrero de 2026
**Alcance:** Full stack - Backend + Frontend + Docker/Infra
**Fase:** Phase 1 MVP (comunicador publico, sin autenticacion)
**Evaluador:** @performance_engineer

## Resumen Ejecutivo

| Area | Estado | Detalle |
|------|--------|---------|
| Frontend Bundle | ALERTA | Initial JS 92KB + lazy chunk 105KB (total 197KB sin gzip). Con gzip estimado ~60KB initial. Fuentes Inter: 56 archivos, ~1MB total. |
| Frontend Runtime | ACEPTABLE | Stores ligeros, debounce 300ms correcto, sin virtual scroll pero volumen bajo (~20 pictogramas/categoria). |
| Backend API | ACEPTABLE | Cache SHA256, rate limiting, queries simples. Falta indice en `label` y en `sequenceHash`. N+1 en GenerateHumanizedPhrase. |
| Docker/Infra | BUENO | Gzip habilitado, cache headers en assets, OPcache configurado. Falta PHP-FPM tuning custom. |
| Core Web Vitals | REQUIERE MEDICION | Fuentes bloquean render (LCP), imagenes sin dimensiones explicitas (CLS). Analisis estatico solo. |

---

## 1. Frontend Bundle

### 1.1 Bundle Size (medido desde build del 9 feb 2026)

| Archivo | Tamano (raw) | Estimado gzip (~65%) | Tipo |
|---------|-------------|---------------------|------|
| `index-oL4cA9b_.js` | 94,642 bytes (92.4 KB) | ~33 KB | Initial chunk (Vue, Pinia, Router, Sentry, Zod, Radix, lucide) |
| `HomeView-Bjwvz0Ug.js` | 107,185 bytes (104.7 KB) | ~37 KB | Lazy chunk (HomeView + todos los componentes) |
| `index-DsQbLlxM.css` | 30,518 bytes (29.8 KB) | ~6 KB | Tailwind CSS purgeado |
| **Total JS** | **197 KB** | **~70 KB** | |
| **Total JS+CSS** | **227 KB** | **~76 KB** | |

**Analisis:**
- El target de `< 100KB initial` se cumple para el JS initial (92.4 KB raw).
- El target de `< 500KB total` se cumple holgadamente (227 KB raw total).
- Con gzip (habilitado en Nginx), el initial chunk baja a ~33 KB, muy por debajo del target.

#### Severidad: Informativo - DENTRO DE TARGETS

### 1.2 Fuentes Inter (problema principal)

| Subconjunto | Pesos | Archivos woff2 | Tamano woff2 estimado |
|-------------|-------|-----------------|----------------------|
| latin | 400,500,600,700 | 4 | ~97 KB |
| latin-ext | 400,500,600,700 | 4 | ~144 KB |
| cyrillic | 400,500,600,700 | 4 | ~32 KB |
| cyrillic-ext | 400,500,600,700 | 4 | ~42 KB |
| greek | 400,500,600,700 | 4 | ~31 KB |
| greek-ext | 400,500,600,700 | 4 | ~22 KB |
| vietnamese | 400,500,600,700 | 4 | ~20 KB |
| **Total woff2** | | **28 archivos** | **~388 KB** |
| **Total woff** | | **28 archivos** | **~510 KB** |
| **Total fuentes** | | **56 archivos** | **~898 KB** |

**Problema:** Se empaquetan 4 pesos (400,500,600,700) x 7 subconjuntos x 2 formatos (woff2+woff) = 56 archivos de fuente. Para una app en espanol, solo se necesitan los subconjuntos `latin` y `latin-ext`. Los subconjuntos cyrillic, greek, vietnamese son innecesarios.

**Impacto real:** El navegador solo descarga los subconjuntos que necesita via `unicode-range` en los `@font-face` generados por `@fontsource/inter`. Los archivos cyrillic/greek/vietnamese existen en el build pero NO se descargan a menos que el texto los requiera. Sin embargo, ocupan espacio en la imagen Docker y en el servidor.

#### Severidad: Medio
- **Quick win:** Importar solo los subconjuntos necesarios desde `@fontsource/inter/latin` y `@fontsource/inter/latin-ext` en lugar de importar por peso (`@fontsource/inter/400.css`). Esto eliminaria ~50 archivos de fuente del build (~600 KB en disco).
- **Alternativa Phase 2+:** Usar `font-display: swap` explicitamente (ya lo hace @fontsource por defecto) y considerar variable font (`@fontsource-variable/inter`) para un solo archivo por subconjunto.

### 1.3 Code Splitting

**Router (lazy loading):** CORRECTO.
```typescript
// src/presentation/router/index.ts
component: () => import('@/presentation/views/HomeView.vue')
```
HomeView se carga como chunk separado (lazy). Sin embargo, al ser la unica ruta de la SPA, todo usuario la carga inmediatamente. El beneficio real del lazy loading es marginal en Phase 1 (solo 1 ruta), pero la arquitectura esta preparada para cuando se agreguen rutas en fases futuras.

**Vite config:** Sin configuracion manual de `manualChunks`. Vite aplica su splitting por defecto.

#### Severidad: Informativo - CORRECTO

### 1.4 Tree Shaking

**lucide-vue-next:** Se importan iconos individuales (`import { Sparkles, Search, X } from 'lucide-vue-next'`). Vite hace tree shaking correcto de iconos no usados.

**@sentry/vue:** Se importa como `import * as Sentry from '@sentry/vue'` en `main.ts`. Esta es la forma recomendada por Sentry y el bundler tree-shakea los modulos no usados. Sin embargo, Sentry agrega ~20-30 KB al bundle initial incluso con tree shaking.

**radix-vue:** Solo se usa indirectamente via shadcn-vue (Badge, Skeleton). El impacto es minimo.

**zod:** Se usa para validar schemas de API responses. Agrega ~12 KB al bundle. Es necesario para la validacion runtime.

**@vueuse/core:** Se importa en `package.json` pero NO se usa en ningun archivo de produccion (solo se menciona en CLAUDE.md como parte del stack). Es una dependencia fantasma.

#### Severidad: Bajo
- **Quick win:** Eliminar `@vueuse/core` de dependencies si no se usa realmente en codigo de produccion. Ahorro estimado: 0 KB en bundle (tree shaking lo elimina si no se importa), pero limpia `package.json`.

### 1.5 Dependencias - Analisis de Bloat

| Dependencia | Tamano estimado en bundle | Necesaria |
|-------------|--------------------------|-----------|
| vue | ~33 KB | Si (framework) |
| @sentry/vue | ~25 KB | Si (observabilidad) |
| zod | ~12 KB | Si (validacion runtime) |
| pinia | ~5 KB | Si (state management) |
| vue-router | ~8 KB | Si (routing) |
| lucide-vue-next | ~2-3 KB (solo iconos usados) | Si (iconos) |
| radix-vue | ~3 KB (solo Badge/Skeleton) | Si (UI primitivas) |
| class-variance-authority | ~1 KB | Si (shadcn utility) |
| clsx | <1 KB | Si (shadcn utility) |
| tailwind-merge | ~3 KB | Si (shadcn utility) |
| @fontsource/inter | 0 KB JS (solo CSS/fonts) | Si (tipografia) |
| @vueuse/core | 0 KB (no importado) | No - ELIMINAR |

**Total estimado de dependencias runtime:** ~92 KB (alineado con el bundle real de 92.4 KB).

#### Severidad: Informativo - SIN BLOAT SIGNIFICATIVO

### 1.6 Sourcemaps

Los sourcemaps estan habilitados en produccion (`sourcemap: true` en `vite.config.ts`):
- `index-oL4cA9b_.js.map`: 791,631 bytes (773 KB)
- `HomeView-Bjwvz0Ug.js.map`: 441,742 bytes (431 KB)
- **Total sourcemaps:** ~1.2 MB

**Problema:** Los sourcemaps se sirven en produccion. Aunque los navegadores solo los descargan cuando DevTools esta abierto, exponen la estructura completa del codigo fuente.

#### Severidad: Medio
- **Quick win Phase 2:** Cambiar a `sourcemap: 'hidden'` para generar sourcemaps (para Sentry) pero no referenciarlos en los JS bundles. Sentry puede subirlos via CLI en el pipeline de CD.
- **Alternativa rapida:** Nginx ya no sirve `*.map` explicitamente, pero el `try_files` catch-all los hace accesibles. Agregar una regla Nginx para denegar acceso a `*.map`.

---

## 2. Frontend Runtime

### 2.1 Reactividad y Re-renders

**Pinia Stores:** Los 3 stores (`useCategoryStore`, `usePictogramStore`, `usePhraseStore`) usan la Composition API con `ref()` y `computed()`. El estado es granular y no provoca re-renders innecesarios.

- `sortedCategories` es un `computed` (se recalcula solo cuando `categories` cambia) -- CORRECTO
- `selectedCategory` es un `computed` (se recalcula solo cuando `categories` o `selectedCategoryId` cambian) -- CORRECTO
- `canGenerate` y `isFull` son `computed` -- CORRECTO

**HomeView.vue:** Contiene 6 `watch()` y 2 `computed`. Todos tienen dependencias correctas y no se disparan innecesariamente.

**PictogramGrid.vue `getCategoryColor`:** Se ejecuta por cada pictograma en cada render. Hace un `Array.find()` sobre las categorias (11 items). Con ~20 pictogramas por categoria, son ~200 operaciones de busqueda simple. Impacto despreciable.

#### Severidad: Informativo - CORRECTO

### 2.2 Debounce

**SearchBar.vue:** Debounce de 300ms en el `watch(query)` -- CORRECTO.
**HomeView.vue (mobile):** Debounce de 300ms en `watch(mobileQuery)` -- CORRECTO.

Los timers se limpian correctamente con `clearTimeout` antes de crear uno nuevo. No hay memory leaks por timers.

#### Severidad: Informativo - CORRECTO

### 2.3 Virtualizacion de Listas

Los pictogramas NO usan virtual scroll. El grid renderiza todos los pictogramas de la categoria de una vez.

**Datos del dominio:** ~194 pictogramas totales distribuidos en 11 categorias. La categoria mas grande tendra ~30 pictogramas. La busqueda devuelve maximo 10 resultados.

**Impacto:** Con un maximo de ~30 cards renderizadas simultaneamente, la virtualizacion no es necesaria. El DOM tiene ~30 x 3 elementos (img + span + button) = ~90 nodos por grid, mas que aceptable.

#### Severidad: Informativo - NO NECESARIO EN PHASE 1

### 2.4 Imagenes de Pictogramas

**Formato:** PNG descargados de ARASAAC. Sin conversion a WebP.
**Tamano tipico:** Los pictogramas ARASAAC son ~5-15 KB cada uno en PNG.
**Dimensiones HTML:** No se especifican `width`/`height` explicitos en las etiquetas `<img>`. Se usa CSS (`h-16 w-16`, `h-20 w-20`, `h-24 w-24` segun breakpoint) para dimensionar.
**Lazy loading nativo:** NO se usa `loading="lazy"` en las imagenes de pictogramas.

```html
<!-- PictogramCard.vue - sin width/height ni loading="lazy" -->
<img
  :src="props.pictogram.imagePath"
  :alt="props.pictogram.label"
  class="h-16 w-16 object-contain drop-shadow-md sm:h-20 sm:w-20 lg:h-24 lg:w-24"
/>
```

#### Severidad: Bajo
- **Quick win:** Agregar `loading="lazy"` a las imagenes de pictogramas en `PictogramCard.vue`. Con 20-30 imagenes en grid, el above-the-fold mostrara ~6-12, el resto se beneficia de lazy loading.
- **Phase 2+:** Convertir imagenes a WebP durante el sync de ARASAAC. Ahorro estimado: 30-50% del tamano.
- **Phase 2+:** Agregar `width` y `height` explicitos para evitar layout shifts (ver seccion CLS).

### 2.5 SpeakButton - Instanciacion de Providers

Cada `SpeakButton` crea una nueva instancia de `WebSpeechTTS`:

```typescript
const provider = new WebSpeechTTS()
```

Con ~3 variaciones de frase visibles simultaneamente, se crean 3 instancias. `WebSpeechTTS` es un wrapper ligero sobre `window.speechSynthesis` (sin estado ni recursos pesados). Impacto despreciable.

#### Severidad: Informativo - ACEPTABLE

---

## 3. Backend API

### 3.1 Queries SQL e Indices

**Tablas identificadas** (via Cycle ORM entities):

| Tabla | Columnas clave | Indices esperados |
|-------|---------------|-------------------|
| `categories` | `id` (PK, UUID) | PK unico |
| `pictograms` | `id` (PK, UUID), `categoryId` (FK), `arasaacId`, `label` | PK + FK. Falta indice en `label` y `arasaacId` |
| `phrases` | `id` (PK, UUID), `sequenceHash` (SHA256), `pictogramIds` (JSON), `variations` (JSON) | PK. Falta indice UNICO en `sequenceHash` |

**Analisis de queries:**

1. **`GET /api/categories`** - `SELECT * FROM categories` (sin WHERE). Devuelve 11 filas. Full table scan sobre 11 filas es instantaneo. -- CORRECTO

2. **`GET /api/pictograms?categoryId=X`** - `SELECT * FROM pictograms WHERE categoryId = X`. Si Cycle ORM no crea indice en FK `categoryId`, esta query es un full table scan sobre ~194 filas. -- NECESITA VERIFICACION

3. **`GET /api/pictograms/search?q=X`** - `SELECT * FROM pictograms WHERE unaccent(LOWER(label)) LIKE unaccent(?) LIMIT 10`. La funcion `unaccent()` y `LOWER()` impiden el uso de indices B-tree normales. Se necesitaria un indice funcional o GIN con pg_trgm.

4. **`POST /api/phrases/generate`** - `SELECT * FROM phrases WHERE sequenceHash = X`. Sin indice en `sequenceHash`, esta query es un full table scan que crece con cada frase generada.

5. **`findByArasaacId`** - `SELECT * FROM pictograms WHERE arasaacId = X`. Sin indice en `arasaacId`, full table scan.

6. **`findById`** (pictograms, categories, phrases) - Busqueda por PK. Instantanea con indice PK. -- CORRECTO

#### Severidad: Alto - INDICES FALTANTES

**Quick wins (crear estos indices):**
```sql
-- Indice para busqueda de frases cacheadas (critico para performance LLM)
CREATE UNIQUE INDEX idx_phrases_sequence_hash ON phrases (sequenceHash);

-- Indice para busqueda de pictogramas por categoria
CREATE INDEX idx_pictograms_category_id ON pictograms (categoryId);

-- Indice para busqueda de pictogramas por arasaacId (sync ARASAAC)
CREATE UNIQUE INDEX idx_pictograms_arasaac_id ON pictograms (arasaacId);

-- Indice funcional para busqueda de texto (requiere pg_trgm)
CREATE INDEX idx_pictograms_label_unaccent ON pictograms
  USING GIN (unaccent(LOWER(label)) gin_trgm_ops);
```

**Nota:** Con ~194 pictogramas y ~pocas frases, el impacto en p95 es minimo actualmente. Pero sin indices, la performance se degrada linealmente con el crecimiento de datos. Los indices son una inversion de coste cero.

### 3.2 N+1 en GenerateHumanizedPhrase

El metodo `validateAndGetPictograms` ejecuta un `findById` POR CADA pictograma en la secuencia:

```php
foreach ($pictogramIdStrings as $idString) {
    $pictogramId = PictogramId::fromString($idString);
    $pictogram = $this->pictogramRepository->findById($pictogramId);
    // ...
}
```

Con una secuencia de 10 pictogramas, se ejecutan 10 queries individuales a la tabla `pictograms`.

#### Severidad: Medio

**Quick win Phase 2:** Agregar un metodo `findByIds(array $ids): array` al `PictogramRepository` que ejecute una sola query `WHERE id IN (...)`. Reducir de N queries a 1 query.

**Impacto actual:** Con 10 pictogramas maximo y PK indexado, cada query es ~0.1ms. Total: ~1ms. Bajo impacto en p95, pero es un anti-patron que debe corregirse.

### 3.3 Cache de Frases (SHA256)

La estrategia de cache es correcta y eficiente:

1. `PictogramSequence::hash()` genera un SHA256 deterministico de los IDs concatenados.
2. `PhraseRepository::findBySequenceHash()` busca en BD por hash.
3. Si existe (cache hit), se devuelve sin llamar al LLM.
4. Si no existe, se llama al LLM, se guarda en BD, y se retorna.

**Fallos de fallback NO se cachean** (linea 104: `if ($source === SOURCE_GENERATED)`). Esto es correcto: si el LLM fallo, la proxima peticion reintentara con el LLM.

#### Severidad: Informativo - BIEN DISENADO

**Mejora Phase 2:** Agregar TTL a las frases cacheadas para permitir regeneracion periodica con mejores prompts.

### 3.4 Rate Limiting Overhead

Se aplican 2 rate limiters en cada `POST /api/phrases/generate`:
1. `phrase_daily` (fixed_window, 500/dia en prod)
2. `phrase_generator` (sliding_window, 30/minuto)

Ambos usan el cache adapter de Symfony (filesystem por defecto en prod). Cada `consume()` implica 1 lectura + 1 escritura al filesystem.

#### Severidad: Bajo
**Phase 2:** Considerar Redis como backend de rate limiting para evitar I/O de filesystem. Actualmente con bajo trafico, el filesystem es suficiente.

### 3.5 GetPictogramsByCategory - 2 Queries

El use case `GetPictogramsByCategory` ejecuta:
1. `categoryRepository->findById()` -- valida que la categoria existe
2. `pictogramRepository->findByCategoryId()` -- obtiene los pictogramas

Son 2 queries por request. La primera es una validacion que podria evitarse si la FK en la tabla pictograms garantiza la existencia de la categoria.

#### Severidad: Bajo
- Es un patron defensivo valido. Con solo 11 categorias y datos inmutables, la query extra es ~0.1ms.

---

## 4. Docker/Infra

### 4.1 Nginx Config

**Gzip:** HABILITADO. Compresion nivel 6 con tipos correctos (json, js, css, svg).
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
- `/api/*`: Sin cache headers explicitos -- CORRECTO (datos dinamicos)

**Static files:** `access_log off` para assets y pictogramas -- CORRECTO (reduce I/O).

**Nota:** No hay `gzip_min_length`. Por defecto Nginx comprime respuestas > 20 bytes. Para pictogramas PNG (ya comprimidos), gzip no aporta. Se podria agregar `gzip_min_length 256` y excluir `image/png` de los tipos.

#### Severidad: Informativo - BUENA CONFIGURACION

**Quick win:** Agregar `gzip_min_length 256;` para evitar comprimir respuestas muy pequenas.

### 4.2 Nginx - Headers Faltantes

No se sirve `Cache-Control` para el `index.html` de la SPA. La ruta catch-all `try_files $uri $uri/ /index.html` sirve `index.html` sin headers de cache especificos.

#### Severidad: Medio
**Quick win:** Agregar `no-cache` para `index.html` para garantizar que siempre se obtiene la version mas reciente tras deploys:
```nginx
location = /index.html {
    add_header Cache-Control "no-cache, no-store, must-revalidate";
}
```

### 4.3 PHP-FPM Config

No hay archivo `www.conf` o `php-fpm.conf` personalizado. Se usa la configuracion por defecto de `php:8.4-fpm-alpine`:
- `pm = dynamic`
- `pm.max_children = 5`
- `pm.start_servers = 2`
- `pm.min_spare_servers = 1`
- `pm.max_spare_servers = 3`

Para Hetzner CX33 (2 vCPU, 4 GB RAM, limite Docker 512MB para backend):

#### Severidad: Bajo
**Phase 2:** Crear `docker/production/php-fpm.conf` con tuning para el hardware:
```ini
[www]
pm = dynamic
pm.max_children = 10        ; con 512MB limit, ~50MB/worker
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5
pm.max_requests = 500        ; reciclar workers para evitar memory leaks
```

### 4.4 OPcache Config

La configuracion de OPcache en produccion es correcta:
```ini
opcache.enable=1
opcache.memory_consumption=128       # Suficiente para Symfony
opcache.interned_strings_buffer=16   # Bueno para annotations
opcache.max_accelerated_files=20000  # Suficiente
opcache.validate_timestamps=0        # No revalidar en prod (correcto)
```

#### Severidad: Informativo - BUENA CONFIGURACION

### 4.5 PostgreSQL Config

Se usa la imagen `postgres:16-alpine` con configuracion por defecto. No hay `postgresql.conf` custom.

Configuracion por defecto relevante:
- `shared_buffers = 128MB` (generalmente se recomienda 25% de RAM)
- `work_mem = 4MB`
- `effective_cache_size = 4GB` (por defecto)
- `max_connections = 100`

Para Hetzner CX33 con 1GB limite Docker para PostgreSQL:

#### Severidad: Bajo
**Phase 2:** Crear `docker/postgres/postgresql.conf` con:
```ini
shared_buffers = 256MB
work_mem = 8MB
effective_cache_size = 768MB
max_connections = 50
```

### 4.6 Docker Image Sizes

| Imagen | Base | Estimado |
|--------|------|----------|
| Backend (PHP-FPM) | php:8.4-fpm-alpine | ~150-200 MB (alpine + composer deps + Symfony cache) |
| Nginx (frontend) | nginx:1.27-alpine + node:20-alpine (build) | ~30-50 MB (alpine + dist files + fonts) |
| PostgreSQL | postgres:16-alpine | ~80 MB |

**Multi-stage build:** El Dockerfile de Nginx usa multi-stage correctamente. Stage 1 (node:20-alpine) compila el frontend, Stage 2 (nginx:1.27-alpine) solo copia el `dist/`. Node.js NO queda en la imagen final.

#### Severidad: Informativo - BUENA CONFIGURACION

### 4.7 Cycle ORM - Schema Compilation en Runtime

`OrmFactory::create()` compila el schema del ORM en cada request (en la primera peticion tras el arranque de PHP-FPM). La compilacion escanea archivos con Finder, tokeniza clases, y genera el schema.

En produccion, el OPcache mitiga esto parcialmente, pero la compilacion del schema se ejecuta en el primer request de cada worker PHP-FPM.

#### Severidad: Medio
**Phase 2:** Cachear el schema compilado. Opciones:
1. Pre-compilar el schema en el build de Docker y guardarlo como array PHP en un archivo.
2. Usar APCu para cachear el schema array entre requests.
3. Cycle ORM soporta `SchemaInterface` con un array estatico.

---

## 5. Core Web Vitals (Analisis Estatico)

### 5.1 LCP (Largest Contentful Paint) - Target < 2.5s

**Candidatos LCP probables:**
1. **PictogramGrid** (primera imagen de pictograma visible) - Es la pieza de contenido mas grande del viewport.
2. **CategoryBar** (texto de categorias) - Depende de la fuente Inter.

**Factores que impactan LCP:**

| Factor | Estado | Impacto |
|--------|--------|---------|
| Font loading (Inter) | 4 pesos, font-display:swap (por defecto @fontsource) | El texto se muestra inmediatamente con system font, luego swap. Causa FOUT pero no bloquea LCP. |
| JS bundle initial | 92 KB raw | Bajo impacto. Con gzip ~33 KB. |
| API call categories | Request asincrono en onMounted | Bloquea render de CategoryBar (muestra Skeleton). No bloquea LCP si las imagenes son el LCP. |
| API call pictograms | Request asincrono tras seleccion de categoria | No impacta LCP inicial (no hay pictogramas al cargar). |
| TTFB | Nginx -> PHP-FPM (index.html estatico) | index.html se sirve directamente por Nginx. TTFB < 50ms esperado. |

**Escenario critico:** El primer render significativo es la CategoryBar con Skeletons (no requiere datos). El LCP real dependera de cuando el usuario selecciona una categoria y se cargan los pictogramas.

#### Severidad: Bajo - REQUIERE MEDICION EN PRODUCCION
- **Recomendacion:** Ejecutar Lighthouse en la URL de produccion para medir LCP real.

### 5.2 CLS (Cumulative Layout Shift) - Target < 0.1

**Factores de riesgo CLS identificados:**

1. **Imagenes de pictogramas sin dimensiones explicitas:**
```html
<img class="h-16 w-16 object-contain" />  <!-- CSS fija el tamano, pero... -->
```
Tailwind `h-16 w-16` (64x64px) fija las dimensiones via CSS. El navegador reserva espacio ANTES de cargar la imagen si el CSS esta cargado. Esto PREVIENE layout shift para las imagenes.

2. **Font swap (Inter):** `font-display: swap` causa un cambio visual cuando la fuente se carga, pero esto NO cuenta como CLS en metricas de Core Web Vitals (es esperado y aceptado).

3. **Sticky header:** El header es `sticky top-0`. Contenido debajo no se desplaza por el header. -- CORRECTO.

4. **Skeletons en CategoryBar:** Los skeletons tienen dimensiones fijas (`h-11 w-11 rounded-xl sm:h-12 sm:w-28`). Cuando los datos reales reemplazan los skeletons, podria haber un layout shift si las dimensiones difieren.

#### Severidad: Bajo - PROBABLEMENTE DENTRO DE TARGET
- **Recomendacion:** Verificar con Lighthouse que CLS < 0.1. Los Tailwind classes fijan dimensiones correctamente.

### 5.3 FID/INP (First Input Delay / Interaction to Next Paint) - Target < 100ms

**Event handlers en el critical path:**

1. **Keyboard handler global** (`handleGlobalKeydown`): Ejecuta en cada keydown. Operaciones livianas (regex test, DOM query). -- ACEPTABLE.

2. **Grid keyboard navigation** (`handleGridKeydown`): `querySelectorAll('button')` sobre ~20-30 buttons + `getColumnCount` que itera hijos del grid. -- ACEPTABLE para ~30 elementos.

3. **Click en pictograma:** `handlePictogramSelect` -> `phraseStore.addPictogram()` -> reactivity update. Sincrono, < 1ms. -- CORRECTO.

4. **Generar frase:** Request HTTP al backend. Async, no bloquea UI. -- CORRECTO.

**No hay operaciones de JS pesadas (>50ms)** en los event handlers. Todos son O(n) con n < 30.

#### Severidad: Informativo - DENTRO DE TARGET

### 5.4 TTFB (Time to First Byte) - Target < 600ms

| Recurso | Servidor | Estimado |
|---------|----------|----------|
| `index.html` | Nginx static file | < 10ms |
| `/assets/*.js` | Nginx static file | < 10ms |
| `/api/categories` | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms (11 filas, query simple) |
| `/api/pictograms?categoryId=X` | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms (~20 filas) |
| `/api/pictograms/search?q=X` | PHP-FPM -> Cycle ORM -> PostgreSQL | 30-100ms (LIKE + unaccent) |
| `/api/phrases/generate` (cache hit) | PHP-FPM -> Cycle ORM -> PostgreSQL | 20-50ms (1 query por hash) |
| `/api/phrases/generate` (cache miss) | PHP-FPM -> LLM API (Gemini/OpenAI) | 1000-3000ms (LLM latencia) |

**TTFB para el initial page load** (index.html): Excelente, servido por Nginx como archivo estatico.

**TTFB para API calls:** Dentro de targets para queries de datos. La generacion de frases con LLM es la excepcion inevitable (1-3s), mitigada por la cache SHA256.

#### Severidad: Informativo - DENTRO DE TARGETS (excepto LLM, que es inherente al dominio)

### 5.5 FCP (First Contentful Paint) - Target < 1.8s

**Cadena critica de render:**
1. HTML descargado (< 10ms, Nginx)
2. CSS (`index-DsQbLlxM.css`, 30 KB, ~6 KB gzipped) descargado y parseado
3. JS initial (`index-oL4cA9b_.js`, 92 KB, ~33 KB gzipped) descargado y ejecutado
4. Vue app montada, router resuelve, lazy chunk de HomeView solicitado
5. Lazy chunk (`HomeView-Bjwvz0Ug.js`, 105 KB, ~37 KB gzipped) descargado y ejecutado
6. Primer render: header + CategoryBar skeletons + empty state

**El paso 4-5 es una waterfall:** El initial chunk debe ejecutarse para que el router solicite el lazy chunk. En 4G rapido (~50ms latencia, 10 Mbps), cada chunk tarda ~50ms de descarga. Total waterfall: ~100ms para los 2 chunks.

**CSS no esta inlined:** Todo el CSS (30 KB) se carga como archivo externo. Es render-blocking.

#### Severidad: Bajo
- **Phase 2:** Considerar inlinear CSS critico (above-the-fold) en `index.html` y cargar el resto async. Con 30 KB de CSS total y ~6 KB gzipped, el beneficio seria marginal.
- **Quick win:** Agregar `<link rel="preload">` para el lazy chunk si se puede predecir. Pero Vite ya genera los hashes en build, complicando el preload.

---

## 6. Resumen de Hallazgos por Severidad

### Critico
_(ninguno)_

### Alto
| # | Hallazgo | Area | Quick Win |
|---|----------|------|-----------|
| H-1 | Indices SQL faltantes (sequenceHash, categoryId, arasaacId, label) | Backend | Si - crear migracion SQL |

### Medio
| # | Hallazgo | Area | Quick Win |
|---|----------|------|-----------|
| M-1 | Fuentes Inter: 56 archivos, solo se necesitan latin/latin-ext | Frontend Bundle | Si - cambiar imports |
| M-2 | Sourcemaps en produccion expuestos | Frontend Bundle | Si - `sourcemap: 'hidden'` o regla Nginx |
| M-3 | Nginx no cachea `index.html` con no-cache | Docker/Infra | Si - agregar location rule |
| M-4 | Cycle ORM compila schema en runtime por worker | Backend | No (requiere refactor de OrmFactory) |
| M-5 | N+1 en validateAndGetPictograms | Backend | No (requiere nuevo metodo en repositorio) |

### Bajo
| # | Hallazgo | Area | Quick Win |
|---|----------|------|-----------|
| B-1 | `@vueuse/core` en dependencies sin usar | Frontend Bundle | Si - npm uninstall |
| B-2 | Pictogramas sin `loading="lazy"` | Frontend Runtime | Si - agregar atributo |
| B-3 | PHP-FPM sin tuning custom | Docker/Infra | Si - crear config |
| B-4 | PostgreSQL sin tuning custom | Docker/Infra | No (requiere testing) |
| B-5 | Rate limiter usa filesystem en vez de Redis | Backend | No (Phase 2) |
| B-6 | CSS critico no inlined | Frontend | No (Phase 2) |

### Informativo
| # | Hallazgo | Area | Estado |
|---|----------|------|--------|
| I-1 | Bundle size dentro de targets (92 KB initial, 197 KB total) | Frontend Bundle | OK |
| I-2 | Code splitting correcto (lazy loading HomeView) | Frontend Bundle | OK |
| I-3 | Tree shaking correcto (lucide, Sentry, Zod) | Frontend Bundle | OK |
| I-4 | Debounce 300ms en SearchBar | Frontend Runtime | OK |
| I-5 | Virtual scroll no necesario (<30 items/grid) | Frontend Runtime | OK |
| I-6 | Cache SHA256 bien disenado | Backend | OK |
| I-7 | Gzip habilitado con tipos correctos | Docker/Infra | OK |
| I-8 | OPcache correctamente configurado | Docker/Infra | OK |
| I-9 | Docker multi-stage build correcto | Docker/Infra | OK |
| I-10 | FID/INP dentro de targets (event handlers ligeros) | Core Web Vitals | OK |

---

## 7. Plan de Accion

### Quick Wins (implementables en < 1 hora)

1. **[H-1] Crear indices SQL** - Crear fichero `init.sql` actualizado o migracion con los 4 indices.
2. **[M-2] Ocultar sourcemaps** - Agregar regla Nginx: `location ~* \.map$ { return 404; }`
3. **[M-3] Cache index.html** - Agregar `location = /index.html { add_header Cache-Control "no-cache"; }`
4. **[B-2] Lazy loading imagenes** - Agregar `loading="lazy"` en PictogramCard.vue y chips de PhraseBar.vue.

### Mejoras Phase 2+

1. **[M-1] Optimizar fuentes** - Importar solo `@fontsource/inter/latin` y `@fontsource/inter/latin-ext`. Evaluar variable font.
2. **[M-4] Cachear schema Cycle ORM** - Pre-compilar schema como array PHP en Docker build.
3. **[M-5] Resolver N+1** - Agregar `findByIds()` al PictogramRepository.
4. **[B-3] PHP-FPM tuning** - Crear config custom para Hetzner CX33.
5. **[B-5] Redis para rate limiting** - Migrar cache backend a Redis.
6. Convertir pictogramas a WebP durante sync.
7. Sentry sourcemap upload via CI/CD (reemplaza sourcemaps publicos).

---

## 8. Metricas Pendientes (requieren medicion en produccion)

| Metrica | Herramienta | Motivo |
|---------|-------------|--------|
| LCP real | Lighthouse en URL produccion | Analisis estatico no puede medir tiempo real |
| CLS real | Lighthouse en URL produccion | Depende de timing de fonts y datos |
| TTFB real | curl o Lighthouse | Depende de latencia red Hetzner |
| p95 API response time | k6 o Artillery load test | Requiere carga simulada |
| Lighthouse score completo | Lighthouse CI | Score compuesto de todas las metricas |
| Bundle gzip sizes reales | DevTools Network tab | Depende de nivel de compresion Nginx |

**Recomendacion:** Ejecutar `npx lighthouse https://hablaia.example.com --output=html` contra la URL de produccion para obtener metricas reales antes de la entrega del TFM.

---

## 9. Conclusion

El proyecto HablaIA Phase 1 tiene una base de performance solida:

- **Bundle size** esta dentro de los targets establecidos (92 KB initial raw, ~33 KB gzipped).
- **Arquitectura frontend** es eficiente: stores granulares, debounce correcto, sin virtual scroll innecesario para el volumen de datos.
- **Cache SHA256** de frases es una solucion elegante que evita llamadas redundantes al LLM.
- **Infraestructura Docker** sigue buenas practicas (gzip, cache headers, OPcache, multi-stage builds).

Los hallazgos principales son:
1. **Indices SQL faltantes** (Alto) - El mas critico para escalabilidad futura.
2. **Fuentes innecesarias en build** (Medio) - Facil de corregir, impacto en tamano de imagen Docker.
3. **Sourcemaps expuestos** (Medio) - Riesgo de seguridad + performance (archivos grandes accesibles).

No se identificaron problemas criticos que impidan el cumplimiento de los targets de Phase 1. Las mejoras recomendadas son preventivas para Phase 2+ cuando el volumen de datos y trafico crezca.
