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
├── Domain/                 # Capa de Dominio (✅ completado)
│   ├── Shared/             # Código compartido (DomainException, Uuid)
│   ├── Pictogram/          # Pictogramas (ARASAAC inicial)
│   ├── Category/           # Categorías (Acciones, Emociones, etc.)
│   └── Phrase/             # Frases cacheadas del LLM
├── Application/            # Casos de uso (✅ implementado)
│   ├── Category/           # GetAllCategories
│   ├── Pictogram/          # GetAllPictograms, GetPictogramsByCategory
│   ├── Phrase/             # GenerateHumanizedPhrase (core MVP)
│   ├── DTO/                # CategoryDTO, PictogramDTO, PhraseResponseDTO
│   └── Exception/          # ApplicationException, *NotFoundException
├── Infrastructure/         # Implementaciones técnicas (⏳ pendiente)
└── Shared/                 # Código compartido
```

## Domain Layer

Ver diagramas completos en [docs/diagrams/domain-layer.md](../docs/diagrams/domain-layer.md)

| Módulo | Entidad | Value Objects | Excepciones | Repositorio | Servicio |
|--------|---------|---------------|-------------|-------------|----------|
| **Shared** | - | `Uuid` | `DomainException` | - | `UuidGeneratorInterface` |
| **Pictogram** | `Pictogram` | `PictogramId`, `ArasaacId` | `InvalidPictogramLabelException`, `InvalidImagePathException` | `PictogramRepository` | `PictogramProviderInterface` |
| **Category** | `Category` | `CategoryId` | `InvalidCategoryNameException` | `CategoryRepository` | - |
| **Phrase** | `Phrase` | `PhraseId`, `PictogramSequence` | `InvalidPhraseVariationsException`, `InvalidPictogramSequenceException` | `PhraseRepository` | `PhraseGeneratorInterface` |

> **Nota:** Las interfaces de servicio permiten cambiar proveedores sin modificar el dominio:
> - `PhraseGeneratorInterface` → OpenAI, Claude, Gemini, etc.
> - `PictogramProviderInterface` → ARASAAC, Mulberry Symbols, etc.
>
> **Excepciones de Dominio:** Todas las excepciones extienden `DomainException` para captura semántica en capas superiores.
>
> **UUID Puro:** `Uuid` solo valida formatos. La generación se delega a `UuidGeneratorInterface` (implementación en Infrastructure).

## Application Layer

Ver diagramas completos en [docs/diagrams/application-layer.md](../docs/diagrams/application-layer.md)

| Módulo | Use Cases | DTOs | Excepciones |
|--------|-----------|------|-------------|
| **Category** | `GetAllCategories` | `CategoryDTO` | `CategoryNotFoundException` |
| **Pictogram** | `GetAllPictograms`, `GetPictogramsByCategory` | `PictogramDTO` | `PictogramNotFoundException` |
| **Phrase** | `GenerateHumanizedPhrase` | `PhraseResponseDTO` | - |

### GenerateHumanizedPhrase (Core MVP)

Caso de uso principal que transforma pictogramas en frases humanizadas:

1. **Valida** que todos los pictogramas existen
2. **Busca en caché** por hash SHA256 de la secuencia
3. **Genera con LLM** si no está cacheado (OpenAI, etc.)
4. **Fallback** a concatenación de labels si LLM falla
5. **Guarda en caché** para futuras consultas

```php
// Ejemplo de uso
$response = $generateHumanizedPhrase(['id-comer', 'id-pan']);
// PhraseResponseDTO {
//   variations: ['Quiero comer pan', 'Me gustaría comer pan', 'Deseo comer pan'],
//   source: 'generated' | 'cache' | 'fallback',
//   sequenceHash: 'a1b2c3...',
//   pictogramIds: ['id-comer', 'id-pan']
// }
```

> **Principios SOLID aplicados:**
> - **S:** Cada Use Case hace una sola cosa
> - **O:** Nuevos generadores sin modificar código existente
> - **L:** FakePhraseGenerator intercambiable con OpenAI
> - **I:** Interfaces pequeñas y específicas
> - **D:** Use Cases dependen de interfaces, no implementaciones

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
