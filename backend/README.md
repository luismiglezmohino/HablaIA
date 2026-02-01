# HablaIA - Backend API

> API REST Symfony 7 para el comunicador SAAC con IA

## Stack

- **Framework:** Symfony 7.2
- **Lenguaje:** PHP 8.2
- **Base de datos:** PostgreSQL 16
- **ORM:** Doctrine
- **Testing:** PestPHP
- **Arquitectura:** Clean Architecture

## Estructura

```
src/
├── Domain/           # Capa de Dominio (pura, sin dependencias)
│   ├── Entity/       # Entidades de negocio
│   ├── ValueObject/  # Value Objects
│   ├── Repository/   # Interfaces de repositorios
│   └── Service/      # Servicios de dominio
├── Application/      # Casos de uso
│   ├── UseCase/      # Casos de uso organizados por módulo
│   ├── DTO/          # Data Transfer Objects
│   └── Service/      # Servicios de aplicación
├── Infrastructure/   # Implementaciones técnicas
│   ├── Persistence/  # Doctrine ORM
│   ├── Http/         # Controllers API REST
│   ├── Security/     # Autenticación/Autorización
│   └── ExternalApi/  # Clientes HTTP (OpenAI, ARASAAC)
└── Shared/           # Código compartido
    ├── Exception/    # Excepciones custom
    └── Validator/    # Validadores
```

## Instalación

```bash
# Instalar dependencias
composer install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Sincronizar pictogramas desde ARASAAC
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

# Formateo de código
composer format

# Limpiar caché
php bin/console cache:clear
```

## API Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/categories` | Listar categorías |
| GET | `/api/pictograms?category={id}` | Pictogramas por categoría |
| POST | `/api/phrases` | Generar frase humanizada |
| GET | `/api/health` | Health check |

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
