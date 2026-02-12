# Security - HablaIA Backend

> Resumen de postura de seguridad. Informe detallado en [`docs/audits/phase1-security-audit.md`](audits/phase1-security-audit.md)

**Ultima revision:** 13 de febrero de 2026
**Scope:** Backend Symfony 7.4 + Cycle ORM + PostgreSQL 16
**Fase:** Phase 1 MVP (sin autenticacion)

---

## Resumen OWASP Top 10

| Categoria | Estado | Notas |
|---|---|---|
| A01 Broken Access Control | 🟡 Sin auth (por diseno en Phase 1) | Rate limiting en generacion de frases y busqueda. Auth planificada para Phase 3 |
| A02 Cryptographic Failures | 🟢 OK | `.env` no trackeado en git. Solo placeholders en `.env.example` |
| A03 Injection | 🟢 OK | Cycle ORM parametrizado. UUIDs validados. Query sanitizada. Prompt injection mitigado |
| A04 Insecure Design | 🟢 OK | Clean Architecture. Validacion en 3 capas (Controller, Application, Domain) |
| A05 Security Misconfiguration | 🟢 OK | 6 cabeceras OWASP. CORS restringido. Container non-root. Trusted proxies configurado |
| A06 Vulnerable Components | 🟢 OK | `composer audit`: 0 vulnerabilidades |
| A07 Auth Failures | 🟡 N/A en Phase 1 | Se implementara en Phase 3 (perfiles de usuario) |
| A08 Software Integrity | 🟢 OK | `composer.lock` commitado. Validacion MIME en descargas |
| A09 Logging | 🟢 OK | Monolog JSON en dev y prod. `fingers_crossed` en produccion. `X-Correlation-Id` en CORS |
| A10 SSRF | 🟢 OK | Allowlist de dominios ARASAAC. Validacion MIME y path traversal |

---

## Protecciones implementadas

### Cabeceras de seguridad (SecurityHeadersSubscriber)

Interceptor Symfony (`kernel.response`) que anade a todas las respuestas:

| Header | Valor |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `X-XSS-Protection` | `0` |
| `Content-Security-Policy` | `default-src 'none'; frame-ancestors 'none'` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |

### Validacion de inputs

- **UUIDs:** Validados en Value Objects (`CategoryId`, `PictogramId`, `PhraseId`) + validacion UUID v4 en controller
- **Search query:** Minimo 2 chars, maximo 100 chars, sanitizada contra SQL injection
- **Pictogram sequence:** Entre 1 y 10 pictogramas por peticion (validado en controller y dominio)
- **LLM labels:** Sanitizados contra prompt injection (solo letras, numeros, espacios, guiones) en todos los proveedores

### SSRF (HttpImageDownloader)

- Allowlist de dominios: solo `static.arasaac.org` y `api.arasaac.org`
- Validacion de path traversal con normalizacion de ruta
- Validacion de MIME type (solo `image/png`, `image/jpeg`, `image/gif`)
- Permisos de directorio `0755`

### CORS

- Origen restringido por regex (configurable via `CORS_ALLOW_ORIGIN`)
- Metodos limitados a `GET` y `POST`

### Rate limiting

- `POST /api/phrases/generate`: doble proteccion por IP para evitar abuso de costes LLM (Gemini/OpenAI). Protege tambien peticiones cacheadas como efecto colateral
  - **Per-minute:** 30 req/60s (sliding window) via `PHRASE_RATE_LIMIT` / `PHRASE_RATE_INTERVAL`
  - **Daily:** 500 req/dia (fixed window) via `PHRASE_DAILY_LIMIT`
- `GET /api/pictograms/search`: proteccion por IP para evitar abuso de peticiones a ARASAAC y escritura en disco
  - **Per-minute:** 30 req/60s (sliding window) via `SEARCH_RATE_LIMIT` / `SEARCH_RATE_INTERVAL`

### Trusted Proxies (Docker/Nginx)

- `TRUSTED_PROXIES=REMOTE_ADDR` para que el rate limiter use la IP real del cliente detras de Nginx
- Solo headers `x-forwarded-for` y `x-forwarded-proto` confiados
- Documentacion visual en [`docs/diagrams/docker-infrastructure.md`](diagrams/docker-infrastructure.md)

### Gestion de secretos

- Archivos `.env` en `.gitignore` (0 trackeados en git)
- Solo `.env.example` con placeholders commitado
- Errores de BD devuelven mensaje generico (`'Database unavailable'`)
- Errores API devuelven mensajes genericos (no exponen detalles internos)
- Container Docker ejecuta como non-root (`appuser`, UID 1000)

---

## Pendiente para fases posteriores

- **Autenticacion JWT** (Phase 3)
- **Paginacion** en endpoints de listado
- **HSTS** via Nginx cuando se configure HTTPS en produccion
