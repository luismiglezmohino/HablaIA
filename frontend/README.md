# HablaIA - Frontend SPA

> Single Page Application Vue.js 3 para el comunicador SAAC con IA

## Stack

- **Framework:** Vue.js 3.5 (Composition API)
- **Lenguaje:** TypeScript 5.3
- **Bundler:** Vite 5
- **CSS:** Tailwind CSS 3.4
- **Validación:** Zod
- **Testing:** Vitest
- **Arquitectura:** Clean Architecture

## Estructura

```
src/
├── domain/           # Capa de Dominio
│   ├── entity/       # Entidades de negocio
│   ├── valueobject/  # Value Objects
│   └── repository/   # Interfaces de repositorios
├── application/      # Casos de uso
│   ├── usecase/      # Casos de uso
│   └── service/      # Servicios de aplicación
├── infrastructure/   # Implementaciones
│   ├── http/         # API Clients
│   ├── storage/      # LocalStorage/IndexedDB
│   └── tts/          # Text-to-Speech
├── presentation/     # UI Layer (Vue)
│   ├── components/   # Componentes Vue
│   ├── views/        # Páginas/Vistas
│   ├── composables/  # Composables Vue
│   └── router/       # Vue Router
└── shared/           # Código compartido
    ├── validation/   # Zod schemas
    ├── types/        # TypeScript types
    └── utils/        # Utilidades
```

## Instalación

```bash
# Instalar dependencias
npm install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con la URL del backend
```

## Comandos

```bash
# Servidor de desarrollo
npm run dev

# Build de producción
npm run build

# Preview del build
npm run preview

# Tests
npm run test

# Tests con cobertura
npm run test:coverage

# Tests en modo watch
npm run test:watch

# Linting
npm run lint

# Formateo
npm run format
```

## Accesibilidad

El frontend cumple **WCAG 2.1 AA**:

- Contraste mínimo 4.5:1
- Click targets ≥ 44x44px
- Navegación por teclado
- ARIA labels en elementos interactivos
- Compatible con screen readers

```bash
# Tests de accesibilidad
npm run test:a11y
```

## Testing

Seguimos TDD con cobertura objetivo:
- **Domain:** 100%
- **Application:** 80%
- **Composables:** 100%
- **Components:** 80%

```bash
# Ejecutar tests
npm run test

# Con cobertura
npm run test:coverage
```

## Arquitectura

Ver [ADR-001: Clean Architecture](../docs/adrs/ADR-001-clean-architecture.md)

### Regla de Dependencia

```
domain ← application ← infrastructure ← presentation
```

- **domain** no depende de nada externo
- **application** depende solo de domain
- **infrastructure** implementa interfaces de domain
- **presentation** usa application y puede acceder a infrastructure
