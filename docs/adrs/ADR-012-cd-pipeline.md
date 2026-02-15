# ADR-012: Pipeline de Despliegue Continuo con SSH y Docker Compose

**Estado:** Aceptado<br>
**Fecha:** 2026-02-09<br>
**Contexto:** HablaIA - Comunicador SAAC con IA<br>

## Contexto

HablaIA necesita un pipeline de despliegue continuo (CD) que lleve el código de `main` a producción de forma automática. El servidor es un Hetzner CX33 con Docker instalado. El proyecto es académico y no requiere orquestadores como Kubernetes.

## Decisión

Pipeline CD nativo con GitHub Actions + SSH + Docker Compose, sin third-party actions.

### Arquitectura de producción

Tres servicios Docker Compose:

| Servicio | Imagen | Función |
|----------|--------|---------|
| **postgres** | `postgres:16-alpine` | Base de datos (volumen persistente) |
| **backend** | PHP 8.4 FPM (Dockerfile multi-stage) | API Symfony con opcache |
| **nginx** | Nginx 1.27 (Dockerfile multi-stage con build Vue.js) | SPA + reverse proxy + static files |

### Flujo de despliegue

```
Push a main → GitHub Actions → SSH a Hetzner → git pull → docker compose build →
docker compose down/up → fix permisos → migraciones → fixtures → arasaac sync →
health check → cleanup
```

### Decisiones técnicas clave

1. **SSH nativo** en vez de third-party actions para eliminar dependencias de supply chain y tener control total del script.
2. **Heredoc SSH** (`bash -s << 'SCRIPT'`) para enviar el script completo al servidor en una sola conexión.
3. **Volúmenes nombrados** (`postgres_data`, `pictograms_data`) para persistencia entre deploys.
4. **Migraciones idempotentes** con tabla `schema_migrations` para tracking de versiones aplicadas.
5. **Health check con rollback automático**: 6 intentos, rollback a commit anterior si falla.

## Retos encontrados y soluciones (PRs #42-#52)

El pipeline inicial (PR #42) funcionó en la primera ejecución pero reveló una cadena de problemas que solo se manifiestan en producción real. Se documentan aquí como referencia:

### 1. Volúmenes de pictogramas (PR #43)
- **Problema:** El Dockerfile copiaba pictogramas con `COPY` al build, pero se perdían al recrear el contenedor.
- **Solución:** Volumen compartido `pictograms_data` montado en backend (RW) y nginx (RO).

### 2. Directorios del Dockerfile (PR #44)
- **Problema:** `chown` fallaba porque `var/` y `public/` no existían en la imagen.
- **Solución:** `mkdir -p` antes del `chown` en el Dockerfile.

### 3. Symfony .env en producción (PR #45)
- **Problema:** Symfony requiere un `.env` aunque las variables se inyecten por entorno.
- **Solución:** Archivo `.env` stub con `APP_ENV=prod` incluido en el build.

### 4. Migraciones automáticas (PR #46)
- **Problema:** Las migraciones se ejecutaban manualmente vía SSH post-deploy.
- **Solución:** Comando `app:migrations:run` integrado en el script de deploy.

### 5. SQL multi-statement y PHPStan (PR #47)
- **Problema:** Las migraciones con múltiples sentencias SQL fallaban al ejecutarse como un solo string. PHPStan reportaba tipos incorrectos.
- **Solución:** Separar sentencias por `;` en el comando de migraciones. Annotations `@var` para PHPStan.

### 6. Seed check incorrecto (PR #48)
- **Problema:** El check de seed verificaba count de categorías, pero las categorías se creaban por migración y el seed eran los pictogramas.
- **Solución:** Verificar count de pictogramas en vez de categorías.

### 7. Permisos de volumen y categorías (PRs #49-#51)
- **Problema:** Los volúmenes Docker se crean como root. `docker compose exec` no permite `-u root`.
- **Solución:** `docker exec -u root $CONTAINER chown ...` en vez de `docker compose exec`.

### 8. Heredoc stdin consumido por docker compose exec (PR #52)
- **Problema:** `docker compose exec -T` hereda stdin del shell padre. Dentro de un heredoc SSH, el primer `exec` consumía el resto del script. Las fixtures, sync y health check **nunca se ejecutaban**, pero GitHub reportaba "success" porque el último comando ejecutado (migraciones) retornaba exit code 0.
- **Solución:** Redirigir stdin con `</dev/null` en cada llamada a `docker compose exec`.

## Consecuencias

### Positivas
- Deploy automático en cada push a main (~1 min)
- Zero-downtime para la base de datos (volumen persistente)
- Rollback automático si el health check falla
- Sin dependencias de terceros en el pipeline

### Negativas
- Downtime breve durante `docker compose down/up` (~10s)
- Sin blue-green deployment ni canary releases
- Debugging requiere acceso SSH al servidor

### Mitigaciones
- El downtime es aceptable para un proyecto académico con usuarios limitados
- Los logs de GitHub Actions proporcionan trazabilidad completa del deploy
- Health check + rollback automático minimizan el impacto de deploys fallidos

## Referencias

- GitHub Actions: deploy nativo con SSH (sin third-party actions)
- Docker Compose V2: volúmenes nombrados y health checks
- Gotcha documentado: `docker compose exec -T` + heredoc stdin
