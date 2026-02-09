# ADR-012: Pipeline de Despliegue Continuo con SSH y Docker Compose

**Estado:** Aceptado
**Fecha:** 2026-02-09
**Contexto:** HablaIA - Comunicador SAAC con IA

## Contexto

HablaIA necesita un pipeline de despliegue continuo (CD) que lleve el codigo de `main` a produccion de forma automatica. El servidor es un Hetzner CX33 con Docker instalado. El proyecto es academico y no requiere orquestadores como Kubernetes.

## Decision

Pipeline CD nativo con GitHub Actions + SSH + Docker Compose, sin third-party actions.

### Arquitectura de produccion

Tres servicios Docker Compose:

| Servicio | Imagen | Funcion |
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

### Decisiones tecnicas clave

1. **SSH nativo** en vez de third-party actions para eliminar dependencias de supply chain y tener control total del script.
2. **Heredoc SSH** (`bash -s << 'SCRIPT'`) para enviar el script completo al servidor en una sola conexion.
3. **Volumenes nombrados** (`postgres_data`, `pictograms_data`) para persistencia entre deploys.
4. **Migraciones idempotentes** con tabla `schema_migrations` para tracking de versiones aplicadas.
5. **Health check con rollback automatico**: 6 intentos, rollback a commit anterior si falla.

## Retos encontrados y soluciones (PRs #42-#52)

El pipeline inicial (PR #42) funciono en la primera ejecucion pero revelo una cadena de problemas que solo se manifiestan en produccion real. Se documentan aqui como referencia:

### 1. Volumenes de pictogramas (PR #43)
- **Problema:** El Dockerfile copiaba pictogramas con `COPY` al build, pero se perdian al recrear el contenedor.
- **Solucion:** Volumen compartido `pictograms_data` montado en backend (RW) y nginx (RO).

### 2. Directorios del Dockerfile (PR #44)
- **Problema:** `chown` fallaba porque `var/` y `public/` no existian en la imagen.
- **Solucion:** `mkdir -p` antes del `chown` en el Dockerfile.

### 3. Symfony .env en produccion (PR #45)
- **Problema:** Symfony requiere un `.env` aunque las variables se inyecten por entorno.
- **Solucion:** Archivo `.env` stub con `APP_ENV=prod` incluido en el build.

### 4. Migraciones automaticas (PR #46)
- **Problema:** Las migraciones se ejecutaban manualmente via SSH post-deploy.
- **Solucion:** Comando `app:migrations:run` integrado en el script de deploy.

### 5. SQL multi-statement y PHPStan (PR #47)
- **Problema:** Las migraciones con multiples sentencias SQL fallaban al ejecutarse como un solo string. PHPStan reportaba tipos incorrectos.
- **Solucion:** Separar sentencias por `;` en el comando de migraciones. Annotations `@var` para PHPStan.

### 6. Seed check incorrecto (PR #48)
- **Problema:** El check de seed verificaba count de categorias, pero las categorias se creaban por migracion y el seed eran los pictogramas.
- **Solucion:** Verificar count de pictogramas en vez de categorias.

### 7. Permisos de volumen y categorias (PRs #49-#51)
- **Problema:** Los volumenes Docker se crean como root. `docker compose exec` no permite `-u root`.
- **Solucion:** `docker exec -u root $CONTAINER chown ...` en vez de `docker compose exec`.

### 8. Heredoc stdin consumido por docker compose exec (PR #52)
- **Problema:** `docker compose exec -T` hereda stdin del shell padre. Dentro de un heredoc SSH, el primer `exec` consumia el resto del script. Las fixtures, sync y health check **nunca se ejecutaban**, pero GitHub reportaba "success" porque el ultimo comando ejecutado (migraciones) retornaba exit code 0.
- **Solucion:** Redirigir stdin con `</dev/null` en cada llamada a `docker compose exec`.

## Consecuencias

### Positivas
- Deploy automatico en cada push a main (~1 min)
- Zero-downtime para la base de datos (volumen persistente)
- Rollback automatico si el health check falla
- Sin dependencias de terceros en el pipeline

### Negativas
- Downtime breve durante `docker compose down/up` (~10s)
- Sin blue-green deployment ni canary releases
- Debugging requiere acceso SSH al servidor

### Mitigaciones
- El downtime es aceptable para un proyecto academico con usuarios limitados
- Los logs de GitHub Actions proporcionan trazabilidad completa del deploy
- Health check + rollback automatico minimizan el impacto de deploys fallidos

## Referencias

- GitHub Actions: deploy nativo con SSH (sin third-party actions)
- Docker Compose V2: volumenes nombrados y health checks
- Gotcha documentado: `docker compose exec -T` + heredoc stdin
