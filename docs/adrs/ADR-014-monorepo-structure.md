# ADR-014: Estructura Monorepo con Stacks Independientes

**Estado:** Aceptado<br>
**Fecha:** 2026-02-13<br>
**Contexto:** HablaIA - Comunicador SAAC con IA<br>

## Contexto

HablaIA es un proyecto académico (TFM) desarrollado por un solo desarrollador con un plazo de MVP de 14 días. El sistema consta de dos stacks tecnológicos distintos (Symfony 7 backend, Vue.js 3 frontend) que comparten un contrato API (OpenAPI), documentación, configuración Docker y pipelines CI/CD. Se necesita decidir cómo organizar el código fuente: un único repositorio o repositorios separados.

Las restricciones principales son:

1. Un solo desarrollador: minimizar overhead de gestión de repositorios
2. MVP rápido: cambios frecuentes en API contract que afectan ambos stacks
3. Despliegue conjunto: Docker Compose orquesta backend + frontend + PostgreSQL en un mismo servidor
4. Documentación centralizada: ADRs, auditorías, diagramas y OpenAPI spec compartidos
5. CI/CD unificado: un único pipeline de CD despliega todo junto

## Decisión

Monorepo con stacks independientes y Docker Compose como punto de unión. La estructura es:

```
/ (root)
  package.json              # Orquestación: husky, commitlint, scripts delegados
  commitlint.config.js      # Conventional commits
  .husky/                   # Pre-commit hooks
  docker-compose.yml        # Unión de servicios: postgres + backend + frontend + swagger
  backend/                  # Symfony 7.4, composer.json independiente, PHP 8.4
  frontend/                 # Vue 3.5 + Vite, package.json independiente, Node 20
  docs/                     # Documentación compartida (ADRs, auditorías, OpenAPI)
  docker/                   # Configuración Docker compartida (nginx, postgres)
  .github/workflows/        # CI/CD compartido (4 workflows)
  agents/                   # Agentes IA (entregable TFM)
  skills/                   # Skills para agentes IA
```

### Principio: independencia real de cada stack

Cada stack tiene su propio gestor de paquetes, configuración de linting, tests y build. No existe dependencia directa en código entre frontend y backend; la comunicación es exclusivamente vía HTTP API.

- **Backend:** `composer.json` propio, PestPHP, PHPStan level 8, Symfony CLI
- **Frontend:** `package.json` propio, Vitest, Playwright, ESLint, vue-tsc

Las configuraciones de cada stack (`tsconfig.json`, `eslint.config.js`, `vite.config.ts`, `vitest.config.ts`, `tailwind.config.js`) residen dentro de su directorio (`frontend/`), no en la raíz del monorepo.

### Root package.json: solo orquestación

El `package.json` de la raíz no declara `workspaces`. Su única responsabilidad es orquestar herramientas transversales:

- **Husky:** Pre-commit hooks que ejecutan checks completos de frontend
- **Commitlint:** Validación de conventional commits en todo el repositorio
- **Scripts delegados:** `npm run test:frontend` delega a `cd frontend && npm run test`

Deliberadamente no se usa npm workspaces, Turborepo ni ninguna herramienta de monorepo. Cada stack se instala y ejecuta de forma independiente.

### Docker Compose como pegamento

Docker Compose define la topología de servicios sin crear acoplamiento en código:

| Servicio | Imagen | Puerto |
|----------|--------|--------|
| postgres | `postgres:16-alpine` | 5432 |
| backend | PHP 8.4 FPM (Symfony) | 8080:8000 |
| frontend | `node:20-alpine` (Vite) | 3000 |
| swagger | `swaggerapi/swagger-ui` (perfil dev) | 8081 |

Red compartida `hablaia_network`. Volumen persistente `postgres_data`.

### CI inteligente con path filters

Cuatro workflows de GitHub Actions, cada uno con scope definido:

| Workflow | Trigger | Scope |
|----------|---------|-------|
| `backend-ci.yml` | Cambios en `backend/**` | PHPStan + PestPHP |
| `frontend-ci.yml` | Cambios en `frontend/**` | ESLint + vue-tsc + Vitest + Lighthouse |
| `commitlint-ci.yml` | Todos los pushes | Conventional commits |
| `cd.yml` | Push a `main` | Deploy completo (SSH + Docker Compose) |

Cuando un PR solo modifica frontend, el pipeline de backend no se ejecuta y viceversa. Esto reduce tiempos de CI y consumo de minutos de GitHub Actions.

### Documentación centralizada

El directorio `docs/` es el punto único de verdad para toda la documentación del proyecto: ADRs, auditorías de seguridad/performance/accesibilidad/QA, diagramas y la especificación OpenAPI. Al residir en el mismo repositorio, la documentación evoluciona con el código y se revisa en los mismos PRs.

## Consecuencias

### Positivas

- Cambios atómicos: un PR puede modificar backend, frontend, docs y CI/CD simultáneamente
- API contract (OpenAPI) versionado junto al código que lo implementa y consume
- Un solo `git clone` para tener el proyecto completo listo para desarrollo
- CI path filters evitan ejecuciones innecesarias sin configuración adicional
- Documentación siempre sincronizada con el código fuente
- Simplifica el CD: un único pipeline despliega todo el sistema

### Negativas

- Historial de git mezclado entre frontend y backend
- Clonado inicial incluye ambos stacks aunque solo se trabaje en uno
- Sin separación de permisos por directorio (irrelevante con un solo desarrollador)

### Mitigaciones

- Path filters en CI eliminan el problema de builds innecesarios
- `git log -- frontend/` o `git log -- backend/` permiten ver historial por stack
- El repositorio es suficientemente pequeño para que el clonado no sea un problema

## Alternativas Consideradas

### 1. Polyrepo (repositorios separados para frontend y backend)

**Pros:** Historial limpio por stack, CI/CD completamente independiente, permisos granulares
**Contras:** Cambios que afectan ambos stacks requieren coordinar dos PRs, versionado del API contract duplicado, Docker Compose debe referenciar repos externos, documentación dispersa
**Rechazo:** El overhead de coordinación entre repos es injustificable para un solo desarrollador con un MVP de 14 días. Los cambios atómicos cross-stack son frecuentes en fase de desarrollo activo.

### 2. Npm Workspaces / Turborepo

**Pros:** Gestores de dependencias optimizados, caching de builds, ejecución paralela de tasks
**Contras:** Solo beneficia si hay paquetes compartidos entre frontend y backend (no es el caso: PHP + TypeScript). Turborepo añade complejidad de configuración sin beneficio real cuando los stacks usan lenguajes diferentes. El root `package.json` ya resuelve la orquestación necesaria con scripts simples.
**Rechazo:** No hay código compartido entre stacks (lenguajes diferentes). La complejidad adicional no aporta valor.

### 3. Monorepo con herramienta dedicada (Nx, Lerna)

**Pros:** Grafos de dependencias, caching distribuido, ejecución incremental, generadores de código
**Contras:** Curva de aprendizaje significativa, configuración inicial pesada, overkill para dos stacks sin dependencias compartidas. Nx y Lerna están diseñados para ecosistemas JavaScript/TypeScript con decenas de paquetes interrelacionados.
**Rechazo:** El proyecto tiene exactamente dos stacks sin código compartido. Los path filters de GitHub Actions ya proporcionan ejecución incremental. La inversión en configurar Nx no se amortiza.

## Referencias

- ADR-010: Inline Feedback over Toasts
- ADR-011: Sistema de Diseño Visual
- [GitHub Actions: path filters](https://docs.github.com/en/actions/writing-workflows/workflow-syntax-for-github-actions#onpushpull_requestpull_request_targetpathspaths-ignore)
- [Monorepo vs Polyrepo (GitHub Blog)](https://github.blog/engineering/engineering-principles/the-monorepo-vs-polyrepo-problem/)
