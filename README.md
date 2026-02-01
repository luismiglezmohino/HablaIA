# HablaIA

> Comunicador Aumentativo y Alternativo (SAAC) con Inteligencia Artificial Humanizante

[![License: Proprietary](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)
[![Portfolio Project](https://img.shields.io/badge/Portfolio-TFM-blueviolet.svg)]()
[![Development Status](https://img.shields.io/badge/Status-Fase%201%20en%20Desarrollo-yellow.svg)]()
[![Phase](https://img.shields.io/badge/Fase-1%2F6-orange.svg)]()
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony)](https://symfony.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.3-3178C6?logo=typescript)](https://typescriptlang.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql)](https://postgresql.org)

---

## 1. Idea General del Proyecto

**HablaIA** es un comunicador aumentativo y alternativo (SAAC) diseñado para personas con dificultades en el habla o la comunicación verbal. A diferencia de los comunicadores tradicionales que generan frases robotizadas y repetitivas, HablaIA utiliza **Inteligencia Artificial** para humanizar la comunicación, generando múltiples variaciones naturales de cada frase y adaptándose al contexto temporal del usuario.

### 🎯 Visión

Proporcionar una herramienta de comunicación que **devuelva la voz** a personas con diversidad funcional, permitiéndoles expresarse de forma **natural, digna y autónoma**, eliminando la barrera comunicativa que limita su participación social, educativa y laboral.

### 👥 Usuarios Objetivo

- **Personas con TEA (Trastorno del Espectro Autista):** Que necesitan apoyo visual para comunicarse
- **Personas con afasia post-ictus:** Adultos que han perdido la capacidad del habla
- **Personas con parálisis cerebral:** Que mantienen capacidad cognitiva pero no pueden hablar
- **Personas con ELA (Esclerosis Lateral Amiotrófica):** En fases avanzadas de la enfermedad
- **Terapeutas y logopedas:** Como herramienta de intervención terapéutica
- **Familias y cuidadores:** Para facilitar la comunicación diaria

### 💎 Valor Diferencial

#### Comparativa con Soluciones Existentes

| Característica | Tobii Dynavox i-Series | Irisbond Hiru/Duo | Proloquo2Go | Let Me Talk | **HablaIA** |
|----------------|------------------------|-------------------|-------------|-------------|-------------|
| **Coste** | €15,000 + €2,000 TTS | €5,000-€10,000 | €280 | Gratuito | **Gratuito → €5/mes premium** |
| **Acceso** | Prescripción médica | Prescripción médica | App Store | Google Play | **Web (cualquier dispositivo)** |
| **Tiempo de obtención** | 6-12 meses | 6-12 meses | Inmediato | Inmediato | **Inmediato** |
| **Calidad de voz** | ⭐⭐⭐⭐⭐ (Acapela) | ⭐⭐⭐⭐ (depende SW) | ⭐⭐⭐⭐ | ⭐⭐ (eSpeak) | **⭐⭐⭐ → ⭐⭐⭐⭐⭐ (progresivo)** |
| **IA contextual** | ❌ | ❌ | ❌ | ❌ | **✅ (3 variaciones)** |
| **Voice cloning** | ❌ | ❌ | ❌ | ❌ | **✅ (Fase 6)** |
| **Hardware requerido** | Propietario (~€5K) | Eye-tracker (€3K-€6K) | iPad | Android | **Cualquier tablet/móvil** |
| **Input method** | Eye-tracking | Eye-tracking | Touch | Touch | **Touch** |
| **Target usuario** | Movilidad muy reducida | SIN movilidad manos | General | General | **CON movilidad táctil** |
| **Modo offline** | ✅ | ✅ | ✅ | ✅ | **✅ (Fase 5)** |
| **Personalización** | ⚠️ Limitada | ⚠️ Depende SW | ⚠️ Limitada | ❌ | **✅ Completa** |
| **Origen** | 🇺🇸 Suecia/USA | 🇪🇸 España | 🇺🇸 USA | 🇩🇪 Alemania | **🇪🇸 España** |

#### ¿Por qué HablaIA si Tobii e Irisbond ya existen?

**Tobii Dynavox** e **Irisbond** son soluciones profesionales de alta calidad para usuarios con necesidades específicas:

**Tobii Dynavox:**
- ✅ Mejor síntesis de voz del mercado (Acapela TTS)
- ✅ Eye-tracking de precisión submilimétrica
- ✅ Integración con control ambiental
- ❌ **Coste:** €15,000-€20,000 (hardware + software + TTS)
- ❌ **Requiere:** Prescripción médica + aprobación seguridad social
- ❌ **Espera:** 6-12 meses de trámites burocráticos

**Irisbond (empresa española):**
- ✅ Eye-tracking más accesible que Tobii (€5,000 vs €15,000)
- ✅ Tecnología española (Barcelona)
- ✅ Integración con software SAAC existente (Grid 3, Communicator 5)
- ❌ **Coste total:** €5,000-€10,000 (eye-tracker + software + subscripciones)
- ❌ **Requiere:** Hardware propietario + prescripción médica
- ❌ **Target:** Usuarios SIN movilidad de manos (ELA avanzado, tetraplejia)
- ❌ **Software SAAC:** De terceros, sin IA contextual

**Resultado común:** Solo el **5% de personas con necesidad de SAAC en España** tienen acceso a estas soluciones. El **95% restante (285,000 personas)** quedan excluidas por:
1. **Barrera económica:** €5,000-€20,000 es inaccesible para familias sin seguridad social
2. **Barrera burocrática:** 6-12 meses de espera durante los cuales NO pueden comunicarse
3. **Barrera geográfica:** Distribución limitada en España, casi nula en Latinoamérica
4. **Barrera de caso de uso:** Eye-tracking solo para usuarios SIN movilidad de manos

**HablaIA resuelve un problema diferente:**

| Criterio | Tobii/Irisbond | HablaIA |
|----------|----------------|---------|
| **Mercado objetivo** | 5% con prescripción médica + €5K-€20K | **95% sin acceso a soluciones premium** |
| **Caso de uso** | Movilidad muy reducida (eye-tracking) | **Movilidad táctil (tablets, el 70% de usuarios SAAC)** |
| **Modelo de acceso** | Institucional (hospitales, seguros) | **Directo al consumidor (familias, terapeutas)** |
| **Geografía** | España + países desarrollados | **España + Latinoamérica (650M hispanohablantes)** |
| **Innovación clave** | Hardware (eye-tracking) | **Software (IA contextual + voz progresiva)** |

**HablaIA democratiza el acceso:**
- ✅ **Gratuito** en versión básica (Web Speech API)
- ✅ **Inmediato** (sin prescripción médica, sin espera)
- ✅ **Universal** (funciona en tablets/móviles que la familia ya tiene)
- ✅ **Progresivo** (empieza funcional, evoluciona a premium a coste accesible)
- ✅ **IA contextual** (Tobii genera "Yo quiero comer", HablaIA genera "Tengo hambre" / "Me apetece comer algo" / "Necesito comer, ¿qué hay?")

#### Modelo de Voz Progresiva

| Fase | Motor TTS | Calidad | Coste/usuario/año | Target |
|------|-----------|---------|-------------------|--------|
| **Fase 1 (MVP)** | Web Speech API | ⭐⭐⭐ | €0 | 100% usuarios |
| **Fase 4** | ElevenLabs Premium | ⭐⭐⭐⭐⭐ | €60 | 20% (premium opt-in) |
| **Fase 6** | Voice Cloning | ⭐⭐⭐⭐⭐ | €99 one-time | 5-10% (early adopters) |

**Estrategia:** Priorizar **acceso universal** (Fase 1) sobre calidad máxima inicial. Una vez validado el valor, ofrecer upgrades premium a coste 200x inferior a Tobii.

#### TCO (Total Cost of Ownership) - 5 años

| Solución | Hardware | Software Inicial | Anual | Total 5 años |
|----------|----------|------------------|-------|--------------|
| **Tobii Dynavox** | €10,000 | €5,000 (SW + TTS) | €1,000 | **€20,000** |
| **Irisbond + Grid 3** | €5,000 | €2,000 (SW) | €500 | **€9,500** |
| **Proloquo2Go** | €0 (iPad propio) | €280 | €0 | **€280** |
| **HablaIA Free** | €0 | €0 | €0 | **€0** |
| **HablaIA Premium** | €0 | €0 | €60 | **€300** (1.5% de Tobii, 3.2% de Irisbond) |

**Nota:** HablaIA funciona en tablets/móviles que las familias ya poseen, eliminando coste de hardware.

#### Tabla Comparativa de Frases Generadas

**Input:** Pictogramas ["yo", "querer", "comer"]  
**Contexto:** Mañana, 09:30 AM

| Solución | Output |
|----------|--------|
| **Tobii Dynavox** | "Yo quiero comer" |
| **Proloquo2Go** | "Yo quiero comer" |
| **Let Me Talk** | "Yo querer comer" |
| **HablaIA** | 1. "Buenos días, tengo hambre"<br>2. "Me apetece desayunar algo"<br>3. "Necesito comer, ¿qué hay para desayunar?" |

**Diferenciador clave:** La IA contextual de HablaIA no solo mejora la naturalidad, sino que **preserva la dignidad comunicativa** del usuario al evitar estructuras infantilizantes.

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

HablaIA combina **pictogramas ARASAAC** (estándar en España), **Inteligencia Artificial contextual** (OpenAI GPT-4o-mini inicialmente, arquitectura agnóstica de proveedor) y **síntesis de voz progresiva** para crear un comunicador que:

1. **Permite seleccionar pictogramas** de forma visual e intuitiva
2. **Genera 3 variaciones de frase humanizada** usando IA contextual
3. **Adapta el lenguaje al momento del día** (mañana, tarde, noche, día de la semana)
4. **Sintetiza voz natural** con progresión hacia voice cloning
5. **Aprende y mejora** con el uso mediante caché inteligente

### Alcance del MVP (Fases 1-2)

**Fase 1 - Core Funcional:**
- Grid de pictogramas organizados por categorías (Acciones, Emociones, Personas, Objetos)
- Selección multi-pictograma para construir frases
- Integración LLM (OpenAI inicial) para generar 3 variaciones humanizadas
- Text-to-Speech con Web Speech API
- Caché de frases en PostgreSQL
- Interfaz accesible (WCAG 2.1 AA)

**Fase 2 - Mejoras UX (Futuro):**
- Contexto temporal inteligente (hora del día, día de la semana)
- Sincronización automática con ARASAAC API
- Historial de frases frecuentes
- Configuración de preferencias de usuario

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
- **ORM:** Doctrine ORM
- **Testing:** PestPHP
- **Validación:** Symfony Validator Component
- **Arquitectura:** Clean Architecture (Domain-Application-Infrastructure)

### Frontend - Single Page Application

- **Framework:** Vue.js 3.5 (Composition API)
- **Lenguaje:** TypeScript 5.3
- **Bundler:** Vite 5
- **CSS Framework:** Tailwind CSS 3.4
- **Validación:** Zod
- **Testing:** Vitest
- **Router:** Vue Router 4

### Inteligencia Artificial & APIs Externas

- **LLM para generación de frases:** OpenAI GPT-4o-mini (inicial). Arquitectura agnóstica permite cambiar a Claude, Gemini, LLaMA, etc.
- **ARASAAC API:** Repositorio de pictogramas (30,000+ símbolos en español)
- **Web Speech API:** Síntesis de voz nativa del navegador (Fase 1)
- **ElevenLabs API:** Text-to-Speech premium (Fase 4 - futuro)
- **Voice Cloning:** Clonación de voz del usuario (Fase 6 - futuro)

### Observabilidad & Monitoreo

- **Analytics:** PostHog (self-hosted)
- **Error Tracking:** Sentry Cloud
- **Logs:** Estructurados en JSON con correlationId
- **Métricas:** Prometheus + Grafana (futuro)

### DevOps & CI/CD

- **Contenedores:** Docker & Docker Compose
- **CI/CD:** GitHub Actions
- **Pre-commit Hooks:** Husky
- **Linting:** ESLint (Frontend), PHPStan (Backend)
- **Formateo:** Prettier (Frontend), PHP-CS-Fixer (Backend)

### Herramientas de Desarrollo

- **Control de Versiones:** Git + GitHub
- **IDE Recomendado:** Visual Studio Code / PhpStorm
- **API Testing:** Postman / Insomnia
- **DB Management:** pgAdmin / TablePlus

---

## 4. Instalación y Ejecución

> ⚠️ **ESTADO DEL PROYECTO:** Fase 1 en desarrollo activo. Actualmente puedes explorar la estructura del código y la documentación.

### Requisitos Previos

- **Docker:** 24.0+ y Docker Compose 2.20+
- **Node.js:** 20.x LTS (para desarrollo frontend local)
- **Composer:** 2.6+ (para desarrollo backend local)
- **Git:** 2.40+

**Opcional (solo para desarrollo sin Docker):**
- PHP 8.2+ con extensiones: pdo_pgsql, intl, opcache, apcu
- PostgreSQL 16+

### Pasos de Instalación

#### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/hablaia.git
cd hablaia
```

#### 2️⃣ Configurar Variables de Entorno

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

# OpenAI API (obtén tu clave en https://platform.openai.com)
OPENAI_API_KEY=sk-tu-clave-aqui

# Symfony
APP_ENV=dev
APP_SECRET=genera-un-secreto-aleatorio-aqui
```

**Edita `frontend/.env` y configura:**

```env
# URL del backend API
VITE_API_URL=http://localhost:8080

# OpenAI API (para desarrollo frontend independiente)
VITE_OPENAI_API_KEY=sk-tu-clave-aqui
```

#### 3️⃣ Levantar Servicios con Docker

```bash
docker-compose up -d
```

Esto iniciará:
- PostgreSQL (puerto 5432)
- Backend Symfony (puerto 8080)
- Frontend Vue (puerto 3000)
- PostHog Analytics (puerto 8081)
- Redis (puerto 6379)

#### 4️⃣ Instalar Dependencias Backend

```bash
cd backend
composer install
```

#### 5️⃣ Ejecutar Migraciones de Base de Datos

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

#### 6️⃣ Sincronizar Pictogramas desde ARASAAC

```bash
php bin/console app:sync-arasaac
```

Este comando descargará ~150 pictogramas básicos desde la API de ARASAAC y los almacenará en la base de datos.

#### 7️⃣ Instalar Dependencias Frontend

```bash
cd ../frontend
npm install
```

#### 8️⃣ Acceder a la Aplicación

- **Frontend (Aplicación):** http://localhost:3000
- **Backend API:** http://localhost:8080/api
- **PostHog Analytics:** http://localhost:8081
- **Documentación API:** http://localhost:8080/api/doc

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

# ⏳ Sincronizar pictogramas desde ARASAAC (pendiente)
php bin/console app:sync-arasaac

# ⏳ Formateo de código (pendiente)
composer format
```

#### Frontend (Vue + TypeScript)

```bash
# Servidor de desarrollo con hot-reload
npm run dev

# Build de producción
npm run build

# Preview del build de producción
npm run preview

# Ejecutar tests unitarios
npm run test

# Tests con reporte de cobertura
npm run test:coverage

# Tests en modo watch
npm run test:watch

# Linting con ESLint
npm run lint

# Auto-fix de problemas de linting
npm run lint:fix

# Formateo con Prettier
npm run format

# Tests de accesibilidad (Lighthouse)
npm run test:a11y
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

# Parar y eliminar volúmenes (⚠️ borra datos)
docker-compose down -v

# Reconstruir imágenes
docker-compose build --no-cache
```

### Troubleshooting

#### Error: "Port 5432 already in use"

PostgreSQL ya está corriendo en tu máquina local. Opciones:
1. Para el PostgreSQL local: `sudo systemctl stop postgresql`
2. Cambia el puerto en `docker-compose.yml`: `"5433:5432"`

#### Error: "OpenAI API key not found"

Asegúrate de haber configurado `OPENAI_API_KEY` en `backend/.env` y `frontend/.env`.

#### Error: "Permission denied" al ejecutar scripts

Dale permisos de ejecución:
```bash
chmod +x scripts/*.sh
```

#### Frontend no conecta con Backend

Verifica que `VITE_API_URL` en `frontend/.env` apunte a `http://localhost:8080`.

---

## 5. Estructuración

### Arquitectura General

```
hablaia/
├── backend/          # API REST Symfony 7
├── frontend/         # SPA Vue.js 3
├── docker/           # Configuración Docker
├── docs/             # Documentación
│   └── adrs/         # Architecture Decision Records
└── scripts/          # Scripts de utilidades
```

### Backend - Clean Architecture

```
backend/src/
├── Domain/           # Capa de Dominio (pura, sin dependencias)
│   ├── Shared/       # DomainException base
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
├── Application/      # Casos de Uso (⏳ pendiente)
├── Infrastructure/   # Implementaciones (⏳ pendiente)
└── Shared/           # Utils compartidos
```

### Frontend - Clean Architecture

```
frontend/src/
├── domain/           # Capa de Dominio
│   ├── entity/
│   ├── valueobject/
│   └── repository/   # Interfaces
├── application/      # Casos de Uso
│   ├── usecase/
│   └── service/
├── infrastructure/   # Implementaciones
│   ├── http/
│   ├── storage/
│   └── tts/
├── presentation/     # UI (Vue)
│   ├── components/
│   ├── views/
│   ├── composables/
│   └── router/
└── shared/           # Utils, Types
```

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
└── ...
```

---

## 6. Funcionalidades

> **Estado:** Fase 1 en desarrollo activo
> **Nota:** Esta sección se actualizará conforme se implementen las funcionalidades

### Objetivos Fase 1 (MVP) 🚧

| Funcionalidad | Estado | Descripción |
|---------------|--------|-------------|
| Grid de pictogramas | 🔲 Pendiente | Categorías: Acciones, Emociones, Personas, Objetos |
| Selección multi-pictograma | 🔲 Pendiente | Construir frases seleccionando pictogramas |
| Generación IA | 🔲 Pendiente | 3 variaciones humanizadas con LLM (OpenAI inicial) |
| Text-to-Speech | 🔲 Pendiente | Web Speech API (voz nativa del navegador) |
| Caché de frases | 🔲 Pendiente | PostgreSQL para reducir llamadas al LLM |
| Sincronización ARASAAC | 🔲 Pendiente | Descarga de pictogramas desde API |
| Accesibilidad WCAG 2.1 AA | 🔲 Pendiente | Navegación por teclado, ARIA labels, contraste |

### Objetivos Técnicos 🛠️

| Aspecto | Estado | Descripción |
|---------|--------|-------------|
| Clean Architecture | 🚧 En progreso | Domain ✅ → Application ⏳ → Infrastructure ⏳ |
| TDD | 🚧 En progreso | 61 tests Domain (100%), Application ⏳ |
| Excepciones de Dominio | ✅ Completado | `DomainException` base + excepciones semánticas por módulo |
| UUID Desacoplado | ✅ Completado | Domain valida (`Uuid`), Infrastructure genera (`UuidGeneratorInterface`) |
| Docker | ✅ Completado | Contenedores para todos los servicios |
| CI/CD | ✅ Completado | GitHub Actions + Husky (pre-commit + pre-push) |

**Leyenda:** 🔲 Pendiente | 🚧 En progreso | ✅ Completado

### Roadmap de Desarrollo 🚀

| Fase | Estado | Descripción |
|------|--------|-------------|
| **Fase 1** | 🚧 EN DESARROLLO | Core funcional (pictogramas + IA + TTS básico) |
| **Fase 2** | 📅 Planificada | Contexto temporal y mejoras UX |
| **Fase 3** | 📅 Planificada | Personalización y perfiles de usuario |
| **Fase 4** | 📅 Planificada | TTS Premium (ElevenLabs) |
| **Fase 5** | 📅 Planificada | Modo offline y PWA |
| **Fase 6** | 📅 Planificada | Voice Cloning del usuario |

**📖 Ver [Roadmap Completo](docs/ROADMAP.md)** para detalles de cada fase.

---

## 📚 Documentación Adicional

- **[Architecture Decision Records](docs/adrs/):** Decisiones de arquitectura documentadas
- **[API Documentation](http://localhost:8080/api/doc):** Especificación OpenAPI 3.0
- **[Testing Guide](docs/guides/testing-guide.md):** Estrategias de testing
- **[Deployment Guide](docs/guides/deployment-guide.md):** Despliegue en producción

---

## 📜 Licencia

**Todos los derechos reservados.**

Este proyecto es un **trabajo de fin de máster (TFM)** y proyecto de portfolio. El código fuente es propietario y se comparte de forma privada únicamente para:

- Evaluación académica (tutores/evaluadores TFM)
- Demostración de competencias técnicas (procesos de selección)

**NO está permitido** ningún uso, copia, modificación o distribución sin autorización escrita.

**Nota sobre ARASAAC:** Los pictogramas de ARASAAC están bajo licencia **Creative Commons BY-NC-SA 4.0**.

Ver [LICENSE](LICENSE) para más detalles.

---

## 🎯 Demo

Una versión demo de la aplicación está disponible en: **[Próximamente]**

Para acceso al código fuente, contactar mediante GitHub o LinkedIn.

---

## 📞 Contacto

- **GitHub:** [Perfil del autor](https://github.com/luismiglezmohino)
- **LinkedIn:** [Perfil profesional](https://linkedin.com/in/tu-usuario)
- **Documentación:** `/docs` en este repositorio

---

## 🙏 Agradecimientos

- **ARASAAC** (Gobierno de Aragón) por proporcionar pictogramas de calidad bajo licencia abierta
- **OpenAI** por la API que permite humanizar la comunicación
- **Comunidad SAAC** de terapeutas, familias y usuarios que inspiran este proyecto

---

<p align="center">
  <strong>Desarrollado con ❤️ para devolver la voz a quienes más lo necesitan</strong>
</p>
