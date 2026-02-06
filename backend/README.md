# HABLAIA - Backend API

> REST API Symfony 7 para el comunicador SAAC con IA

## Stack

- **Framework:** Symfony 7.4
- **Lenguaje:** PHP 8.4
- **Base de datos:** PostgreSQL 16
- **ORM:** Cycle ORM (ver [ADR-007](../docs/adrs/ADR-007-cycle-orm-over-doctrine.md))
- **Testing:** PestPHP
- **Arquitectura:** Clean Architecture / DDD

## Estructura

```
src/
├── Domain/                     # Capa de Dominio (pura, sin dependencias externas)
│   ├── Shared/                 # Codigo compartido (DomainException, Uuid, UuidGeneratorInterface)
│   ├── Pictogram/              # Pictogramas (Entity, ValueObjects, Repository, Provider)
│   ├── Category/               # Categorias SAAC (colorHex Fitzgerald Key, displayOrder)
│   └── Phrase/                 # Frases cacheadas del LLM
├── Application/                # Casos de uso
│   ├── Category/               # GetAllCategories
│   ├── Pictogram/              # GetAllPictograms, GetPictogramsByCategory, SearchPictogram
│   ├── Phrase/                 # GenerateHumanizedPhrase (core MVP)
│   ├── DTO/                    # CategoryDTO, PictogramDTO, PhraseResponseDTO
│   └── Exception/              # ApplicationException, *NotFoundException
└── Infrastructure/             # Implementaciones tecnicas
    ├── Console/                # Comandos CLI (app:fixtures:load, app:arasaac:sync)
    ├── DataFixtures/           # CategoryFixtures (7 categorias SAAC, colores Fitzgerald Key)
    ├── ExternalApi/            # Clientes externos
    │   ├── Arasaac/            # ArasaacApiClient (PictogramProviderInterface)
    │   ├── Gemini/             # GeminiPhraseGenerator (PhraseGeneratorInterface)
    │   ├── OpenAI/             # OpenAIPhraseGenerator (PhraseGeneratorInterface)
    │   └── Shared/             # PhrasePrompt (constantes compartidas)
    ├── Health/                 # Health checks (DB status)
    ├── Http/Controller/        # CategoryController, PictogramController, PhraseController, HealthController
    ├── Persistence/Cycle/      # Cycle ORM (entidades, mappers, repositorios)
    ├── Service/                # ImageDownloader, VocabularyLoader
    └── Shared/                 # SymfonyUuidGenerator
```

## Domain Layer

Ver diagramas completos en [docs/diagrams/domain-layer.md](../docs/diagrams/domain-layer.md)

| Modulo | Entidad | Value Objects | Excepciones | Repositorio | Servicio |
|--------|---------|---------------|-------------|-------------|----------|
| **Shared** | - | `Uuid` | `DomainException` | - | `UuidGeneratorInterface` |
| **Pictogram** | `Pictogram` | `PictogramId`, `ArasaacId` | `InvalidPictogramLabelException`, `InvalidImagePathException` | `PictogramRepository` | `PictogramProviderInterface` |
| **Category** | `Category` | `CategoryId` | `InvalidCategoryNameException`, `InvalidCategoryColorException`, `InvalidCategoryDisplayOrderException` | `CategoryRepository` | - |
| **Phrase** | `Phrase` | `PhraseId`, `PictogramSequence` | `InvalidPhraseVariationsException`, `InvalidPictogramSequenceException` | `PhraseRepository` | `PhraseGeneratorInterface` |

> **Nota:** Las interfaces de servicio permiten cambiar proveedores sin modificar el dominio:
> - `PhraseGeneratorInterface` -> OpenAI, Claude, Gemini, etc.
> - `PictogramProviderInterface` -> ARASAAC, Mulberry Symbols, etc.
>
> **Excepciones de Dominio:** Todas las excepciones extienden `DomainException` para captura semantica en capas superiores.
>
> **UUID Puro:** `Uuid` solo valida formatos. La generacion se delega a `UuidGeneratorInterface` (implementacion en Infrastructure).

## Application Layer

Ver diagramas completos en [docs/diagrams/application-layer.md](../docs/diagrams/application-layer.md)

| Modulo | Use Cases | DTOs | Excepciones |
|--------|-----------|------|-------------|
| **Category** | `GetAllCategories` | `CategoryDTO` | `CategoryNotFoundException` |
| **Pictogram** | `GetAllPictograms`, `GetPictogramsByCategory`, `SearchPictogram` | `PictogramDTO` | `PictogramNotFoundException` |
| **Phrase** | `GenerateHumanizedPhrase` | `PhraseResponseDTO` | - |

### GenerateHumanizedPhrase (Core MVP)

Caso de uso principal que transforma pictogramas en frases humanizadas:

1. **Valida** que todos los pictogramas existen
2. **Busca en cache** por hash SHA256 de la secuencia
3. **Genera con LLM** si no esta cacheado (OpenAI, etc.)
4. **Fallback** a concatenacion de labels si LLM falla
5. **Guarda en cache** para futuras consultas

```php
// Ejemplo de uso
$response = $generateHumanizedPhrase(['id-comer', 'id-pan']);
// PhraseResponseDTO {
//   variations: ['Quiero comer pan', 'Me gustaria comer pan', 'Deseo comer pan'],
//   source: 'generated' | 'cache' | 'fallback',
//   sequenceHash: 'a1b2c3...',
//   pictogramIds: ['id-comer', 'id-pan']
// }
```

### SearchPictogram

Caso de uso para busqueda de pictogramas con fallback a ARASAAC API:

1. **Busca localmente** en base de datos (LIKE query)
2. **Si hay resultados locales**, los retorna
3. **Si NO hay resultados**, busca en ARASAAC API
4. **Descarga imagenes** y guarda pictogramas encontrados
5. **Retorna** maximo 10 resultados

> **Principios SOLID aplicados:**
> - **S:** Cada Use Case hace una sola cosa
> - **O:** Nuevos generadores sin modificar codigo existente
> - **L:** FakePhraseGenerator intercambiable con OpenAI
> - **I:** Interfaces pequenas y especificas
> - **D:** Use Cases dependen de interfaces, no implementaciones

## Infrastructure Layer

Ver diagramas completos en [docs/diagrams/infrastructure-layer.md](../docs/diagrams/infrastructure-layer.md)

### External APIs

| Cliente | Interfaz | Descripcion |
|---------|----------|-------------|
| `ArasaacApiClient` | `PictogramProviderInterface` | Cliente para ARASAAC API (busqueda y descarga de pictogramas) |
| `GeminiPhraseGenerator` | `PhraseGeneratorInterface` | Generador de frases con Gemini 2.5 Flash Lite (default, free tier) |
| `RealOpenAIPhraseGenerator` | `PhraseGeneratorInterface` | Generador de frases con OpenAI GPT-4o-mini |
| `FakeOpenAIPhraseGenerator` | `PhraseGeneratorInterface` | Fake para desarrollo/testing (no requiere API key) |
| `PhraseGeneratorFactory` | - | Factory que crea el generador segun `PHRASE_PROVIDER` env var |

### Console Commands

| Comando | Descripcion |
|---------|-------------|
| `app:fixtures:load` | Carga categorias SAAC estandar en la base de datos |
| `app:arasaac:sync` | Sincroniza pictogramas desde ARASAAC API |

### Services

| Servicio | Descripcion |
|----------|-------------|
| `YamlVocabularyLoader` | Carga vocabulario desde archivo YAML |
| `HttpImageDownloader` | Descarga imagenes de ARASAAC CDN |
| `CycleDatabaseHealthChecker` | Verifica conectividad de base de datos |

## Instalacion

```bash
# Instalar dependencias
composer install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales

# Crear base de datos y ejecutar migraciones
php bin/console cycle:migrate

# Cargar categorias iniciales
php bin/console app:fixtures:load

# (Opcional) Sincronizar pictogramas desde ARASAAC
php bin/console app:arasaac:sync --all
```

## Comandos

```bash
# Servidor de desarrollo
symfony server:start

# Tests
composer test

# Tests con cobertura
composer test:coverage

# Analisis estatico
composer analyse

# Limpiar cache
php bin/console cache:clear

# Cargar fixtures (categorias SAAC)
php bin/console app:fixtures:load
php bin/console app:fixtures:load --dry-run  # Ver sin ejecutar

# Sincronizar pictogramas ARASAAC
php bin/console app:arasaac:sync --all                           # Sincronizar todo el vocabulario
php bin/console app:arasaac:sync comer beber --category Acciones # Sincronizar keywords especificos
php bin/console app:arasaac:sync --all --dry-run                 # Ver sin ejecutar
php bin/console app:arasaac:sync --all --limit 3                 # Limitar pictogramas por keyword
```

## API Endpoints

Ver especificacion completa en [docs/openapi.yaml](../docs/openapi.yaml)

| Metodo | Endpoint | Descripcion | Estado |
|--------|----------|-------------|--------|
| GET | `/api/health` | Health check completo (DB status) | Implementado |
| GET | `/api/health/live` | Liveness probe (Kubernetes) | Implementado |
| GET | `/api/health/ready` | Readiness probe (Kubernetes) | Implementado |
| GET | `/api/categories` | Listar categorias | Implementado |
| GET | `/api/categories/{id}` | Obtener categoria por ID | Implementado |
| GET | `/api/pictograms` | Listar pictogramas | Implementado |
| GET | `/api/pictograms?categoryId={id}` | Pictogramas por categoria | Implementado |
| GET | `/api/pictograms/search?q={query}` | Buscar pictogramas | Implementado |
| GET | `/api/pictograms/{id}` | Obtener pictograma por ID | Implementado |
| POST | `/api/phrases/generate` | Generar frase humanizada | Implementado |

### Rate Limiting

El endpoint `/api/phrases/generate` tiene rate limiting para proteger costes de LLM API:
- **Limite:** 30 requests por minuto por IP (configurable via `PHRASE_RATE_LIMIT`)
- **Ventana:** 60 segundos (configurable via `PHRASE_RATE_INTERVAL`)
- **Respuesta:** HTTP 429 con header `Retry-After` cuando se excede

## Testing

Seguimos TDD con cobertura objetivo:
- **Domain:** 100%
- **Application:** 80%
- **Infrastructure:** Tests de integracion

```bash
# Ejecutar tests
./vendor/bin/pest

# Con cobertura
./vendor/bin/pest --coverage

# Tests de un directorio especifico
./vendor/bin/pest tests/Unit/Domain/
./vendor/bin/pest tests/Unit/Application/
./vendor/bin/pest tests/Functional/
```

## Arquitectura

Ver [ADR-001: Clean Architecture](../docs/adrs/ADR-001-clean-architecture.md)

### Regla de Dependencia

```
Domain <- Application <- Infrastructure
```

- **Domain** no depende de nada externo
- **Application** depende solo de Domain
- **Infrastructure** implementa interfaces de Domain

### Diagramas

- [Domain Layer](../docs/diagrams/domain-layer.md) - Entidades, Value Objects, Repositorios
- [Application Layer](../docs/diagrams/application-layer.md) - Use Cases, DTOs, Flujos
- [Infrastructure Layer](../docs/diagrams/infrastructure-layer.md) - Controllers, Persistence, External APIs
- [API Flow](../docs/diagrams/api-flow.md) - Flujos de las APIs principales

## Variables de Entorno

```bash
# Base de datos
DATABASE_URL="postgresql://user:pass@localhost:5432/pictospeak?serverVersion=16&charset=utf8"

# Rate Limiting
PHRASE_RATE_LIMIT="30"
PHRASE_RATE_INTERVAL="60"

# LLM Phrase Generator (gemini | openai | fake)
PHRASE_PROVIDER="gemini"
PHRASE_TEMPERATURE="0.7"
PHRASE_MAX_TOKENS="256"
PHRASE_TIMEOUT="10"

# Gemini (default - free tier)
GEMINI_API_URL="https://generativelanguage.googleapis.com/v1beta/models"
GEMINI_API_KEY="..."
GEMINI_MODEL="gemini-2.5-flash-lite"

# OpenAI (alternative)
OPENAI_API_URL="https://api.openai.com/v1/chat/completions"
OPENAI_API_KEY="sk-..."
OPENAI_MODEL="gpt-4o-mini"

# Directorio de pictogramas
PICTOGRAMS_DIRECTORY="%kernel.project_dir%/public/pictograms"
```

## ADRs Relacionados

- [ADR-001: Clean Architecture](../docs/adrs/ADR-001-clean-architecture.md)
- [ADR-003: Infrastructure Layer](../docs/adrs/ADR-003-infrastructure-layer.md)
- [ADR-007: Cycle ORM over Doctrine](../docs/adrs/ADR-007-cycle-orm-over-doctrine.md)
- [ADR-008: Modified Fitzgerald Key Color Coding](../docs/adrs/ADR-008-fitzgerald-key-color-coding.md)
- [ADR-009: Multi-provider LLM (OpenAI + Gemini)](../docs/adrs/ADR-009-multi-provider-llm.md)
