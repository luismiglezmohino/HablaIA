# HablaIA - Project Context

**Stack:** Symfony 7 + Vue.js 3 + PostgreSQL + Docker
**Architecture:** Clean Architecture / DDD
**Testing:** TDD (PestPHP + Vitest)
**Security:** OWASP Top 10, Zero Trust

## Contexto del Proyecto

**Dominio:** Comunicacion Aumentativa y Alternativa (SAAC) con IA
**Usuario Final:** Personas con TEA, afasia, paralisis cerebral, ELA
**Objetivo:** Comunicador pictografico con IA humanizante que mejora autonomia comunicativa

### Restricciones
- **Licencia:** Source Available (NO comercial por ARASAAC CC BY-NC-SA 4.0)
- **Accesibilidad:** WCAG 2.1 AA obligatorio
- **Privacidad:** Datos sensibles (salud/comunicacion) - GDPR estricto
- **Performance:** Latencia < 200ms en prediccion IA
- **Offline-first:** Funcionalidad basica sin conexion

## Roles

Adopt role mindset for each task. Lee `agents/{rol}.md` para instrucciones detalladas (protocolos, gates, restricciones fatales).

- **@product_owner** - User Stories con ROI
- **@architect** - Clean Architecture, Domain puro
- **@technical_writer** - Docs, ADRs, READMEs
- **@database_engineer** - Migrations, schema, optimization
- **@tdd_developer** - RED-GREEN-REFACTOR
- **@security_auditor** - OWASP Top 10
- **@qa_engineer** - Coverage 100/80/0
- **@performance_engineer** - Core Web Vitals, p95 < 200ms
- **@devops** - CI/CD, Docker, conventional commits
- **@observability_engineer** - Metrics, logs, tracing
- **@ux_designer** - WCAG 2.1 AA, SAAC interfaces

## Workflow

1. **Analysis** → @product_owner (User Stories, ROI)
2. **Design** (paralelo): @architect (ADRs) + @ux_designer (WCAG 2.1 AA)
3. **Implementation** → @tdd_developer (RED-GREEN-REFACTOR)
4. **Security** → @security_auditor (OWASP review)
5. **Quality** → @qa_engineer (coverage 100/80/0)
6. **Observability** → @observability_engineer (instrumentar metricas)
7. **Performance** → @performance_engineer (optimizar con datos)
8. **Deploy** → @devops (CI/CD, release)

**Roles transversales:** @devops, @database_engineer, @technical_writer intervienen cuando se necesitan.

## Skills

Lee `skills/{skill-name}/SKILL.md` antes de implementar.

**Backend:** `symfony`, `symfony-pest`, `cycle-orm`, `postgresql`, `llm-integration`
**Frontend:** `vue`, `vue-vitest`, `typescript`, `tailwind`, `accessibility`
**Infra:** `docker`

## Structure

```
backend/            # Symfony API (ver backend/CLAUDE.md)
  Domain/           # Entities, ValueObjects, Repository interfaces
  Application/      # UseCases, DTOs, Services
  Infrastructure/   # Persistence, Http, Security
  tests/            # PestPHP (Unit + Functional)
frontend/           # Vue.js SPA (ver frontend/CLAUDE.md)
  domain/           # Entities, ValueObjects
  application/      # UseCases, Services
  infrastructure/   # Http, Storage
  tests/            # Vitest
docs/               # Documentation, ADRs
docker/             # Docker config
```

## Standards

**Naming:** PascalCase (files/classes), camelCase (methods), UPPER_SNAKE_CASE (constants), snake_case (DB).
**Quality:** TypeScript strict, ESLint + Prettier, pre-commit hooks, coverage 100/80/0.
**Security:** Zero Trust (validate all inputs), OWASP Top 10, no secrets in code, JSON logs with correlationId.
**TDD:** 1) RED - Test fails 2) GREEN - Minimum code 3) REFACTOR - Improve without breaking.

## Definition of Done

- [ ] Tests passing (100/80/0)
- [ ] Security audit (OWASP)
- [ ] Code reviewed
- [ ] Documentation updated
- [ ] Performance acceptable

## Global Guards

- **Zero Trust:** Validate all inputs
- **Clean Arch:** Domain > Application > Infrastructure
- **Logs:** JSON with correlationId
- **TDD:** No production code without failing test first

## Commands

```bash
# Dev
docker-compose up -d
cd backend && symfony server:start
cd frontend && npm run dev

# Test
cd backend && ./vendor/bin/pest --coverage
cd frontend && npm run test:coverage
```

## Agent Sync

Los agentes se definen en `agents/` y se sincronizan:
- **OpenCode**: enlaces simbolicos (`.opencode/agents`)
- **Claude Code**: conversion de formato (`.claude/agents/`)

Despues de modificar un agente: `./sync-to-claude-code.sh`
