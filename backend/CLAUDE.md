# HablaIA Backend

## Stack

- **Language:** PHP 8.4+
- **Framework:** Symfony 7.4
- **ORM:** Cycle ORM 2.13
- **Database:** PostgreSQL 16
- **Testing:** PestPHP 3.0
- **Static Analysis:** PHPStan level 8
- **CORS:** nelmio/cors-bundle

## Architecture (Clean Architecture)

```
src/
  Domain/           # Entities, ValueObjects, Repository interfaces (puro PHP, sin dependencias)
  Application/      # UseCases, DTOs, Services
  Infrastructure/   # Persistence (Cycle ORM), Http (Controllers), Security
config/
  packages/
    cycle.yaml          # Cycle ORM services (factories, repositories)
    repositories.yaml   # Domain interface → Infrastructure aliases
    framework.yaml      # Rate limiting, trusted proxies, UUID v7
    monolog.yaml        # JSON logging
    nelmio_cors.yaml    # CORS config
tests/
  Unit/             # Tests unitarios (Domain, Application)
  Functional/       # Tests funcionales (Controllers, API endpoints)
```

## Commands

```bash
composer test              # PestPHP tests
composer test:coverage     # Tests con coverage
composer analyse           # PHPStan level 8
php bin/console cache:clear  # Limpiar cache (obligatorio tras cambios en DI)
symfony server:start       # Dev server
```

## Skills Relevantes

Lee `skills/{skill}/SKILL.md` antes de implementar:
- `symfony` - Framework patterns, DI, errores comunes
- `symfony-pest` - PestPHP + WebTestCase, errores comunes
- `cycle-orm` - Entity mapping, Fragment, JSON typecast, DI factories
- `postgresql` - Schema design, indices, optimizacion
- `llm-integration` - Multi-provider LLM (OpenAI, Gemini, Factory pattern)

## Coverage Targets

- **Domain:** 100% (entidades, value objects, logica de negocio)
- **Application:** 80% (use cases, services)
- **Infrastructure:** Tests funcionales en endpoints criticos

## Configuracion Clave

### Rate Limiting
```yaml
# framework.yaml - /api/phrases/generate
rate_limiter:
  phrase_generator:
    policy: sliding_window
    limit: 30            # PHRASE_RATE_LIMIT (req/min)
    interval: '60 seconds' # PHRASE_RATE_INTERVAL
  phrase_daily:
    policy: fixed_window
    limit: 500           # PHRASE_DAILY_LIMIT (req/dia, 9999 en dev)
    interval: '1 day'
```

### Cycle ORM DI Pattern
```yaml
# cycle.yaml - Registrar nuevo repositorio:
# 1. Factory del ORM
cycle.repository.entity_name:
    class: Cycle\ORM\Select\Repository
    factory: ['@Cycle\ORM\ORM', 'getRepository']
    arguments: [App\Infrastructure\Persistence\Cycle\Entity\EntityNameEntity]

# 2. Wrapper repository
App\Infrastructure\Persistence\Cycle\Repository\CycleEntityNameRepository:
    arguments:
        $repository: '@cycle.repository.entity_name'
        $entityManager: '@Cycle\ORM\EntityManagerInterface'

# repositories.yaml - Alias interface Domain
App\Domain\EntityName\Repository\EntityNameRepository:
    alias: App\Infrastructure\Persistence\Cycle\Repository\CycleEntityNameRepository
```

### Test Suites
```bash
# Ejecutar por suite
./vendor/bin/pest tests/Unit
./vendor/bin/pest tests/Functional

# AMBAS suites (obligatorio antes de commit)
./vendor/bin/pest
```
