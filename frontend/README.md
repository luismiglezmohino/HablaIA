# HablaIA - Frontend SPA

> Single Page Application Vue.js 3 para el comunicador SAAC con IA

## Stack

- **Framework:** Vue.js 3.5 (Composition API)
- **Lenguaje:** TypeScript 5.3
- **Bundler:** Vite 5
- **CSS:** Tailwind CSS 3.4
- **State Management:** Pinia
- **UI Components:** shadcn-vue (Radix Vue primitives)
- **Icons:** Lucide Vue Next
- **Validación:** Zod (runtime schemas para API responses)
- **HTTP:** Fetch API nativo
- **Utilities:** @vueuse/core
- **Testing:** Vitest
- **Arquitectura:** Clean Architecture

## Estructura

```
src/
├── domain/           # Capa de Dominio (TypeScript puro, sin dependencias)
│   ├── entities/     # Category, Pictogram, PhraseResponse
│   ├── repositories/ # Interfaces: CategoryRepository, PictogramRepository, PhraseRepository
│   └── services/     # Interfaces: TTSProvider (futuro)
├── application/      # Capa de Aplicación
│   ├── schemas/      # Zod schemas para validacion de API responses
│   ├── stores/       # Pinia stores (futuro)
│   └── composables/  # Vue composables (futuro)
├── infrastructure/   # Implementaciones
│   ├── http/         # ApiClient + HTTP repositories
│   ├── storage/      # LocalStorage/IndexedDB (futuro)
│   └── tts/          # Text-to-Speech (futuro)
├── presentation/     # UI Layer (Vue)
│   ├── components/   # Componentes Vue
│   ├── views/        # Paginas/Vistas
│   ├── layouts/      # Layouts (futuro)
│   └── router/       # Vue Router
└── lib/              # Utilidades (cn helper para shadcn-vue)
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

El frontend cumple **WCAG 2.2 AA**:

- Contraste mínimo 4.5:1
- Click targets ≥ 44x44px
- Navegación por teclado
- ARIA labels en elementos interactivos
- Compatible con screen readers

Tests de accesibilidad se ejecutaran con Lighthouse CI (configurado en `lighthouserc.json`).

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
