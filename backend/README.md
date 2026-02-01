# HablaIA - Backend API

> API REST Symfony 7 para el comunicador SAAC con IA

## Stack

- **Framework:** Symfony 7.4
- **Lenguaje:** PHP 8.4
- **Base de datos:** PostgreSQL 16
- **ORM:** Doctrine
- **Testing:** PestPHP
- **Arquitectura:** Clean Architecture / DDD

## Estructura

```
src/
├── Domain/                 # Capa de Dominio (pura, sin dependencias)
│   ├── Pictogram/          # Pictogramas (ARASAAC inicial)
│   ├── Category/           # Categorías (Acciones, Emociones, etc.)
│   └── Phrase/             # Frases cacheadas del LLM
├── Application/            # Casos de uso (⏳ pendiente)
├── Infrastructure/         # Implementaciones técnicas (⏳ pendiente)
└── Shared/                 # Código compartido
```

## Domain Layer

Ver diagramas completos en [docs/diagrams/domain-layer.md](../docs/diagrams/domain-layer.md)

| Módulo | Entidad | Value Objects | Repositorio | Servicio |
|--------|---------|---------------|-------------|----------|
| **Pictogram** | `Pictogram` | `PictogramId`, `ArasaacId` | `PictogramRepository` | `PictogramProviderInterface` |
| **Category** | `Category` | `CategoryId` | `CategoryRepository` | - |
| **Phrase** | `Phrase` | `PhraseId`, `PictogramSequence` | `PhraseRepository` | `PhraseGeneratorInterface` |

> **Nota:** Las interfaces de servicio permiten cambiar proveedores sin modificar el dominio:
> - `PhraseGeneratorInterface` → OpenAI, Claude, Gemini, etc.
> - `PictogramProviderInterface` → ARASAAC, Mulberry Symbols, etc.

## Instalación

```bash
# Instalar dependencias
composer install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales

# ⏳ Ejecutar migraciones (pendiente)
php bin/console doctrine:migrations:migrate

# ⏳ Sincronizar pictogramas desde ARASAAC (pendiente)
php bin/console app:sync-arasaac
```

## Comandos

```bash
# Servidor de desarrollo
symfony server:start

# Tests
composer test

# Tests con cobertura
composer test:coverage

# Análisis estático
composer analyse

# Limpiar caché
php bin/console cache:clear
```

## API Endpoints (⏳ pendiente)

| Método | Endpoint | Descripción | Estado |
|--------|----------|-------------|--------|
| GET | `/api/categories` | Listar categorías | ⏳ |
| GET | `/api/pictograms?category={id}` | Pictogramas por categoría | ⏳ |
| POST | `/api/phrases` | Generar frase humanizada | ⏳ |
| GET | `/api/health` | Health check | ⏳ |

## Testing

Seguimos TDD con cobertura objetivo:
- **Domain:** 100%
- **Application:** 80%
- **Infrastructure:** Tests de integración

```bash
# Ejecutar tests
./vendor/bin/pest

# Con cobertura
./vendor/bin/pest --coverage
```

## Arquitectura

Ver [ADR-001: Clean Architecture](../docs/adrs/ADR-001-clean-architecture.md)

### Regla de Dependencia

```
Domain ← Application ← Infrastructure
```

- **Domain** no depende de nada externo
- **Application** depende solo de Domain
- **Infrastructure** implementa interfaces de Domain
