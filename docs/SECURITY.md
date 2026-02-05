# Security Audit - HablaIA Backend

**Fecha:** 2026-02-05
**Scope:** Backend Symfony 7 + Cycle ORM + PostgreSQL
**Fase:** MVP (sin autenticación)

---

## Resumen OWASP Top 10

| Categoría | Estado | Notas |
|---|---|---|
| A01 Broken Access Control | 🟡 Sin auth (razonable en MVP) | Rate limiting en generación de frases |
| A02 Cryptographic Failures | 🟢 OK | `.env` usa placeholders. `imagePath` es ruta relativa web |
| A03 Injection | 🟢 OK | Cycle ORM parametrizado. UUIDs validados. Query sanitizada (2-100 chars) |
| A04 Insecure Design | 🟢 OK para MVP | Sin paginación en listados (~300 registros, aceptable) |
| A05 Security Misconfiguration | 🟢 OK | Cabeceras OWASP en todas las respuestas. CORS restringido a GET/POST |
| A06 Vulnerable Components | 🟢 OK | `composer audit` limpio |
| A07 Auth Failures | 🟡 Sin auth (razonable en MVP) | Se implementará JWT en fase posterior |
| A08 Software Integrity | 🟢 OK | `composer.lock` commitado |
| A09 Logging Failures | 🟡 Pendiente | Monolog JSON configurado pero sin logging a nivel de aplicación |
| A10 SSRF | 🟢 OK | Whitelist de dominios, validación MIME y path traversal |

---

## Protecciones implementadas

### Cabeceras de seguridad (SecurityHeadersSubscriber)
Interceptor Symfony (`kernel.response`) que añade a todas las respuestas:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-XSS-Protection: 0`

### Validación de inputs
- **UUIDs:** Validados en Value Objects (`CategoryId`, `PictogramId`, `PhraseId`)
- **Search query:** Mínimo 2 chars, máximo 100 chars, sanitizada contra SQL injection
- **Pictogram sequence:** Máximo 10 pictogramas por petición
- **OpenAI labels:** Sanitizados contra prompt injection (solo letras, números, espacios)

### SSRF (HttpImageDownloader)
- Whitelist de dominios: solo `static.arasaac.org` y `api.arasaac.org`
- Validación de path traversal con `realpath()`
- Validación de MIME type (solo `image/png`, `image/jpeg`, `image/gif`)
- Permisos de directorio `0755`

### CORS
- Origen restringido por regex (configurable via `CORS_ALLOW_ORIGIN`)
- Métodos limitados a `GET` y `POST`

### Rate limiting
- `POST /api/phrases/generate`: 30 peticiones / 60 segundos (sliding window, por IP)

---

## Pendiente para fases posteriores

- **Autenticación JWT**
- **Rate limiting en búsqueda** (`GET /api/pictograms/search`)
- **Paginación** en endpoints de listado
- **Logging de aplicación** con correlationId
- **HSTS** cuando se configure HTTPS
