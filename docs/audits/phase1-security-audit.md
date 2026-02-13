# Auditoria de Seguridad - FASE 1 (Full Stack)

> Revision de seguridad OWASP Top 10 (2021) del proyecto completo HablaIA

**Ultima revision:** 13 de febrero de 2026<br>
**Revision anterior:** 11 de febrero de 2026<br>
**Alcance:** Full stack - Backend + Frontend + Docker/Infra + CI/CD<br>
**Fase:** Fase 1 MVP (comunicador publico, sin autenticacion)<br>
**Evaluador:** @security_auditor

---

## Contenido

- [Metodologia](#metodologia)
- [Resumen Ejecutivo](#resumen-ejecutivo)
- [1. Backend - OWASP Top 10](#1-backend---owasp-top-10-2021)
- [2. Backend - Security Headers](#2-backend---security-headers)
- [3. Backend - Validacion de Inputs](#3-backend---validacion-de-inputs)
- [4. Frontend Security](#4-frontend-security)
- [5. Docker / Infraestructura](#5-docker--infraestructura)
- [6. GitHub Actions / CI/CD](#6-github-actions--cicd)
- [7. Hallazgos Corregidos](#7-hallazgos-corregidos-desde-revision-anterior)
- [8. Notas Informativas](#8-notas-informativas)
- [9. Areas Verificadas](#9-areas-verificadas-como-correctas)
- [10. Gestion de Secretos](#10-gestion-de-secretos)
- [11. Trusted Proxies](#11-trusted-proxies-dockernginx)
- [12. Trabajo Futuro](#12-trabajo-futuro-de-seguridad)
- [Conclusion](#conclusion)

---

## Metodologia

### Proceso

1. Revision asistida por IA (LLM como @security_auditor) del backend (Symfony 7.4 + PHP 8.4 + Cycle ORM)
2. Revision del frontend (Vue.js 3 + TypeScript, incluyendo Sentry, accesibilidad, TTS, SearchBar, validacion Zod)
3. Revision de Docker/infraestructura (Dockerfiles, Nginx, docker-compose, CD pipeline)
4. Revision de CI/CD (GitHub Actions workflows, pre-commit hooks)
5. Clasificacion de hallazgos por severidad (Critico/Alto/Medio/Bajo/Informativo)
6. Separacion de hallazgos con accion requerida vs informativos

### Herramientas

| Herramienta | Uso |
|-------------|-----|
| Revision de codigo asistida por IA | Analisis de todos los archivos fuente (PHP, TypeScript, Vue, YAML, Dockerfile, nginx.conf) |
| OWASP Top 10 (2021) | Checklist sistematico de categorias de vulnerabilidad |
| `composer audit` | Verificacion de CVEs en dependencias PHP (integrado en CI: `backend-ci.yml`) |
| `npm audit --omit=dev` | Verificacion de CVEs en dependencias JS (integrado en CI: `frontend-ci.yml`) |
| PHPStan level 8 | Analisis estatico de tipos PHP (integrado en CI) |
| TypeScript strict | Analisis estatico de tipos frontend (`strict: true` en tsconfig) |
| ESLint + vue-tsc | Linting y type-checking frontend (integrado en CI) |

---

## Resumen Ejecutivo

| Severidad | Hallazgos |
|-----------|-----------|
| Critico | 0 |
| Alto | 0 |
| Medio | 0 |
| Bajo | 0 |
| Informativo | 6 |

**Veredicto: FUERTE para FASE 1 MVP** - 0 hallazgos con accion requerida. Los 3 hallazgos de la revision anterior (1 medio, 2 bajos) han sido resueltos o descartados con justificacion.

### Cambios respecto a revision anterior (11 feb 2026)

| Hallazgo | Severidad anterior | Estado actual | PR |
|----------|-------------------|---------------|-----|
| SEC-01: Security headers en sub-locations Nginx | Medio | **CORREGIDO** | PR #71 |
| SEC-02: Sourcemaps en produccion | Bajo | **CORREGIDO** | PR #71 |
| SEC-03: Sentry 404 filtering | Bajo | **DESCARTADO** (decision deliberada) | - |

---

## 1. Backend - OWASP Top 10 (2021)

### A01:2021 - Broken Access Control

**Estado: ACEPTABLE para FASE 1**

No existe autenticacion, lo cual es por diseno (comunicador AAC publico).

- **Rate limiting dual** en `POST /api/phrases/generate`:
  - Per-minute: `sliding_window`, `PHRASE_RATE_LIMIT` (default 30), `PHRASE_RATE_INTERVAL` (default 60s)
  - Daily: `fixed_window`, `PHRASE_DAILY_LIMIT` (default 500/dia)
- **Endpoints GET** (`/api/pictograms`, `/api/categories`) son solo lectura y bajo coste.
- **CORS** en `nelmio_cors.yaml`: `origin_regex: true`, origen restringido via `CORS_ALLOW_ORIGIN`, solo `GET`/`POST` permitidos.

### A02:2021 - Cryptographic Failures

**Estado: PASS**

- Sin claves API ni secretos hardcodeados en el codigo fuente.
- Ningun archivo `.env` trackeado en git (`.gitignore` raiz excluye `.env`, `.env.local`, `.env.*.local`; backend excluye `.env.dev`, `.env.test`).
- `.env.example` contiene solo placeholders (`CHANGE_ME_RANDOM_STRING_32_CHARS`, `GEMINI_API_KEY=` vacio, `OPENAI_API_KEY=` vacio).
- Hash de cache de frases usa SHA-256 (`PictogramSequence::hash()`).

### A03:2021 - Injection

**Estado: PASS**

**SQL Injection:**
- Cycle ORM con queries parametrizadas via patron repository.
- `SearchPictogram::sanitizeQuery()` sanitiza adicionalmente: `preg_replace('/[\'";\\\\%_]/', '', $trimmed)`.
- `CyclePictogramRepository::findByLabelLike()` usa `Fragment` con parametro vinculado (no concatenacion).
- Defensa en profundidad correcta.

**Prompt Injection:**
- `GeminiPhraseGenerator` y `RealOpenAIPhraseGenerator` sanitizan labels con `sanitizeLabel()`:
  - Elimina caracteres no alfanumericos/espacio/guion: `preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $label)`
  - Trunca a `MAX_LABEL_LENGTH` (50 chars)
  - Fallback a `'elemento'` para resultados vacios
- El usuario solo envia UUIDs; los labels se resuelven server-side desde la base de datos.

**SSRF:**
- `HttpImageDownloader` tiene allowlist: `ALLOWED_HOSTS = ['static.arasaac.org', 'api.arasaac.org']`.
- Valida host via `parse_url()` antes de cualquier peticion.

**Path Traversal:**
- `HttpImageDownloader` valida que la ruta destino este dentro del `baseDirectory` con normalizacion que resuelve componentes `..`.

### A04:2021 - Insecure Design

**Estado: PASS**

- Clean Architecture con Domain sin dependencias de framework.
- Inputs validados en multiples capas (Controller, Application, Domain Value Objects).
- `PhraseGeneratorFactory` usa `FakePhraseGenerator` como fallback seguro para proveedores desconocidos.
- Health checks exponen informacion minima.

### A05:2021 - Security Misconfiguration

**Estado: PASS**

- `handle_all_throwables: true` en Symfony.
- Monolog produccion: `fingers_crossed` con `excluded_http_codes: [404, 405]`, JSON a stderr.
- Container Docker ejecuta como non-root (`www-data` en produccion, `appuser` en dev).
- Production Dockerfile: `expose_php=Off`, `display_errors=Off`, `log_errors=On`.
- Cookies de sesion: `cookie_secure: auto`, `cookie_samesite: lax`.

### A06:2021 - Vulnerable and Outdated Components

**Estado: PASS**

- `composer audit` integrado en CI (`backend-ci.yml` linea 63).
- Stack actual: PHP 8.4, Symfony 7.4, PostgreSQL 16 (versiones actuales).
- Nota: El usuario debe ejecutar `composer audit` y `npm audit` manualmente para verificar el estado actual fuera del CI. Los resultados del CI mas reciente son la referencia.

### A07:2021 - Identification and Authentication Failures

**Estado: NO APLICA para FASE 1**

No hay autenticacion. Fase 1 es un comunicador SAAC publico.

### A08:2021 - Software and Data Integrity Failures

**Estado: PASS**

- `composer.lock` y `package-lock.json` aseguran integridad de dependencias.
- CI usa `npm ci` (instala desde lockfile exacto).
- Validacion MIME type en imagenes descargadas (`finfo` buffer check).
- Parsing de respuestas LLM valida estructura JSON esperada.

### A09:2021 - Security Logging and Monitoring Failures

**Estado: PASS**

- Monolog JSON en dev y prod.
- Prod: `fingers_crossed` (solo loguea en errores) con JSON a stderr.
- Header `X-Correlation-Id` expuesto via CORS.
- Sentry integrado (`sentry-symfony`), configurable via `SENTRY_DSN`.
- Violaciones de rate limit con respuestas JSON estructuradas.

### A10:2021 - Server-Side Request Forgery (SSRF)

**Estado: PASS**

`HttpImageDownloader` con allowlist estricta. `ArasaacApiClient` solo hace peticiones a constantes hardcodeadas (`API_BASE_URL`, `CDN_BASE_URL`).

---

## 2. Backend - Security Headers

Todas las respuestas HTTP incluyen headers de seguridad via `SecurityHeadersSubscriber`:

| Header | Valor | Proposito |
|--------|-------|-----------|
| `X-Content-Type-Options` | `nosniff` | Previene MIME sniffing |
| `X-Frame-Options` | `DENY` | Previene clickjacking |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limita fuga de referrer |
| `X-XSS-Protection` | `0` | Safe default (header deprecado) |
| `Content-Security-Policy` | `default-src 'none'; frame-ancestors 'none'` | API pura: bloquea contenido y framing |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Deshabilita APIs innecesarias |

---

## 3. Backend - Validacion de Inputs

| Endpoint | Input | Validacion |
|----------|-------|------------|
| `POST /api/phrases/generate` | JSON body `{pictogramIds: [...]}` | Parse JSON, campo presente, tipo array, rango 1-10, UUID v4 regex por elemento |
| `GET /api/pictograms?categoryId=` | Query param `categoryId` | `CategoryId::fromString()` valida UUID |
| `GET /api/pictograms/search?q=` | Query param `q` | Null/empty check, `sanitizeQuery()`, longitud min 2, max 100 |
| `GET /api/pictograms/{id}` | Path param `id` | `PictogramId::fromString()` valida UUID |
| `GET /api/categories/{id}` | Path param `id` | `CategoryId::fromString()` valida UUID |
| `GET /api/health/*` | Ninguno | Sin input de usuario |
| `ArasaacApiClient` | Parametro idioma | Validado contra allowlist `ALLOWED_LANGUAGES` |
| `HttpImageDownloader` | URL y path | Allowlist de host + prevencion path traversal |

---

## 4. Frontend Security

### 4.1 XSS (Cross-Site Scripting)

**Estado: PASS**

- **0 usos de `v-html`** en todo el frontend. Verificado via grep en todos los archivos `.vue`.
- **0 usos de `innerHTML`** en todo el frontend. Verificado via grep en todos los archivos `.ts` y `.vue`.
- Todos los datos dinamicos se renderizan via interpolacion segura de Vue (`{{ }}`) o bindings de atributos (`:src`, `:alt`, `:aria-label`), que escapan HTML automaticamente.
- Las variaciones de frases del LLM se renderizan como texto plano: `<span class="text-accessible-text">{{ variation }}</span>`.
- Los mensajes de error se renderizan como texto plano: `<span class="text-red-700">{{ store.error }}</span>`.
- Etiquetas de pictogramas se renderizan como texto plano: `{{ pictogram.label }}`.

### 4.2 Validacion de Datos de API (Zod Schemas)

**Estado: PASS**

Todas las respuestas de la API se validan con schemas Zod en el `ApiClient`:

| Schema | Validaciones |
|--------|-------------|
| `PictogramSchema` | `id`: UUID, `arasaacId`: int >= 1, `categoryId`: UUID, `label`: string 1-100, `imagePath`: string 1-500 |
| `CategorySchema` | `id`: UUID, `name`: string 1-50, `icon`: string nullable, `colorHex`: regex `#[0-9A-Fa-f]{6}`, `displayOrder`: int >= 0 |
| `PhraseResponseSchema` | `variations`: array of string max 500, 1-3 items; `source`: enum cache/generated/fallback; `sequenceHash`: string 1-128; `pictogramIds`: array of UUID 1-10 |

El `ApiClient.get()` y `ApiClient.post()` llaman `schema.parse(data)` en cada respuesta. Datos malformados del servidor lanzan error de Zod, capturado por los stores como error generico.

### 4.3 Sanitizacion de Inputs de Usuario

**Estado: PASS**

- **SearchBar:** El input de busqueda (`v-model="query"`) se pasa como string al store, que llama `repository.search(query)`. El `HttpPictogramRepository` codifica el query con `encodeURIComponent(query)` antes de incluirlo en la URL. La sanitizacion real ocurre server-side en `SearchPictogram::sanitizeQuery()`.
- **PhraseStore:** Solo envia UUIDs (`selectedPictograms.map(p => p.id)`) al backend.
- **CategoryStore:** Solo envia IDs de categoria (strings).
- No hay formularios de texto libre que se envien al servidor.

### 4.4 Secretos en Codigo

**Estado: PASS**

- **Sentry DSN:** Leido desde `import.meta.env.VITE_SENTRY_DSN` en `main.ts`. No hardcodeado.
- **0 API keys** en el codigo fuente del frontend.
- `frontend/.env.example` contiene solo placeholders vacios (`VITE_SENTRY_DSN=`).
- `frontend/.gitignore` excluye `.env.local` y `.env.*.local`.
- El Sentry DSN se inyecta como build arg en `nginx.Dockerfile` y es visible en el bundle JS de produccion. Esto es **por diseno** (ver INFO-04).

### 4.5 CORS

**Estado: PASS**

- En desarrollo: Vite proxy (`vite.config.ts`) redirige `/api` y `/pictograms` al backend. No hay peticiones cross-origin.
- En produccion: Nginx sirve frontend y API desde el mismo origen (puerto 80). No hay CORS cross-origin.
- Backend CORS (`nelmio_cors.yaml`) restringido a `CORS_ALLOW_ORIGIN` (regex localhost/127.0.0.1 en dev). En produccion, `CORS_ALLOW_ORIGIN` debe coincidir con el dominio real.

### 4.6 Dependencias Frontend

**Estado: PASS (verificacion via CI)**

- `npm audit --omit=dev` integrado en CI (`frontend-ci.yml` linea 54).
- Stack: Vue 3.5, Vite 6, TypeScript 5.6, Zod 3.24, Sentry Vue 10.38.
- No hay dependencias con CVEs conocidos en la ultima ejecucion del CI.
- `@vueuse/core` eliminado de dependencies (era dependencia sin uso, removida en PR #72).

---

## 5. Docker / Infraestructura

### 5.1 Dockerfile de Desarrollo (`docker/php/Dockerfile`)

**Estado: PASS**

- Usuario non-root: `appuser` (UID 1000).
- Base imagen alpine (superficie de ataque reducida).
- Symfony CLI instalado via curl desde dominio oficial (`get.symfony.com`).

### 5.2 Dockerfile de Produccion - Backend (`docker/production/php.Dockerfile`)

**Estado: PASS**

- Usuario non-root: `www-data`.
- Sin Symfony CLI (solo `php-fpm`).
- Opcache configurado para produccion (`validate_timestamps=0`).
- `expose_php=Off`, `display_errors=Off`, `log_errors=On`.
- Dependencias solo de produccion: `composer install --no-dev --optimize-autoloader`.
- Permisos: `chown -R www-data:www-data var/ public/`.
- Build placeholder para `APP_SECRET` durante build (no el secreto real).

### 5.3 Dockerfile de Produccion - Nginx (`docker/production/nginx.Dockerfile`)

**Estado: PASS**

- Multi-stage build: Stage 1 compila frontend con Node, Stage 2 copia solo `dist/` a Nginx.
- `npm ci --ignore-scripts` evita ejecucion de scripts de dependencias.
- Imagen base `nginx:1.27-alpine`.
- `HEALTHCHECK` integrado.
- El `VITE_SENTRY_DSN` se pasa como build arg (necesario para Vite en build time).

### 5.4 Nginx Production Config

**Estado: PASS**

Security headers aplicados correctamente en **todos** los locations via `include /etc/nginx/security-headers.conf`:

| Location | Security headers | Cache headers |
|----------|-----------------|---------------|
| Server block (global) | `include security-headers.conf` | - |
| `/pictograms/` | `include security-headers.conf` | `expires 7d; Cache-Control: public, immutable` |
| `/assets/` | `include security-headers.conf` | `expires 1y; Cache-Control: public, immutable` |
| `/health` | `include security-headers.conf` | - |
| `/index.html` | `include security-headers.conf` | `Cache-Control: no-cache, no-store, must-revalidate` |

**Verificado:** `docker/production/nginx.conf` lineas 24, 64, 75, 85, 93.

Security headers en `security-headers.conf`:

| Header | Valor |
|--------|-------|
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `Content-Security-Policy` | `default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' https://static.arasaac.org data:; connect-src 'self' https://*.ingest.de.sentry.io; font-src 'self'; frame-ancestors 'none'` |

- `server_tokens off` oculta la version de Nginx.
- CSP permite `'unsafe-inline'` para styles (necesario para `:style` bindings de Vue con Fitzgerald Key colors — ver INFO-03).

### 5.5 docker-compose.prod.yml

**Estado: PASS**

- `POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:?}` - obligatorio, falla si no esta definido.
- `APP_SECRET: ${APP_SECRET:?}` - obligatorio en produccion.
- `CORS_ALLOW_ORIGIN: ${CORS_ALLOW_ORIGIN:?}` - obligatorio en produccion.
- PostgreSQL no expone puertos al host (solo red interna `hablaia_network`).
- Resource limits en todos los servicios (`memory`, `cpus`).
- `restart: unless-stopped` en todos los servicios.
- Volumen `pictograms_data` compartido entre backend (rw) y nginx (ro).
- Sentry DSN configurable via entorno (vacio = deshabilitado).

### 5.6 docker-compose.yml (Desarrollo)

**Estado: ACEPTABLE para desarrollo**

- PostgreSQL expone puerto 5432 al host (solo desarrollo).
- `APP_SECRET` tiene default inseguro (`change_me_in_production_32_chars!`). Aceptable para desarrollo.
- Swagger UI solo activo con profile `dev`.

---

## 6. GitHub Actions / CI/CD

### 6.1 Backend CI (`backend-ci.yml`)

**Estado: PASS**

- `composer audit` integrado (linea 63).
- PHPStan level 8 (linea 66).
- Tests completos (linea 69).
- Concurrency group evita ejecuciones paralelas en la misma rama.

### 6.2 Frontend CI (`frontend-ci.yml`)

**Estado: PASS**

- `npm audit --omit=dev` integrado (linea 54).
- ESLint + vue-tsc type check.
- Tests con coverage.
- Build de produccion verificado.
- Lighthouse CI para accesibilidad.

### 6.3 CD (`cd.yml`)

**Estado: PASS**

- `permissions: contents: read` (principio de minimo privilegio).
- Concurrency con `cancel-in-progress: false` (evita interrumpir deploys).
- SSH key limpiada en step `always` (linea 116).
- `StrictHostKeyChecking=yes` (no acepta hosts desconocidos).
- Rollback automatico si health check falla (6 intentos).
- No usa third-party actions para deploy (solo SSH nativo).
- Secretos via `secrets.SSH_PRIVATE_KEY`, `secrets.SERVER_HOST`, `secrets.SERVER_USER`.
- Build args de Vite exportados desde `.env` del servidor (linea 57-59).

### 6.4 Commitlint CI (`commitlint-ci.yml`)

**Estado: PASS**

- Valida formato Conventional Commits en PRs.

---

## 7. Hallazgos Corregidos desde Revision Anterior

### SEC-01: Security headers en sub-locations Nginx — CORREGIDO (PR #71)

**Ubicacion original:** `docker/production/nginx.conf`, locations `/pictograms/`, `/assets/`, `/health`

**Problema original (11 feb):** Nginx `add_header` en un bloque `location` no hereda los `add_header` del bloque `server` padre. Las locations que definian sus propios `add_header` no incluian los security headers.

**Correccion aplicada:** Se creo `docker/production/security-headers.conf` con todos los headers de seguridad, y se agrego `include /etc/nginx/security-headers.conf;` en cada location que define `add_header` propio. Verificado en lineas 24, 64, 75, 85, 93 de `nginx.conf`.

**Estado: CORREGIDO.** Verificado en produccion (13 feb 2026): `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, CSP completa, `server_tokens off`, sin `X-Powered-By`.

### SEC-02: Sourcemaps en produccion — CORREGIDO (PR #71)

**Ubicacion original:** `frontend/vite.config.ts`, linea 29

**Problema original (11 feb):** `sourcemap: true` generaba sourcemaps referenciados desde los JS bundles, accesibles en DevTools del navegador.

**Correccion aplicada:** Cambiado a `sourcemap: 'hidden'` (`vite.config.ts` linea 29). Los sourcemaps se generan (disponibles para Sentry upload) pero no se referencian desde los bundles, por lo que no son accesibles en el navegador.

**Estado: CORREGIDO.**

### SEC-03: Sentry 404 filtering — DESCARTADO (decision deliberada)

**Ubicacion:** `backend/config/packages/sentry.yaml`

**Hallazgo original (11 feb, Bajo):** La configuracion de Sentry no filtra excepciones 404. Bots que escanean rutas comunes generan ruido.

**Decision:** Se decidio **no filtrar** los 404 en Sentry. Los errores 404 son utiles para detectar escaneos automatizados y ataques de reconocimiento contra la aplicacion (`/wp-admin`, `/.env`, `/phpinfo.php`, etc.). El coste en cuota de Sentry (5K errores/mes free tier) es aceptable dado el bajo trafico actual.

**Estado: DESCARTADO por decision informada.**

---

## 8. Notas Informativas

> Sin accion requerida para Fase 1. Documentadas para referencia futura.

### INFO-01: HSTS Header Ausente

`nginx.conf` y `SecurityHeadersSubscriber` no establecen `Strict-Transport-Security`. Correcto para Fase 1 (HTTP en produccion actual). Cuando se configure TLS (Let's Encrypt), anadir HSTS en Nginx:
```nginx
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
```

### INFO-02: APP_SECRET Default en docker-compose de desarrollo

El default `change_me_in_production_32_chars!` es aceptable para desarrollo local. En produccion, `docker-compose.prod.yml` requiere `APP_SECRET:?` (falla si no esta definido).

### INFO-03: CSP con 'unsafe-inline' para Styles

`nginx.conf` permite `style-src 'self' 'unsafe-inline'` porque Vue usa `:style` bindings para los colores Fitzgerald Key de categorias (ej. `:style="{ borderTopColor: category.colorHex }"`). Esto es una concesion aceptable. Eliminar `unsafe-inline` requeriria migrar los estilos dinamicos a clases CSS o custom properties, lo cual no es viable sin cambiar la logica de colores Fitzgerald Key.

### INFO-04: Sentry DSN Publico en Frontend

El `VITE_SENTRY_DSN` se embebe en el bundle JS de produccion (es una variable `VITE_` y Vite la incrusta en build time). Esto es **por diseno de Sentry**: el DSN del frontend es publico y solo permite enviar eventos. El rate limiting y la proteccion contra abuso son responsabilidad de Sentry Cloud. Documentado como informativo.

### INFO-05: TLS No Configurado

El despliegue actual en Hetzner usa HTTP (puerto 80). TLS con Let's Encrypt es recomendable antes de uso real por usuarios. No es critico para Fase 1 MVP en evaluacion academica.

### INFO-06: Alcance del Rate Limiting

Solo `POST /api/phrases/generate` tiene rate limiting (dual: por minuto + diario). Los endpoints GET son solo lectura y bajo coste. Si el abuso de endpoints GET se convierte en problema, rate limiting a nivel de Nginx seria la capa apropiada.

---

## 9. Areas Verificadas como Correctas

Las siguientes areas se revisaron exhaustivamente y no presentan hallazgos:

| Area | Verificacion | Resultado |
|------|-------------|-----------|
| SQL Injection | Cycle ORM parametrizado + sanitizeQuery() + Fragment con parametros | PASS |
| Prompt Injection | sanitizeLabel() + resolucion server-side de labels | PASS |
| SSRF | Allowlist estricta en HttpImageDownloader y ArasaacApiClient | PASS |
| Path Traversal | Normalizacion de rutas en HttpImageDownloader | PASS |
| XSS | 0 usos de v-html/innerHTML, interpolacion segura de Vue | PASS |
| Validacion frontend | Zod schemas en todas las respuestas API | PASS |
| Secretos en codigo | 0 hardcodeados, todo via env vars | PASS |
| Containers | Non-root en ambos Dockerfiles de produccion | PASS |
| CI/CD | Auditorias de deps, tipo estricto, tests, deploy con rollback | PASS |
| Security headers | Todos los locations incluyen security-headers.conf | PASS |
| Sourcemaps | `hidden` — no accesibles en navegador | PASS |
| Rate limiting | Dual (per-minute + daily) en endpoint costoso | PASS |
| CORS | Mismo origen en produccion, restringido en desarrollo | PASS |
| Error responses | JSON estructurado via ApiExceptionSubscriber, sin stack traces | PASS |

---

## 10. Gestion de Secretos

| Verificacion | Resultado |
|-------------|-----------|
| Archivos `.env` en git | 0 trackeados |
| Claves API hardcodeadas en fuente (backend) | 0 encontradas |
| Claves API hardcodeadas en fuente (frontend) | 0 encontradas |
| Sentry DSN hardcodeado | 0 (leido de entorno en ambos lados) |
| Errores de BD expuestos al usuario | No (mensaje generico `'Database unavailable'`) |
| Mensajes de error de API | Strings estaticos (`'Phrase generation request failed'`) |
| Stack traces en respuestas | 0 (controllers capturan excepciones, devuelven JSON) |
| Password en docker-compose prod | `${POSTGRES_PASSWORD:?}` obligatorio |
| Valores en `.env.example` | Solo placeholders |
| Logging de secretos | 0 en codigo fuente |
| `expose_php` en produccion | `Off` |
| `display_errors` en produccion | `Off` |

---

## 11. Trusted Proxies (Docker/Nginx)

Configuracion en `framework.yaml`:

```yaml
trusted_proxies: '%env(default::TRUSTED_PROXIES)%'
trusted_headers: ['x-forwarded-for', 'x-forwarded-proto']
```

- `trusted_proxies` configurable via entorno, default vacio si `TRUSTED_PROXIES` no esta definido.
- `trusted_headers` restringido a `x-forwarded-for` y `x-forwarded-proto` (no `x-forwarded-host`).
- En Docker produccion: `TRUSTED_PROXIES=REMOTE_ADDR` confiere confianza al upstream directo (Nginx).

---

## 12. Trabajo Futuro de Seguridad

Las siguientes mejoras de seguridad quedan documentadas para cuando el proyecto evolucione:

| Area | Descripcion |
|------|-------------|
| TLS/HTTPS | Configurar Let's Encrypt + HSTS cuando se use dominio propio |
| Autenticacion | Auditoria completa obligatoria si se implementan perfiles de usuario (JWT/sessions, CSRF, Argon2id, GDPR) |
| CSP | Eliminar `unsafe-inline` migrando estilos dinamicos a CSS custom properties |
| Dependency updates | Automatizar con Dependabot o Renovate |

---

## Conclusion

El proyecto HablaIA demuestra una postura de seguridad madura para un MVP Fase 1 full stack:

**Backend:**
- Validacion de inputs en cada capa (Controller, Application, Domain)
- Sanitizacion de outputs con mensajes de error genericos y headers OWASP
- Proteccion SSRF con allowlists, prevencion de path traversal, mitigacion de prompt injection
- Rate limiting dual (por minuto + diario) en el endpoint mas costoso
- Gestion de secretos 100% via variables de entorno
- Ejecucion non-root en containers
- Logging JSON estructurado con `excluded_http_codes` para reducir ruido

**Frontend:**
- 0 usos de `v-html` o `innerHTML` (inmune a XSS via DOM)
- Validacion Zod en todas las respuestas de API (defensa contra datos malformados)
- `encodeURIComponent` en parametros de URL
- Sin secretos en codigo fuente
- TypeScript strict mode habilitado

**Infraestructura:**
- Dockerfiles con usuarios non-root y multi-stage builds
- CSP completa en Nginx con allowlist para Sentry y ARASAAC
- Security headers en **todos** los locations (corregido en PR #71)
- Sourcemaps ocultos en produccion (corregido en PR #71)
- `server_tokens off`, `expose_php=Off`
- PostgreSQL sin puertos expuestos en produccion
- CI con auditorias de seguridad automatizadas en ambos stacks
- CD con rollback automatico y limpieza de SSH keys

**Hallazgos pendientes: 0.** Los 3 hallazgos de la revision anterior han sido corregidos (SEC-01, SEC-02) o descartados con justificacion documentada (SEC-03).

**El proyecto esta listo para despliegue de Fase 1 desde perspectiva de seguridad.**
