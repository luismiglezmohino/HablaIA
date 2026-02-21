# HablaIA

> Comunicador Aumentativo y Alternativo (SAAC) con Inteligencia Artificial Humanizante

[![License: Proprietary](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)
[![Portfolio Project](https://img.shields.io/badge/Portfolio-TFM-blueviolet.svg)]()
[![Development Status](https://img.shields.io/badge/Status-Fase%201%20Completada-brightgreen.svg)]()
[![Fase](https://img.shields.io/badge/Fase-1%2F7-blue.svg)]()
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony)](https://symfony.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.6-3178C6?logo=typescript)](https://typescriptlang.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql)](https://postgresql.org)

**Demo:** [https://damevozya.es](https://damevozya.es) · **Slides:** [https://damevozya.es/slides/](https://damevozya.es/slides/)

> **Nota:** El dominio `damevozya.es` es la URL de producción del proyecto. El nombre del proyecto es **HablaIA**.

---

## 1. Idea General del Proyecto

**HablaIA** es un comunicador aumentativo y alternativo (SAAC) diseñado para personas con dificultades en el habla o la comunicación verbal. A diferencia de los comunicadores tradicionales que generan frases robotizadas y repetitivas, HablaIA utiliza **Inteligencia Artificial** para humanizar la comunicación, generando múltiples variaciones naturales de cada frase. En Fase 2 se añadirá adaptación al contexto temporal (hora del día, día de la semana).

### Visión

Proporcionar una herramienta de comunicación que **devuelva la voz** a personas con diversidad funcional, permitiéndoles expresarse de forma **natural, digna y autónoma**, eliminando la barrera comunicativa que limita su participación social, educativa y laboral.

### Usuarios Objetivo

- **Personas con TEA (Trastorno del Espectro Autista):** Que necesitan apoyo visual para comunicarse
- **Personas con afasia post-ictus:** Adultos que han perdido la capacidad del habla
- **Personas con parálisis cerebral:** Que mantienen capacidad cognitiva pero no pueden hablar
- **Personas con ELA (Esclerosis Lateral Amiotrófica):** En fases avanzadas de la enfermedad
- **Personas mayores con deterioro cognitivo:** Que necesitan apoyo para expresar necesidades básicas
- **Personas con retraso del lenguaje o síndrome de Down:** Niños y adultos en proceso de adquisición del habla
- **Personas en recuperación post-quirúrgica:** Temporalmente sin capacidad de habla (intubación, cirugía maxilofacial)
- **Terapeutas y logopedas:** Como herramienta de apoyo en sesiones de comunicación
- **Familias y cuidadores:** Para facilitar la comunicación diaria

### Valor Diferencial

| Capacidad | LetMeTalk | Proloquo2Go | Grid 3 | TD Snap | **HablaIA** |
|-----------|-----------|-------------|--------|---------|-------------|
| **Construcción de frase** | Concatenación | Manual con popups | Auto-conjugación parcial | Navegación estructurada | **LLM automático** |
| **Carga gramatical para el usuario** | Total | Alta (elegir formas) | Media (auto-conjuga) | Media (navegación) | **Mínima (solo pictogramas)** |
| **Inserción automática artículos/preposiciones** | No | Manual | No | No | **Automática** |
| **Variaciones naturales** | No | No | No | No | **3 por petición** |
| **Adaptación al contexto temporal** | No | No | No | No | **Planificado Fase 2** (hora del día, día de la semana) |
| **Coste** | Gratuito | ~250 EUR | ~600 EUR + hardware | ~15.000 EUR (prescripción médica) | **Proyecto académico (web)** |

Los comunicadores comerciales de gama alta (Tobii Dynavox, Irisbond) requieren **prescripción médica** y plazos de 6-12 meses para su obtención. HablaIA funciona en cualquier tablet/móvil con navegador, sin hardware especializado.

**Ver [Análisis Comparativo Detallado](docs/competitive-analysis.md)** para una comparativa técnica completa con referencias.

#### Ejemplo de Frase Generada

**Input:** Pictogramas ["yo", "querer", "comer"]

| Comunicador | Output |
|-------------|--------|
| **Tradicional** | "Yo quiero comer" |
| **HablaIA** | 1. "Buenos días, tengo hambre"<br>2. "Me apetece desayunar algo"<br>3. "Necesito comer, ¿qué hay para desayunar?" |

La IA contextual **preserva la dignidad comunicativa** del usuario al evitar estructuras infantilizantes.

---

## 2. Descripción General

### Problemática

Las personas con dificultades en la comunicación verbal enfrentan barreras significativas:

- **Aislamiento social:** Incapacidad para expresar necesidades, emociones y pensamientos
- **Dependencia:** Necesitan intermediarios para comunicarse
- **Deshumanización:** Los comunicadores actuales generan frases mecánicas que no reflejan su personalidad
- **Falta de autonomía:** Limitaciones en educación, trabajo y vida independiente

Los comunicadores SAAC tradicionales (Tobii Dynavox, Proloquo2Go) ofrecen pictogramas y síntesis de voz básica, pero **no humanizan** la comunicación. Una persona con capacidad cognitiva intacta se ve obligada a comunicarse con frases como "yo-querer-agua", lo cual resulta infantilizante y limitante.

### Solución: HablaIA

HablaIA combina **pictogramas ARASAAC** (estándar en España), **Inteligencia Artificial contextual** (multi-proveedor configurable vía `PHRASE_PROVIDER`: openai, gemini, fake) y **síntesis de voz** para crear un comunicador que:

1. **Permite seleccionar pictogramas** de forma visual e intuitiva
2. **Genera 3 variaciones de frase humanizada** usando IA
3. **Sintetiza voz** con Web Speech API
4. **Cachea frases** para evitar llamadas redundantes al LLM

### Alcance del MVP

**Fase 1 - Core Funcional (Completada):**
- Grid de pictogramas organizados por 11 categorías SAAC (10 curadas + "Sin categoría") con colores Modified Fitzgerald Key
- Selección multi-pictograma para construir frases
- Integración LLM multi-proveedor para generar 3 variaciones humanizadas
- Text-to-Speech con Web Speech API
- Caché de frases en PostgreSQL (SHA256)
- Búsqueda de pictogramas insensible a acentos con fallback ARASAAC
- Interfaz accesible (WCAG 2.2 AA)
- Responsive: móvil, tablet portrait, tablet landscape

### Contexto de Uso

- **Terapia:** Logopedas y terapeutas ocupacionales en sesiones de comunicación aumentativa
- **Educación:** Aulas de educación especial e integración escolar
- **Hogar:** Comunicación familiar diaria
- **Comunidad:** Participación en actividades sociales y comunitarias
- **Autonomía personal:** Expresar necesidades en entornos médicos, comerciales, etc.

---

## 3. Stack Tecnológico

### Backend - API REST

- **Framework:** Symfony 7.4
- **Lenguaje:** PHP 8.4
- **Base de Datos:** PostgreSQL 16
- **ORM:** Cycle ORM
- **Testing:** PestPHP (400 tests), PHPStan nivel 8
- **Arquitectura:** Clean Architecture (Domain-Application-Infrastructure)

### Frontend - Single Page Application

- **Framework:** Vue.js 3.5 (Composition API)
- **Lenguaje:** TypeScript 5.6
- **Bundler:** Vite 6
- **CSS Framework:** Tailwind CSS 3.4
- **State Management:** Pinia
- **UI Components:** shadcn-vue (Radix Vue primitives, WAI-ARIA)
- **Icons:** Lucide Vue Next
- **Validación:** Zod (runtime API response validation)
- **HTTP:** Fetch API nativo
- **Testing:** Vitest (263 unit) + Playwright (21 E2E)
- **Router:** Vue Router 4

### Inteligencia Artificial y APIs Externas

- **LLM para generación de frases:** Multi-proveedor configurable vía `PHRASE_PROVIDER`:
  - `openai` - Cualquier API compatible OpenAI (en producción: Groq GPT-OSS 120B)
  - `gemini` - Gemini 2.5 Flash
  - `fake` - Respuestas simuladas (sin API key)
  - Valor inválido → fallback a `fake`
- **ARASAAC API:** Repositorio de pictogramas (30,000+ símbolos en español)
- **Web Speech API:** Síntesis de voz nativa del navegador

### Observabilidad y Monitoreo

- **Error Tracking:** Sentry Cloud free tier (integrado: @sentry/vue + sentry-symfony)
- **Logs:** Estructurados en JSON con correlationId

### DevOps y CI/CD

- **Contenedores:** Docker y Docker Compose
- **CI/CD:** GitHub Actions (3 workflows CI + 1 CD)
- **Pre-commit Hooks:** Husky (detección de secrets, eslint + vue-tsc + vitest, phpstan + pest)
- **Commit Convention:** Commitlint en CI (conventional commits)
- **Linting:** ESLint (Frontend), PHPStan nivel 8 (Backend)
- **Deploy:** SSH a Hetzner CX33 con Docker Compose producción

---

## 4. Instalación y Ejecución

### Requisitos Previos

- **Docker:** 24.0+ y Docker Compose 2.20+
- **Node.js:** 20.x LTS (para desarrollo frontend local)
- **Composer:** 2.6+ (para desarrollo backend local)
- **Git:** 2.40+

### Pasos de Instalación

#### 1. Clonar el Repositorio

```bash
git clone https://github.com/luismiglezmohino/HablaIA.git
cd HablaIA
```

#### 2. Configurar Variables de Entorno

```bash
# Backend
cp backend/.env.example backend/.env

# Frontend
cp frontend/.env.example frontend/.env
```

**Edita `backend/.env` y configura:**

```env
# Base de datos
DATABASE_URL="postgresql://hablaia_user:hablaia_pass@postgres:5432/hablaia?serverVersion=16&charset=utf8"

# Symfony
APP_ENV=dev
APP_SECRET=genera-un-secreto-aleatorio-aqui

# LLM Phrase Generator (openai | gemini | fake)
PHRASE_PROVIDER="openai"
PHRASE_TEMPERATURE="0.5"
PHRASE_MAX_TOKENS="256"
PHRASE_TIMEOUT="10"

# OpenAI-compatible (default - Groq GPT-OSS 120B)
OPENAI_API_URL="https://api.groq.com/openai/v1/chat/completions"
OPENAI_API_KEY=""
OPENAI_MODEL="openai/gpt-oss-120b"

# Gemini (alternativa)
GEMINI_API_URL="https://generativelanguage.googleapis.com/v1beta/models"
GEMINI_API_KEY="tu-clave-gemini"
GEMINI_MODEL="gemini-2.5-flash"

# Rate Limiting (POST /api/phrases/generate)
PHRASE_RATE_LIMIT="30"
PHRASE_RATE_INTERVAL="60"
PHRASE_DAILY_LIMIT="500"
```

**Solo si ejecutas el frontend fuera de Docker**, el proxy usa `http://localhost:8080` por defecto. Para sobreescribirlo, crea `frontend/.env`:

```env
VITE_API_TARGET=http://localhost:8080
```

#### 3. Levantar Servicios con Docker

```bash
docker-compose up -d
```

Esto iniciará:
- PostgreSQL (puerto 5432)
- Backend Symfony (puerto 8080)
- Frontend Vue (puerto 3000)
- Swagger UI (puerto 8081, solo con perfil `dev`)

#### 4. Instalar Dependencias Backend

```bash
cd backend
composer install
```

#### 5. Ejecutar Migraciones de Base de Datos

```bash
php bin/console cycle:migrate
```

#### 6. Sincronizar Pictogramas desde ARASAAC

```bash
php bin/console app:arasaac:sync --all
```

Este comando descargará 194 pictogramas (197 palabras core vocabulary) desde la API de ARASAAC y los almacenará en la base de datos.

#### 7. Instalar Dependencias Frontend

```bash
cd ../frontend
npm install
```

#### 8. Acceder a la Aplicación

- **Frontend (Aplicación):** http://localhost:3000
- **Backend API:** http://localhost:8080/api
- **Swagger UI:** http://localhost:8081 (requiere `docker compose --profile dev up`)

> Para problemas comunes de instalación, ver [Troubleshooting](docs/guides/TROUBLESHOOTING.md).

### Scripts Disponibles

#### Backend (Symfony)

```bash
# Ejecutar tests con PestPHP
composer test

# Tests con reporte de cobertura
composer test:coverage

# Análisis estático de código (PHPStan nivel 8)
composer analyse

# Limpiar caché
php bin/console cache:clear

# Cargar categorías SAAC (11 categorías: 10 curadas + "Sin categoría")
php bin/console app:fixtures:load

# Sincronizar pictogramas desde ARASAAC (197 palabras core vocabulary)
php bin/console app:arasaac:sync --all

# Sincronizar keywords específicos
php bin/console app:arasaac:sync comer beber dormir --category=Acciones

# Ver qué se sincronizaría (sin ejecutar)
php bin/console app:arasaac:sync --all --dry-run
```

#### Frontend (Vue + TypeScript)

```bash
# Servidor de desarrollo con hot-reload
npm run dev

# Build de producción
npm run build

# Ejecutar tests unitarios
npm run test

# Tests con reporte de cobertura
npm run test:coverage

# Tests en modo watch
npm run test:watch

# Tests E2E con Playwright
npm run test:e2e

# E2E con interfaz visual
npm run test:e2e:ui

# Linting con ESLint
npm run lint

# Auto-fix de problemas de linting
npm run lint:fix
```

#### Docker

```bash
# Levantar todos los servicios
docker-compose up -d

# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend

# Parar todos los servicios
docker-compose down

# Reconstruir imágenes
docker-compose build --no-cache
```

---

## 5. Estructuración

### Arquitectura General

```
hablaia/
├── .github/          # CI/CD y plantillas
│   └── workflows/    # 3 CI (backend, frontend, commitlint) + 1 CD
├── .husky/           # Git hooks (pre-commit, pre-push)
├── agents/           # Agentes IA para desarrollo
├── backend/          # API REST Symfony 7
├── docker/           # Configuración Docker
├── docs/             # Documentación
│   ├── adrs/         # Architecture Decision Records (15)
│   ├── api-tests/    # Pruebas de prompt LLM
│   ├── audits/       # Auditorías (seguridad, rendimiento, accesibilidad, calidad)
│   ├── diagrams/     # Diagramas de arquitectura
│   ├── guides/       # Guías (troubleshooting, seguridad, rendimiento, accesibilidad)
│   ├── screenshots/  # Capturas de pantalla
│   └── testing/      # Documentación de testing
├── frontend/         # SPA Vue.js 3
└── skills/           # Skills para agentes IA
```

### Backend - Clean Architecture

```
backend/src/
├── Domain/           # Capa de Dominio
│   ├── Shared/       # DomainException base, Uuid, UuidGeneratorInterface
│   ├── Category/     # Categorías de pictogramas
│   │   ├── Entity/
│   │   ├── ValueObject/
│   │   ├── Exception/
│   │   └── Repository/
│   ├── Pictogram/    # Pictogramas ARASAAC
│   │   ├── Entity/
│   │   ├── ValueObject/
│   │   ├── Exception/
│   │   ├── Repository/
│   │   └── Service/
│   └── Phrase/       # Frases generadas por LLM
│       ├── Entity/
│       ├── ValueObject/
│       ├── Exception/
│       ├── Repository/
│       └── Service/
├── Application/      # Casos de Uso
│   ├── Category/     # GetAllCategories
│   ├── Pictogram/    # GetAllPictograms, GetPictogramsByCategory, SearchPictogram
│   ├── Phrase/       # GenerateHumanizedPhrase (core MVP)
│   ├── DTO/          # CategoryDTO, PictogramDTO, PhraseResponseDTO
│   └── Exception/    # ApplicationException, *NotFoundException
├── Infrastructure/   # Implementaciones
│   ├── Console/      # LoadFixturesCommand, SyncArasaacCommand
│   ├── DataFixtures/ # CategoryFixtures (11 categorías SAAC, colores Fitzgerald Key)
│   ├── ExternalApi/  # Arasaac/, Gemini/, OpenAI/, Shared/, PhraseGeneratorFactory
│   ├── Health/       # DatabaseHealthChecker
│   ├── Http/         # Controllers (Category, Pictogram, Phrase, Health)
│   ├── Persistence/  # Cycle ORM (Entities, Mappers, Repositories)
│   ├── Service/      # VocabularyLoader, ImageDownloader
│   └── Shared/       # SymfonyUuidGenerator
└── Shared/           # Utils compartidos
```

### Frontend - Clean Architecture

```
frontend/src/
├── domain/           # Capa de Dominio (TypeScript puro)
│   ├── entities/     # Category, Pictogram, PhraseResponse
│   ├── repositories/ # Interfaces: CategoryRepository, PictogramRepository, PhraseRepository
│   └── services/     # Interfaces: TTSProvider
├── application/      # Capa de Aplicación
│   ├── schemas/      # Zod schemas (API response validation)
│   ├── stores/       # Pinia stores (useCategoryStore, usePictogramStore, usePhraseStore)
│   └── composables/  # Vue composables (useTTS)
├── infrastructure/   # Implementaciones
│   ├── http/         # ApiClient, HttpCategoryRepository, HttpPictogramRepository, HttpPhraseRepository
│   └── tts/          # WebSpeechTTS (Web Speech API)
├── presentation/     # UI (Vue)
│   ├── components/   # Componentes Vue
│   ├── views/        # HomeView
│   └── router/       # Vue Router
└── lib/              # Utilidades (cn helper)
```

> La documentación completa del proyecto (ADRs, diagramas, auditorías, API, testing) está indexada en **[docs/INDEX.md](docs/INDEX.md)**.

### Principio de Dependencia

```
Domain ← Application ← Infrastructure
   ↑                        │
   └────────────────────────┘
   Infrastructure implementa interfaces de Domain
```

### ADRs (Architecture Decision Records)

Documentan las decisiones arquitectónicas del proyecto:

```
docs/adrs/
├── ADR-001-clean-architecture.md
├── ADR-002-openai-integration.md
├── ADR-003-arasaac-pictograms.md
├── ADR-004-tts-strategy.md
├── ADR-005-phrase-caching.md
├── ADR-006-uuid-agnostic-domain.md
├── ADR-007-cycle-orm-over-doctrine.md
├── ADR-008-fitzgerald-key-color-coding.md
├── ADR-009-multi-provider-llm.md
├── ADR-010-inline-feedback-over-toasts.md
├── ADR-011-visual-design-system.md
├── ADR-012-cd-pipeline.md
├── ADR-013-keyboard-screenreader-accessibility.md
├── ADR-014-monorepo-structure.md
└── ADR-015-groq-primary-llm-provider.md
```

---

## 6. Funcionalidades

### Fase 1 (MVP) - Completada

| Funcionalidad | Estado | Descripción |
|---------------|--------|-------------|
| Grid de pictogramas | ✅ | GET /api/pictograms, GET /api/categories (11 categorías SAAC, colores Fitzgerald Key) |
| Búsqueda de pictogramas | ✅ | GET /api/pictograms/search?q= (insensible a acentos, fallback ARASAAC) |
| Generación IA | ✅ | POST /api/phrases/generate (3 variaciones humanizadas) |
| Caché de frases | ✅ | PostgreSQL + SHA256 hash |
| Sincronización ARASAAC | ✅ | app:arasaac:sync (194 pictogramas, 197 palabras core vocabulary) |
| Health checks | ✅ | /api/health, /api/health/live, /api/health/ready |
| Text-to-Speech | ✅ | Web Speech API (SpeakButton, useTTS composable) |
| Accesibilidad WCAG 2.2 AA | ✅ | 44x44px targets, focus rings, navegación por teclado, screen reader |
| Error handling UI | ✅ | Inline feedback, mensajes en español, retry |
| Responsive | ✅ | Móvil, tablet portrait, tablet landscape |

### Objetivos Técnicos

| Aspecto | Estado | Descripción |
|---------|--------|-------------|
| Clean Architecture | ✅ | Backend: Domain → Application → Infrastructure. Frontend: ídem |
| TDD | ✅ | 400 tests (backend) + 263 unit + 21 E2E (frontend) = 684 total |
| Docker | ✅ | Contenedores dev + prod (PHP-FPM + Nginx + PostgreSQL) |
| CI/CD | ✅ | GitHub Actions + Husky pre-commit + commitlint + deploy automático |
| Seguridad | ✅ | SSRF protection, Path Traversal, MIME validation, Rate limiting, Nginx reverse proxy |
| Observabilidad | ✅ | Sentry Cloud (frontend + backend), health checks |

### Roadmap de Desarrollo

| Fase | Estado | Descripción |
|------|--------|-------------|
| **Fase 1** | ✅ COMPLETADA | Core funcional (pictogramas + IA + TTS básico) |
| **Fase 2** | 📅 Planificada | Mejoras de comunicación y emergencia |
| **Fase 3** | 📅 Planificada | Autenticación y multiusuario |
| **Fase 4** | 📅 Planificada | Comunicación personalizada |
| **Fase 5** | 📅 Planificada | Gestión de pictogramas y categorías |
| **Fase 6** | 📅 Planificada | Modo terapeuta |
| **Fase 7** | 📅 Planificada | Visualización, compartir y exportación |

**Ver [Roadmap Completo](docs/ROADMAP.md)** para detalles de cada fase.

---

## Documentación Adicional

- **[Índice de Documentación](docs/INDEX.md):** Guía de navegación completa
- **[OpenAPI Specification](docs/openapi.yaml):** Especificación completa de la API REST
- **[Architecture Decision Records](docs/adrs/):** 15 decisiones de arquitectura documentadas
- **[Diagramas de Arquitectura](docs/diagrams/):** Domain, Application, Infrastructure, API Flow, Docker
- **[Roadmap](docs/ROADMAP.md):** Plan de desarrollo en 7 fases
- **[Troubleshooting](docs/guides/TROUBLESHOOTING.md):** Solución de problemas comunes

---

## Licencia

**Todos los derechos reservados.**

Este proyecto es un **trabajo de fin de máster (TFM)** y proyecto de portfolio. El código fuente es propietario y se comparte de forma privada únicamente para:

- Evaluación académica (tutores/evaluadores TFM)
- Demostración de competencias técnicas (procesos de selección)

**NO está permitido** ningún uso, copia, modificación o distribución sin autorización escrita.

**Nota sobre ARASAAC:** Los pictogramas de ARASAAC están bajo licencia **Creative Commons BY-NC-SA 4.0**.

Ver [LICENSE](LICENSE) para más detalles.

---

## Contacto

- **GitHub:** [Perfil del autor](https://github.com/luismiglezmohino)
- **Documentación:** `/docs` en este repositorio

---

## Agradecimientos

- **ARASAAC** (Gobierno de Aragón) por proporcionar pictogramas de calidad bajo licencia abierta
- **Comunidad SAAC** de terapeutas, familias y usuarios que inspiran este proyecto
