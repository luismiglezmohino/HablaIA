# Project Configuration - PictoSpeak AI

This project uses a custom agent orchestration system for PictoSpeak AI - a SAAC (Comunicación Aumentativa y Alternativa) application with AI.

**Domain:** Comunicación Aumentativa y Alternativa (SAAC) con IA  
**Users:** Personas con TEA, afasia, parálisis cerebral, ELA  
**Goal:** Comunicador pictográfico con IA humanizante que mejora autonomía comunicativa  

**Stack:** Symfony 7 + Vue.js 3 + PostgreSQL + Docker + OpenAI API  
**Architecture:** Clean Architecture / DDD  
**Testing:** TDD (PestPHP + Vitest)  
**Security:** OWASP Top 10, Zero Trust, GDPR estricto  
**Accessibility:** WCAG 2.1 AA mandatory  
**Documentation:** TFM standard (6 sections)

## Agent Structure

The project uses specialized agents defined in `agents/` (shared across tools via symlinks):

### Primary Agent
- **orchestrator** - Primary agent that routes requests to specialized agents based on intent

### Specialized Subagents

#### Analysis & Design
- **product_owner** - Business requirements, user stories and acceptance criteria
- **architect** - Clean Architecture design, ADRs and system validation
- **technical_writer** - Documentation, READMEs, guides and API docs (TFM standard)
- **ux_designer** - UX/UI design for accessibility and AAC interfaces (WCAG 2.1 AA)

#### Data & Persistence
- **database_engineer** - Database migrations, schema design, query optimization and PostgreSQL best practices

#### Implementation & Quality
- **tdd_developer** - Test-driven development implementation (RED-GREEN-REFACTOR)
- **security_auditor** - Security audits, OWASP compliance and vulnerability scanning
- **qa_engineer** - Quality assurance, test coverage (100/80/0) and testing pyramid
- **performance_engineer** - Performance optimization, profiling, Core Web Vitals and load testing

#### Operations & Observability
- **devops** - CI/CD, Docker, Git workflow (GitHub Flow), Kubernetes and infrastructure automation
- **observability_engineer** - Metrics, structured logging, distributed tracing and monitoring

### Agent Workflow

1. **Analysis** → `@product_owner` creates user stories (SAAC context, ROI)
2. **Design** (paralelo):
   - `@architect` validates Clean Architecture + `@technical_writer` documents
   - `@ux_designer` designs accessible interfaces (WCAG 2.1 AA)
3. **Implementation** → `@tdd_developer` (test first, schema emerges from tests)
4. **Security Review** → `@security_auditor` checks OWASP compliance + GDPR
5. **Quality Check** → `@qa_engineer` validates coverage (100/80/0)
6. **Observability** → `@observability_engineer` adds monitoring (PostHog + Sentry)
7. **Performance** → `@performance_engineer` optimizes with data (latency < 200ms)
8. **Deploy** → `@devops` handles CI/CD and release

**Roles transversales:** `@devops` (infra), `@database_engineer` (schema), `@technical_writer` (docs) intervienen cuando se necesitan.

## Skills Available

Technical skills are defined in `skills/` (shared across tools via symlinks):

### Backend
- **symfony** - Symfony framework with PestPHP and Clean Architecture patterns
- **symfony-pest** - Testing patterns for Symfony with Pest PHP
- **postgresql** - PostgreSQL database configuration and best practices

### Frontend
- **vue** - Vue.js 3 with TypeScript, Vitest testing and Clean Architecture
- **vue-vitest** - Vue.js 3 testing with Vitest including component and composable tests
- **typescript** - TypeScript with Zod validation, ESLint and Prettier
- **tailwind** - Tailwind CSS with PostCSS, security patterns and visual testing
- **accessibility** - WCAG 2.1 AA compliance for AAC applications

### AI & Integration
- **openai-integration** - OpenAI API integration for pictogram prediction

### Infrastructure
- **docker** - Docker with multi-stage builds, security best practices and Docker Compose

## Project Structure

### Backend (Symfony)
```
src/
├── Domain/
│   ├── Entity/
│   ├── ValueObject/
│   ├── Repository/
│   └── Service/
├── Application/
│   ├── UseCase/
│   ├── DTO/
│   └── Service/
├── Infrastructure/
│   ├── Persistence/
│   ├── Http/
│   └── Security/
└── Shared/
    ├── Exception/
    └── Validator/
```

### Frontend (Vue)
```
src/
├── domain/
│   ├── entity/
│   ├── valueobject/
│   └── repository/
├── application/
│   ├── usecase/
│   └── service/
├── infrastructure/
│   ├── http/
│   └── storage/
└── shared/
    ├── validation/
    └── types/
```

## Naming Conventions

- **Files:** PascalCase (UserService.php)
- **Classes:** PascalCase (UserService)
- **Methods:** camelCase (getUserById)
- **Constants:** UPPER_SNAKE_CASE (MAX_ATTEMPTS)
- **DB:** snake_case (user_profiles)

## Common Commands

```bash
# Development
docker-compose up -d                    # Start all services
cd backend && symfony server:start      # Start Symfony dev server
cd frontend && npm run dev              # Start Vue dev server

# Testing
# Backend (PestPHP)
cd backend && ./vendor/bin/pest
cd backend && ./vendor/bin/pest --coverage

# Frontend (Vitest)
cd frontend && npm run test
cd frontend && npm run test:coverage

# Code Quality
# Backend
composer install
php bin/console cache:clear
php bin/console doctrine:migrations:migrate

# Frontend
npm install
npm run lint
npm run lint:fix
npm run build
```

## Workflow Standards

### TDD Cycle (Mandatory)
1. **RED** - Write test that fails
2. **GREEN** - Write minimum code to pass
3. **REFACTOR** - Improve code without breaking tests

### Definition of Done
- [ ] Tests passing (100/80/0 coverage)
- [ ] Security audit passed (OWASP + GDPR)
- [ ] Accessibility verified (WCAG 2.1 AA, Lighthouse > 90)
- [ ] Code reviewed
- [ ] Documentation updated (TFM standard)
- [ ] Performance acceptable (Core Web Vitals, latency < 200ms)
- [ ] UX approved (@ux_designer) for SAAC interfaces

### User Story Template (SAAC Context)
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
- [ ] Código revisado
- [ ] Documentación actualizada
- [ ] Aprobación UX (@ux_designer)
```

### Test Template (Symfony/Pest)
```php
it('should [behavior]', function () {
    // Arrange
    $input = ['key' => 'value'];
    
    // Act
    $result = $service->execute($input);
    
    // Assert
    expect($result)->toBe('expected');
});
```

### Test Template (Vue/Vitest)
```typescript
it('should [behavior]', () => {
    // Arrange
    const { result } = renderComponent();
    
    // Act
    fireEvent.click(result.button);
    
    // Assert
    expect(result.output).toBe('expected');
});
```

## Getting Started

1. Clone repository
2. Copy `.env.example` to `.env` and configure
3. Run `docker-compose up -d` to start development environment
4. Access backend at http://localhost:8080
5. Access frontend at http://localhost:3000

## Notes

- **Clean Architecture**: Domain layer must NEVER depend on Infrastructure
- **TDD is King**: No production code without a failing test first
- **Security First**: Every change must consider OWASP Top 10 + GDPR
- **Accessibility**: WCAG 2.1 AA mandatory for all UI components
- **SAAC Context**: Design for users with TEA, afasia, PC, ELA
- **Performance**: Latency < 200ms critical for UX (users need immediate feedback)
- **Documentation**: Follow TFM README standard (6 sections)
- **Observability**: PostHog self-hosted + Sentry Cloud for SAAC context
- **Git**: Use conventional commits (feat:, fix:, docs:, test:, etc.)

## Sincronizacion de Agentes

Los agentes se definen en `agents/` (formato OpenCode) y se sincronizan:

```
agents/             ← Fuente (formato OpenCode)
skills/             ← Fuente (compatible ambos)
.opencode/agents    → ../agents (enlace simbolico)
.opencode/skills    → ../skills (enlace simbolico)
.claude/agents/     ← Generado (formato Claude Code)
.claude/skills      → ../skills (enlace simbolico)
```

Despues de modificar un agente en `agents/`, ejecutar:
```bash
./sync-to-claude-code.sh
```

## Support

For questions about:
- **Architecture** → Check ADRs in `/docs/adrs/`
- **Setup issues** → See troubleshooting in README.md
- **Agent usage** → Refer to `agents/` directory