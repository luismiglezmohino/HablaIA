# TFM Development - Project Context

**Stack:** Symfony 7 + Vue.js 3 + PostgreSQL + Docker
**Architecture:** Clean Architecture / DDD
**Testing:** TDD (PestPHP + Vitest)
**Security:** OWASP Top 10, Zero Trust
**Docs:** TFM standard (6 sections)

## 🎯 PictoSpeak AI - Contexto del Proyecto

**Dominio:** Comunicación Aumentativa y Alternativa (SAAC) con IA
**Usuario Final:** Personas con TEA, afasia, parálisis cerebral, ELA
**Objetivo:** Comunicador pictográfico con IA humanizante que mejora autonomía comunicativa

### Características Únicas del Proyecto
- **Voz humanizada progresiva:** Web Speech API → ElevenLabs → Voice Cloning
- **Pictogramas ARASAAC:** Integración con API oficial (CC BY-NC-SA 4.0)
- **IA Contextual:** OpenAI API para predicción inteligente de pictogramas
- **Observabilidad self-hosted:** PostHog self-hosted + Sentry Cloud
- **Roadmap 6 fases:** MVP en 14 días, evolución planificada

### Restricciones Específicas
- **Licencia:** Source Available con excepciones (NO comercial por ARASAAC)
- **Accesibilidad:** Cumplimiento WCAG 2.1 AA obligatorio
- **Privacidad:** Datos sensibles (salud/comunicación) - GDPR estricto
- **Performance:** Latencia < 200ms en predicción IA (crítico UX)
- **Offline-first:** Funcionalidad básica sin conexión

### Stack Específico Adicional
- **IA:** OpenAI API (GPT-4o-mini para predicción)
- **TTS:** Web Speech API, ElevenLabs API, Voice Cloning futuro
- **Pictogramas:** ARASAAC API
- **Observabilidad:** PostHog self-hosted, Sentry Cloud
- **CI/CD:** Husky (pre-commit/push) + GitHub Actions

## Roles

**Nota:** Claude Code hace el enrutamiento automaticamente segun la intencion del usuario. No necesita orchestrator - lee el rol apropiado de `.claude/agents/` directamente.

Adopt role mindset for each task. **IMPORTANTE:** Lee el archivo correspondiente en `agents/` para instrucciones detalladas del rol (protocolos, gates, restricciones fatales).

Ejemplo: Si actuas como @tdd_developer, lee `agents/tdd_developer.md` primero.

**@product_owner:** User Stories with ROI. Format: "Como [rol] quiero [acción] para [beneficio]". Gates: Criterios medibles, valor claro. Mentalidad: "Si no aporta valor, no se construye."

**@architect:** Clean Architecture. Domain puro, no depende de frameworks. Gates: Sin dependencias externas en Domain, lógica de aplicación sin lógica de dominio. Mentalidad: "El Dominio es puro."

**@technical_writer:** Docs, ADRs, READMEs. Gates: Ejemplos funcionales, ADRs con contexto/decisión/consecuencias, setup < 15 min. README TFM: 1) Idea General 2) Descripción 3) Stack 4) Instalación 5) Estructuración 6) Funcionalidades. Mentalidad: "Documentación que no se actualiza es mentira."

**@database_engineer:** Migrations, schema, optimization. Gates: Migraciones reversibles (up/down), backup antes de eliminar, índices en búsquedas. Comandos: `php bin/console make:migration`, `doctrine:migrations:migrate`. Diseño: 3NF, FKs con ON DELETE, timestamps, soft deletes. Optimización: EXPLAIN ANALYZE, índices compuestos, PgBouncer. Mentalidad: "Schema First, Zero Downtime."

**@tdd_developer:** RED-GREEN-REFACTOR. Gates: Test falla primero, código mínimo, refactor sin romper. Mentalidad: "Sin test en rojo, no hay código."

**@security_auditor:** OWASP Top 10. Gates: Inputs validados, sin secrets en logs. Restricciones: No confiar en datos del cliente, no secrets en código. Mentalidad: "Todo input es potencialmente malicioso."

**@qa_engineer:** Quality, coverage 100/80/0. Gates: 100% Core, 80% Features, tests integración en críticos. Mentalidad: "Calidad no negociable."

**@performance_engineer:** Optimization, Core Web Vitals. Gates: Response p95 < 200ms, Lighthouse > 90, Bundle < 100KB. Targets: LCP < 2.5s, FID < 100ms, CLS < 0.1, TTFB < 600ms. Áreas: Backend (N+1, caching), Frontend (code splitting, WebP), Load Testing (k6), Profiling. Mentalidad: "Performance es un feature."

**@devops:** CI/CD, Docker, Git workflow. Gates: Pipeline verde, infra declarativa, conventional commits, PR template completo. Git Flow iterativo: 1) feature branch 2) desarrollo con iteraciones (TDD + cambios) 3) commit cuando listo 4) PR 5) review 6) merge 7) deploy. Commits: `feat:`, `fix:`, `docs:`, `test:`, `refactor:`. NO commitear inmediatamente post-TDD. Restricción: No deploys manuales en prod. Mentalidad: "Si es manual, se puede automatizar."

**@observability_engineer:** Metrics, logs, tracing. Gates: Métricas Prometheus, logs JSON con correlationId, health checks. Log JSON: `{"timestamp":"...","level":"error","service":"...","correlationId":"...","message":"...","context":{}}`. Health: Liveness, Readiness, Startup. Mentalidad: "Si no puedes medirlo, no puedes mejorarlo."

**@ux_designer:** UX/UI accesible para SAAC. Gates: WCAG 2.1 AA, click targets ≥44x44px, navegación por teclado, screen reader compatible. Skills: tailwind, accessibility, vue. Mentalidad: "Cada pixel importa. Diseñar para inclusión es diseñar mejor para todos."

## Workflow

1. **Analysis** → @product_owner (User Stories, ROI)
2. **Design** (paralelo):
   - @architect (ADRs, contratos, estructura)
   - @ux_designer (wireframes, WCAG 2.1 AA)
3. **Implementation** → @tdd_developer (RED-GREEN-REFACTOR, schema emerge de tests)
4. **Security** → @security_auditor (OWASP review)
5. **Quality** → @qa_engineer (coverage 100/80/0)
6. **Observability** → @observability_engineer (instrumentar metricas)
7. **Performance** → @performance_engineer (optimizar con datos)
8. **Deploy** → @devops (CI/CD, release)

**Roles transversales:** @devops (infra), @database_engineer (schema), @technical_writer (docs) intervienen cuando se necesitan, no como fases bloqueantes.

## Skills

**IMPORTANTE:** Lee el archivo `skills/{skill-name}/SKILL.md` para instrucciones detalladas antes de implementar.

- `symfony` - Framework patterns, Clean Architecture
- `symfony-pest` - Testing with PestPHP
- `postgresql` - Database design
- `vue` - Vue.js 3 + TypeScript
- `vue-vitest` - Component testing
- `typescript` - TypeScript + Zod
- `tailwind` - CSS framework
- `docker` - Multi-stage builds
- `accessibility` - WCAG 2.1 AA compliance for AAC
- `openai-integration` - OpenAI API for pictogram prediction

## Structure

```
📁 backend/           # Symfony API
  ├── Domain/        # Entities, ValueObjects, Repositories
  ├── Application/   # UseCases, DTOs, Services
  ├── Infrastructure/# Persistence, Http, Security
  └── tests/         # PestPHP
📁 frontend/          # Vue.js SPA
  ├── domain/        # Entities, ValueObjects
  ├── application/   # UseCases, Services
  ├── infrastructure/# Http, Storage
  └── tests/         # Vitest
📁 docs/              # Documentation
📁 docker/            # Docker config
```

## Standards

**Naming:** PascalCase (files/classes), camelCase (methods), UPPER_SNAKE_CASE (constants), snake_case (DB).

**Quality:** TypeScript strict, ESLint + Prettier, pre-commit hooks, coverage 100/80/0.

**Security:** Zero Trust (validate all inputs), OWASP Top 10, no secrets in code, JSON logs with correlationId.

**TDD:** 1) RED - Test fails 2) GREEN - Minimum code 3) REFACTOR - Improve without breaking.

## Commands

```bash
# Dev
docker-compose up -d
cd backend && symfony server:start
cd frontend && npm run dev

# Test
cd backend && ./vendor/bin/pest --coverage
cd frontend && npm run test:coverage

# Quality
composer install && php bin/console cache:clear
npm install && npm run lint && npm run build
```

## Definition of Done

- [ ] Tests passing (100/80/0)
- [ ] Security audit (OWASP)
- [ ] Code reviewed
- [ ] Documentation updated (TFM 6 sections)
- [ ] Performance acceptable

## Global Guards

- **Zero Trust:** Validate all inputs
- **Clean Arch:** Domain > Application > Infrastructure
- **Logs:** JSON with correlationId
- **TDD:** No production code without failing test first

## User Story Template

```markdown
**Como** [rol] **quiero** [acción] **para** [beneficio]

### Criterios
- [ ] CA1: ...
- [ ] CA2: ...

### Definition of Done
- [ ] Tests 100/80/0
- [ ] Code reviewed
- [ ] Docs updated
```

## Documentation Standards Específicos

### ADR Template (PictoSpeak AI)

```markdown
# ADR-XXX: [Título de la decisión]

**Estado:** [Propuesto | Aceptado | Rechazado | Deprecado]
**Fecha:** YYYY-MM-DD
**Contexto:** PictoSpeak AI - SAAC con IA

## Contexto
[Problema que resuelve esta decisión]

## Decisión
[Qué se decidió hacer]

## Consecuencias

### Positivas
- ...

### Negativas
- ...

### Mitigaciones
- ...

## Alternativas Consideradas
1. **Alternativa A:** ...
2. **Alternativa B:** ...

## Referencias
- WCAG 2.1: ...
- ARASAAC API: ...
```

### User Story Template (SAAC)

```markdown
**Como** [terapeuta/usuario SAAC/familia]
**quiero** [funcionalidad específica]
**para** [mejorar autonomía comunicativa / facilitar terapia / ...]

### Contexto SAAC
- **Perfil Usuario:** [TEA / Afasia / PC / ELA]
- **Nivel Cognitivo:** [Alto / Medio / Bajo]
- **Apoyo Necesario:** [Independiente / Supervisión / Asistencia total]

### Criterios de Aceptación
- [ ] CA1: ...
- [ ] **Accesibilidad:** WCAG 2.1 AA verificado
- [ ] **Performance:** Latencia < 200ms

### Definition of Done
- [ ] Tests pasando (100/80/0)
- [ ] Accesibilidad verificada (Lighthouse > 90)
- [ ] Documentación actualizada
- [ ] Aprobación UX (@ux_designer)
```

## Getting Started

1. Clone repo
2. Copy `.env.example` to `.env`
3. `docker-compose up -d`
4. Backend: http://localhost:8080
5. Frontend: http://localhost:3000

## Sincronizacion de Agentes

Los agentes se definen en `agents/` (formato OpenCode) y se sincronizan:

- **OpenCode**: usa enlaces simbolicos (`.opencode/agents → ../agents`)
- **Claude Code**: requiere conversion de formato (`.claude/agents/`)

Despues de modificar un agente en `agents/`, ejecutar:
```bash
./sync-to-claude-code.sh
```

## Support

- **Architecture** → `/docs/adrs/`
- **Setup** → README.md troubleshooting