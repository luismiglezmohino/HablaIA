# Security - HablaIA Backend

> Resumen de postura de seguridad. Informe detallado en [auditoría de seguridad — Fase 1](../audits/phase1-security-audit.md)

**Última revisión:** 13 de febrero de 2026
**Scope:** Backend Symfony 7.4 + Cycle ORM + PostgreSQL 16
**Fase:** Fase 1 MVP (sin autenticación)

---

## Resumen OWASP Top 10

| Categoría | Estado | Notas |
|---|---|---|
| A01 Broken Access Control | 🟡 Sin auth (por diseño en Fase 1) | Rate limiting en generación de frases y búsqueda. Auth planificada para Fase 3 |
| A02 Cryptographic Failures | 🟢 OK | `.env` no trackeado en git. Solo placeholders en `.env.example` |
| A03 Injection | 🟢 OK | Cycle ORM parametrizado. UUIDs validados. Query sanitizada. Prompt injection mitigada |
| A04 Insecure Design | 🟢 OK | Clean Architecture. Validación en 3 capas (Controller, Application, Domain) |
| A05 Security Misconfiguration | 🟢 OK | 6 cabeceras OWASP. CORS restringido. Container non-root. Trusted proxies configurados |
| A06 Vulnerable Components | 🟢 OK | `composer audit`: 0 vulnerabilidades |
| A07 Auth Failures | 🟡 N/A en Fase 1 | Se implementará en Fase 3 (perfiles de usuario) |
| A08 Software Integrity | 🟢 OK | `composer.lock` commitado. Validación MIME en descargas |
| A09 Logging | 🟢 OK | Monolog JSON en dev y prod. `fingers_crossed` en producción. `X-Correlation-Id` en CORS |
| A10 SSRF | 🟢 OK | Allowlist de dominios ARASAAC. Validación MIME y path traversal |

---

## Protecciones implementadas

### Cabeceras de seguridad (SecurityHeadersSubscriber)

Interceptor Symfony (`kernel.response`) que añade a todas las respuestas:

| Header | Valor |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `X-XSS-Protection` | `0` |
| `Content-Security-Policy` | `default-src 'none'; frame-ancestors 'none'` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |

### Validación de inputs

- **UUIDs:** Validados en Value Objects (`CategoryId`, `PictogramId`, `PhraseId`) + validación UUID v4 en controller
- **Search query:** Mínimo 2 chars, máximo 100 chars, sanitizada contra SQL injection
- **Pictogram sequence:** Entre 1 y 10 pictogramas por petición (validado en controller y dominio)
- **LLM labels:** Sanitizados contra prompt injection (solo letras, números, espacios, guiones) en todos los proveedores

### SSRF (HttpImageDownloader)

- Allowlist de dominios: solo `static.arasaac.org` y `api.arasaac.org`
- Validación de path traversal con normalización de ruta
- Validación de MIME type (solo `image/png`, `image/jpeg`, `image/gif`)
- Permisos de directorio `0755`

### CORS

- Origen restringido por regex (configurable vía `CORS_ALLOW_ORIGIN`)
- Métodos limitados a `GET` y `POST`

### Rate limiting

- `POST /api/phrases/generate`: doble protección por IP para evitar abuso de costes LLM (Groq/Gemini/OpenAI). Protege también peticiones cacheadas como efecto colateral
  - **Per-minute:** 30 req/60s (sliding window) vía `PHRASE_RATE_LIMIT` / `PHRASE_RATE_INTERVAL`
  - **Daily:** 500 req/día (fixed window) vía `PHRASE_DAILY_LIMIT`
- `GET /api/pictograms/search`: protección por IP para evitar abuso de peticiones a ARASAAC y escritura en disco
  - **Per-minute:** 30 req/60s (sliding window) vía `SEARCH_RATE_LIMIT` / `SEARCH_RATE_INTERVAL`

### Trusted Proxies (Docker/Nginx)

- `TRUSTED_PROXIES=REMOTE_ADDR` para que el rate limiter use la IP real del cliente detrás de Nginx
- Solo headers `x-forwarded-for` y `x-forwarded-proto` confiados
- Documentación visual en [`docs/diagrams/docker-infrastructure.md`](../diagrams/docker-infrastructure.md)

### Gestión de secretos

- Archivos `.env` en `.gitignore` (0 trackeados en git)
- Solo `.env.example` con placeholders commitado
- Errores de BD devuelven mensaje genérico (`'Database unavailable'`)
- Errores API devuelven mensajes genéricos (no exponen detalles internos)
- Container Docker ejecuta como non-root (`appuser`, UID 1000)

---

## Implementado post-auditoría

- **HSTS** vía Nginx (21 feb 2026)

## Pendiente para fases posteriores

- **Autenticación JWT** (Fase 3)
- **Paginación** en endpoints de listado
