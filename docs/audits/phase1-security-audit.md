# Auditoria de Seguridad - Phase 1 (Backend)

> Revision de seguridad OWASP Top 10 (2021) del backend de HablaIA

**Ultima revision:** 5 de febrero de 2026
**Alcance:** Backend (`backend/`) - Symfony 7.4 + PHP 8.4 + Cycle ORM + PostgreSQL 16
**Fase:** Phase 1 MVP (comunicador publico, sin autenticacion)

---

## Metodologia

### Proceso

1. Auditoria inicial del codigo fuente completo del backend
2. Identificacion y clasificacion de hallazgos por severidad (Critico/Alto/Medio/Bajo/Informativo)
3. Descarte de falsos positivos tras verificacion (`.env` no trackeado en git, autenticacion fuera de alcance Phase 1)
4. Correccion de hallazgos confirmados en rama `fix/security-qa-audit`
5. Re-auditoria de verificacion post-fix

### Herramientas

| Herramienta | Uso |
|-------------|-----|
| Revision manual de codigo | Analisis de todos los archivos fuente PHP del backend |
| OWASP Top 10 (2021) | Checklist sistematico de categorias de vulnerabilidad |
| `composer audit` | Verificacion de CVEs conocidos en dependencias (resultado: 0 vulnerabilidades) |
| `git ls-files` | Verificacion de que archivos `.env` no estan trackeados en el repositorio |
| PHPStan level 8 | Analisis estatico de tipos (integrado en CI) |
| PestPHP | Ejecucion de tests para validar que los fixes no rompen funcionalidad |

---

## Resumen Ejecutivo

| Severidad | Hallazgos |
|-----------|-----------|
| Critico | 0 |
| Alto | 0 |
| Medio | 0 |
| Bajo | 0 |
| Informativo | 3 |

**Veredicto: FUERTE para Phase 1 MVP** - Sin hallazgos de seguridad pendientes.

---

## 1. OWASP Top 10 (2021) - Revision Sistematica

### A01:2021 - Broken Access Control

**Estado: ACEPTABLE para Phase 1**

No existe autenticacion, lo cual es por diseno para Phase 1 (comunicador AAC publico). El roadmap confirma que las cuentas de usuario llegan en Phase 3.

- **Rate limiter** aplicado al endpoint mas costoso (`POST /api/phrases/generate`) con `PHRASE_RATE_LIMIT` configurable (default 30) y `PHRASE_RATE_INTERVAL` (default 60 segundos) por IP.
- **Endpoints GET** (`/api/pictograms`, `/api/categories`) son solo lectura y bajo coste; la ausencia de rate limiting es aceptable para Phase 1.
- **CORS** configurado en `config/packages/nelmio_cors.yaml` con `origin_regex: true`, origen restringido a `localhost/127.0.0.1` via `CORS_ALLOW_ORIGIN`. Solo metodos `GET` y `POST` permitidos.

---

### A02:2021 - Cryptographic Failures

**Estado: PASS**

- Sin claves API ni secretos hardcodeados en el codigo fuente (verificado via grep).
- Ningun archivo `.env` esta trackeado en git (verificado con `git ls-files`).
- `.gitignore` raiz excluye `.env`, `.env.local`, `.env.*.local`.
- `.gitignore` del backend excluye `.env.dev` y `.env.test`.
- `.env.example` contiene solo placeholders (`change_me_in_production_please_use_32_chars`, `GEMINI_API_KEY=` vacio, `OPENAI_API_KEY=` vacio).
- Hash de cache de frases usa SHA-256 (`PictogramSequence::hash()`), apropiado para claves de cache.

---

### A03:2021 - Injection

**Estado: PASS**

**SQL Injection:**
- Cycle ORM con queries parametrizadas via patron repository.
- `SearchPictogram` sanitiza adicionalmente el query (`preg_replace('/[\'";\\\\%_]/', '', $trimmed)`) antes de pasar a `findByLabelLike`.
- Defensa en profundidad implementada correctamente.

**Prompt Injection:**
- `GeminiPhraseGenerator` y `RealOpenAIPhraseGenerator` sanitizan labels con `sanitizeLabel()`:
  - Elimina caracteres no alfanumericos/espacio/guion: `preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $label)`
  - Trunca a `MAX_LABEL_LENGTH` (50 chars)
  - Fallback a `'elemento'` para resultados vacios
- El usuario solo envia UUIDs; los labels se resuelven server-side desde la base de datos.

**SSRF:**
- `HttpImageDownloader` tiene allowlist explicita: `ALLOWED_HOSTS = ['static.arasaac.org', 'api.arasaac.org']`.
- Valida el host de la URL parseada antes de cualquier peticion HTTP.

**Path Traversal:**
- `HttpImageDownloader` valida que la ruta destino este dentro del `baseDirectory` configurado usando normalizacion de path que resuelve componentes `..`.

---

### A04:2021 - Insecure Design

**Estado: PASS**

- Arquitectura Clean Architecture con separacion de capas correcta.
- Capa Domain sin dependencias de framework.
- Inputs externos validados en multiples capas (Controller, Use Case, Domain Value Objects).
- `PhraseGeneratorFactory` por defecto usa `FakeOpenAIPhraseGenerator` para valores de proveedor desconocidos (fail-safe).
- Health checks exponen informacion minima.

---

### A05:2021 - Security Misconfiguration

**Estado: PASS**

- `framework.yaml` establece `handle_all_throwables: true`, asegurando que Symfony capture y sanitice todas las excepciones.
- Config monolog produccion (`when@prod`) usa handler `fingers_crossed` que solo loguea errores, con formato JSON a stderr.
- Container Docker ejecuta como usuario no-root (`appuser`, UID 1000).
- Configuracion de cookies de sesion: `cookie_secure: auto`, `cookie_samesite: lax`.
- Sin endpoints de debug expuestos.

---

### A06:2021 - Vulnerable and Outdated Components

**Estado: PASS**

- `composer audit` ejecutado: **0 vulnerabilidades conocidas** en dependencias.
- El proyecto usa PHP 8.4, Symfony 7.4 y PostgreSQL 16, todos versiones actuales.
- Se recomienda ejecutar `composer audit` periodicamente para verificar CVEs nuevos.

---

### A07:2021 - Identification and Authentication Failures

**Estado: NO APLICA para Phase 1**

No hay autenticacion implementada. Phase 1 es un comunicador AAC publico. Documentado en roadmap y README.

---

### A08:2021 - Software and Data Integrity Failures

**Estado: PASS**

- `composer.lock` asegura integridad de dependencias.
- Validacion de MIME type en imagenes descargadas (`finfo` buffer check para `image/png`, `image/jpeg`, `image/gif`).
- Parsing de respuestas LLM valida la estructura JSON esperada antes de usar.
- Sin deserializacion de datos no confiables.

---

### A09:2021 - Security Logging and Monitoring Failures

**Estado: PASS**

- Monolog configurado con formato JSON en dev y prod.
- Dev logs incluyen todos los canales excepto `event`.
- Prod logs usan `fingers_crossed` (solo loguea cuando hay errores) con JSON a stderr.
- Header `X-Correlation-Id` permitido a traves de CORS para distributed tracing.
- Violaciones de rate limit devuelven respuestas JSON estructuradas con informacion `retryAfter`.

---

### A10:2021 - Server-Side Request Forgery (SSRF)

**Estado: PASS**

`HttpImageDownloader` implementa proteccion SSRF basada en allowlist:

```php
private const array ALLOWED_HOSTS = [
    'static.arasaac.org',
    'api.arasaac.org',
];
```

`isAllowedUrl()` usa `parse_url()` para extraer el host y verifica contra la allowlist con comparacion estricta. `ArasaacApiClient` tambien solo hace peticiones a sus constantes hardcodeadas `API_BASE_URL` y `CDN_BASE_URL`.

---

## 2. Security Headers

Todas las respuestas HTTP incluyen los siguientes headers de seguridad via `SecurityHeadersSubscriber`:

| Header | Valor | Proposito |
|--------|-------|-----------|
| `X-Content-Type-Options` | `nosniff` | Previene MIME sniffing |
| `X-Frame-Options` | `DENY` | Previene clickjacking |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limita fuga de referrer |
| `X-XSS-Protection` | `0` | Default seguro (header deprecado, CSP lo reemplaza) |
| `Content-Security-Policy` | `default-src 'none'; frame-ancestors 'none'` | API pura: bloquea carga de contenido y framing |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Deshabilita APIs del navegador innecesarias |

El subscriber no sobreescribe headers ya establecidos por controllers (`if (!$response->headers->has($header))`).

---

## 3. Validacion de Inputs

Todos los inputs externos se validan antes del procesamiento:

| Endpoint | Input | Validacion |
|----------|-------|------------|
| `POST /api/phrases/generate` | JSON body `{pictogramIds: [...]}` | Parse JSON, campo presente, tipo array, rango 1-10, UUID v4 regex por elemento |
| `GET /api/pictograms?categoryId=` | Query param `categoryId` | `CategoryId::fromString()` valida formato UUID |
| `GET /api/pictograms/search?q=` | Query param `q` | Null/empty check, `sanitizeQuery()`, longitud min 2, max 100 |
| `GET /api/pictograms/{id}` | Path param `id` | `PictogramId::fromString()` valida formato UUID |
| `GET /api/categories/{id}` | Path param `id` | `CategoryId::fromString()` valida formato UUID |
| `GET /api/health/*` | Ninguno | Sin input de usuario |
| Comandos consola | Arguments/options | Symfony Console Input system (typed) |
| `ArasaacApiClient` | Parametro idioma | Validado contra allowlist `ALLOWED_LANGUAGES` |
| `HttpImageDownloader` | URL y path | Allowlist de host + prevencion path traversal |

---

## 4. Gestion de Secretos

| Verificacion | Resultado |
|-------------|-----------|
| Archivos `.env` en git | 0 trackeados |
| Claves API hardcodeadas en fuente | 0 encontradas |
| Errores de base de datos | Mensaje generico `'Database unavailable'` |
| Mensajes de error de API | Strings estaticos (`'Phrase generation request failed'`) |
| Stack traces en respuestas | 0 - controllers capturan excepciones y devuelven JSON |
| Password en docker-compose | `${POSTGRES_PASSWORD:?}` con check obligatorio |
| Valores en `.env.example` | Solo placeholders |
| Logging de secretos | 0 en codigo fuente |

---

## 5. Trusted Proxies (Docker/Nginx)

Configuracion en `config/packages/framework.yaml`:

```yaml
trusted_proxies: '%env(default::TRUSTED_PROXIES)%'
trusted_headers: ['x-forwarded-for', 'x-forwarded-proto']
```

- `trusted_proxies` configurable via entorno, default vacio (sin confianza) si `TRUSTED_PROXIES` no esta definido.
- `trusted_headers` restringido a solo `x-forwarded-for` y `x-forwarded-proto` (no incluye `x-forwarded-host` ni `x-forwarded-port`).
- En Docker: `TRUSTED_PROXIES=REMOTE_ADDR` confiere confianza al upstream directo (Nginx).
- Documentacion visual en `docs/diagrams/docker-infrastructure.md`.

---

## 6. Notas Informativas

> Sin accion requerida para Phase 1. Documentadas para referencia futura.

### INFO-01: HSTS Header Ausente

`SecurityHeadersSubscriber` no establece `Strict-Transport-Security`. Correcto para Phase 1 (desarrollo usa HTTP). En produccion con TLS, considerar anadir HSTS via Nginx (reverse proxy), no via aplicacion, ya que la terminacion TLS ocurre en la capa del proxy.

### INFO-02: APP_SECRET Default en docker-compose

El default `change_me_in_production_32_chars!` es aceptable para desarrollo local. La documentacion de despliegue a produccion debe requerir explicitamente generar un secreto criptograficamente aleatorio.

### INFO-03: Alcance del Rate Limiting

Solo `POST /api/phrases/generate` tiene rate limiting. Los endpoints GET son solo lectura y bajo coste. Si el abuso de endpoints GET se convierte en problema en produccion, rate limiting a nivel de Nginx seria la capa apropiada.

---

## 7. Recomendaciones para Futuras Fases

| Fase | Accion de Seguridad Requerida |
|------|-------------------------------|
| Phase 2 (Contexto temporal) | Verificar que datos temporales no expongan informacion sensible |
| Phase 3 (Perfiles usuario) | **Auditoria completa obligatoria**: autenticacion, sesiones, autorizacion, CSRF, password hashing |
| Phase 4 (TTS Premium) | Proteger claves ElevenLabs, validar audio responses |
| Phase 5 (PWA/Offline) | Service Worker security, IndexedDB encryption para datos sensibles |
| Phase 6 (Voice Cloning) | GDPR compliance, consentimiento explicito, cifrado de audio, derecho a eliminacion |

---

## Conclusion

El backend de HablaIA demuestra una postura de seguridad madura para un MVP Phase 1:

- Validacion de inputs en cada capa (Controller, Application, Domain)
- Sanitizacion de outputs con mensajes de error genericos y headers OWASP
- Proteccion SSRF con allowlists de dominio
- Prevencion de path traversal con normalizacion de path
- Mitigacion de prompt injection con filtrado de caracteres
- Rate limiting en el endpoint mas costoso
- Gestion de secretos enteramente via variables de entorno
- Ejecucion de container como non-root
- Logging JSON estructurado en todos los entornos
- 0 vulnerabilidades conocidas en dependencias (`composer audit`)

**El codebase esta listo para despliegue de Phase 1 desde perspectiva de seguridad.**
