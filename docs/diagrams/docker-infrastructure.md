# Docker Infrastructure - Diagramas

> Arquitectura de contenedores, red interna y flujo de peticiones HTTP de HablaIA

## Arquitectura de Contenedores (Desarrollo)

```mermaid
graph TB
    subgraph Internet["Navegador / Cliente"]
        Browser["Navegador"]
    end

    subgraph DockerNetwork["hablaia_network (bridge)"]
        subgraph BackendContainer["backend (hablaia_backend)"]
            Symfony["Symfony Dev Server<br/>:8000"]
        end

        subgraph FrontendContainer["frontend (hablaia_frontend)"]
            Vite["Vite Dev Server<br/>:3000"]
        end

        subgraph PostgresContainer["postgres (hablaia_postgres)"]
            PG["PostgreSQL 16<br/>:5432"]
            PGData[("postgres_data<br/>volume")]
        end

        subgraph SwaggerContainer["swagger (hablaia_swagger)"]
            SwaggerUI["Swagger UI<br/>:8080"]
        end
    end

    subgraph ExternalAPIs["APIs Externas"]
        Gemini["Gemini API<br/>generativelanguage.googleapis.com"]
        OpenAI["OpenAI API<br/>api.openai.com"]
        ARASAAC["ARASAAC API<br/>api.arasaac.org"]
    end

    Browser -->|":8080 → :8000"| Symfony
    Browser -->|":3000"| Vite
    Browser -->|":8081 → :8080<br/>(perfil dev)"| SwaggerUI

    Symfony -->|"DATABASE_URL"| PG
    PG --- PGData

    Symfony -->|"PHRASE_PROVIDER=gemini"| Gemini
    Symfony -->|"PHRASE_PROVIDER=openai"| OpenAI
    Symfony -->|"Busqueda pictogramas"| ARASAAC

    style Internet fill:#e3f2fd
    style DockerNetwork fill:#f3e5f5
    style ExternalAPIs fill:#fff3e0
```

## Arquitectura con Nginx Reverse Proxy (Produccion)

En produccion, Nginx actua como punto de entrada unico y distribuye las peticiones
entre backend y frontend. Esto es donde `trusted_proxies` cobra importancia.

```mermaid
graph TB
    subgraph Internet["Internet"]
        Client["Cliente<br/>IP: 88.12.34.56"]
    end

    subgraph DockerNetwork["hablaia_network (bridge)"]
        subgraph NginxContainer["nginx"]
            Nginx["Nginx<br/>:80"]
        end

        subgraph BackendContainer["backend"]
            Symfony["Symfony<br/>:8000"]
        end

        subgraph FrontendContainer["frontend"]
            Vite["Vue SPA<br/>:5173"]
        end

        subgraph PostgresContainer["postgres"]
            PG["PostgreSQL<br/>:5432"]
        end
    end

    Client -->|"Puerto 80"| Nginx

    Nginx -->|"/api/*<br/>X-Forwarded-For: 88.12.34.56<br/>X-Forwarded-Proto: https"| Symfony
    Nginx -->|"/*<br/>(todo lo demas)"| Vite

    Symfony -->|"SQL"| PG

    style Internet fill:#e3f2fd
    style DockerNetwork fill:#f3e5f5
    style NginxContainer fill:#fff9c4
```

## Flujo de IP: Por que se necesita `trusted_proxies`

Sin `trusted_proxies`, Symfony ve la IP del contenedor Nginx, no la del cliente real.
Esto afecta directamente al **rate limiter** del endpoint `/api/phrases/generate`.

### Sin trusted_proxies (problema)

```mermaid
sequenceDiagram
    participant C1 as Cliente A<br/>IP: 88.12.34.56
    participant C2 as Cliente B<br/>IP: 77.99.11.22
    participant Nginx as Nginx<br/>IP: 172.18.0.2
    participant Symfony as Symfony

    C1->>Nginx: POST /api/phrases/generate
    Note right of Nginx: Nginx anade:<br/>X-Forwarded-For: 88.12.34.56

    Nginx->>Symfony: POST /api/phrases/generate
    Note right of Symfony: $request->getClientIp()<br/>= 172.18.0.2 (IP de Nginx)

    Symfony->>Symfony: RateLimiter bucket: "172.18.0.2"

    C2->>Nginx: POST /api/phrases/generate
    Note right of Nginx: Nginx anade:<br/>X-Forwarded-For: 77.99.11.22

    Nginx->>Symfony: POST /api/phrases/generate
    Note right of Symfony: $request->getClientIp()<br/>= 172.18.0.2 (misma IP!)

    Symfony->>Symfony: RateLimiter bucket: "172.18.0.2"
    Note over Symfony: PROBLEMA: Ambos clientes<br/>comparten el mismo bucket.<br/>30 peticiones entre TODOS.
```

### Con trusted_proxies (solucion)

```mermaid
sequenceDiagram
    participant C1 as Cliente A<br/>IP: 88.12.34.56
    participant C2 as Cliente B<br/>IP: 77.99.11.22
    participant Nginx as Nginx<br/>IP: 172.18.0.2
    participant Symfony as Symfony

    C1->>Nginx: POST /api/phrases/generate
    Nginx->>Symfony: POST /api/phrases/generate<br/>X-Forwarded-For: 88.12.34.56

    Note right of Symfony: trusted_proxies = REMOTE_ADDR<br/>Nginx (172.18.0.2) es de confianza<br/>Lee X-Forwarded-For

    Symfony->>Symfony: $request->getClientIp() = 88.12.34.56
    Symfony->>Symfony: RateLimiter bucket: "88.12.34.56"

    C2->>Nginx: POST /api/phrases/generate
    Nginx->>Symfony: POST /api/phrases/generate<br/>X-Forwarded-For: 77.99.11.22

    Symfony->>Symfony: $request->getClientIp() = 77.99.11.22
    Symfony->>Symfony: RateLimiter bucket: "77.99.11.22"

    Note over Symfony: CORRECTO: Cada cliente tiene<br/>su propio bucket de 30 req/min.
```

## Configuracion Nginx (docker/nginx/default.conf)

```mermaid
flowchart LR
    subgraph Nginx["Nginx :80"]
        Router{Ruta?}
    end

    Client([Cliente]) --> Router

    Router -->|"/api/*"| Backend["backend:8000<br/>Symfony API"]
    Router -->|"/*"| Frontend["frontend:5173<br/>Vue SPA"]
    Router -->|"/health"| Health["200 OK<br/>(Nginx directo)"]

    style Nginx fill:#fff9c4
    style Backend fill:#e8f5e9
    style Frontend fill:#e3f2fd
    style Health fill:#f3e5f5
```

### Cabeceras que Nginx anade al Backend

| Cabecera | Valor | Uso en Symfony |
|----------|-------|----------------|
| `X-Real-IP` | IP real del cliente | Referencia |
| `X-Forwarded-For` | IP del cliente + cadena de proxies | `$request->getClientIp()` (con trusted_proxies) |
| `X-Forwarded-Proto` | `http` o `https` | `$request->isSecure()` |
| `Host` | Hostname original | Generacion de URLs |

## Puertos Expuestos (Desarrollo)

| Servicio | Puerto Host | Puerto Contenedor | URL |
|----------|-------------|-------------------|-----|
| **Backend** | 8080 | 8000 | http://localhost:8080/api |
| **Frontend** | 3000 | 3000 | http://localhost:3000 |
| **PostgreSQL** | 5432 | 5432 | postgresql://localhost:5432 |
| **Swagger** | 8081 | 8080 | http://localhost:8081 (perfil dev) |

## Variables de Entorno Clave

```mermaid
flowchart LR
    subgraph EnvFile[".env (raiz, gitignored)"]
        PG_PASS["POSTGRES_PASSWORD"]
        APP_SEC["APP_SECRET"]
        PROVIDER["PHRASE_PROVIDER"]
        GEMINI_KEY["GEMINI_API_KEY"]
    end

    subgraph DockerCompose["docker-compose.yml"]
        PG_PASS -->|"${POSTGRES_PASSWORD}"| BackendEnv["DATABASE_URL"]
        PG_PASS -->|"${POSTGRES_PASSWORD}"| PGEnv["POSTGRES_PASSWORD"]
        APP_SEC -->|"${APP_SECRET}"| AppEnv["APP_SECRET"]
        PROVIDER -->|"${PHRASE_PROVIDER:-fake}"| ProvEnv["PHRASE_PROVIDER"]
        GEMINI_KEY -->|"${GEMINI_API_KEY:-}"| GemEnv["GEMINI_API_KEY"]
    end

    subgraph Contenedores
        BackendEnv --> Backend["backend"]
        AppEnv --> Backend
        ProvEnv --> Backend
        GemEnv --> Backend
        PGEnv --> Postgres["postgres"]
    end

    style EnvFile fill:#ffcdd2
    style DockerCompose fill:#fff9c4
    style Contenedores fill:#c8e6c9
```

> **Seguridad:** El `.env` de la raiz esta en `.gitignore`. Solo `.env.example` con valores
> placeholder se commitea al repositorio. Las claves API reales solo existen localmente.
