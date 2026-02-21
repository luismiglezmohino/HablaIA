---
theme: neversink
title: "HablaIA — Comunicador SAAC con IA"
author: "Luis Miguel González-Mohino"
info: |
  Trabajo Fin de Máster — Máster de Desarrollo con IA
  BIG School
routerMode: hash
colorSchema: light
aspectRatio: "16/9"
fonts:
  sans: Inter
  mono: Fira Code
transition: slide-left
neversink_slug: "HablaIA — TFM 2026"
favicon: /favicon.svg
layout: cover
color: navy
---

# HablaIA

## Comunicador pictográfico con Inteligencia Artificial

<br>

<div class="grid grid-cols-2 gap-8">
<div>

**Luis Miguel González-Mohino**

Máster de Desarrollo con IA — BIG School

Octubre 2025 - Febrero 2026

</div>
<div>

- **Demo:** [https://damevozya.es](https://damevozya.es)
- **Repo:** [github.com/luismiglezmohino/HablaIA](https://github.com/luismiglezmohino/HablaIA)

</div>
</div>

<span class="text-sm opacity-60">Navegar: flechas del teclado ← → · click para avanzar · pulsa «o» para ver todas las slides</span>

---
layout: side-title
side: l
color: rose
titlewidth: is-4
align: rm-lm
---

:: title ::

# El problema

:: content ::

Las personas con **TEA, afasia, parálisis cerebral o ELA** dependen de comunicadores SAAC para expresarse.

<br>

<AdmonitionType type="warning" width="100%">

**Barreras de los comunicadores actuales:**
- **Precio elevado** — soluciones de software desde 250 EUR, hardware especializado desde 15.000 EUR
- **Atados a hardware específico** — requieren dispositivos concretos o sistemas operativos cerrados
- **Sin inteligencia** — concatenan palabras: "yo querer agua"
- **La gramática recae en el usuario** — personas que ya tienen dificultad comunicativa

</AdmonitionType>

---
layout: section
color: emerald
---

# Objetivo

Un **comunicador web** accesible desde cualquier dispositivo

que usa **IA generativa** para transformar pictogramas en **frases naturales completas**

<hr>

<span class="text-2xl">El usuario solo selecciona pictogramas. La IA se encarga de la gramática.</span>

---
layout: default
color: sky-light
---

# Por qué HablaIA es diferente

<div class="grid grid-cols-3 gap-3 w-full mt-4">
  <div class="bg-emerald-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">Web</div>
    <div class="text-sm mt-1">Cualquier dispositivo, sin hardware específico</div>
  </div>
  <div class="bg-violet-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">IA generativa</div>
    <div class="text-sm mt-1">Primer SAAC que humaniza frases con un LLM</div>
  </div>
  <div class="bg-amber-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">Sin barreras de entrada</div>
    <div class="text-sm mt-1">Sin licencia, sin hardware, cualquier navegador</div>
  </div>
  <div class="bg-rose-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">ARASAAC</div>
    <div class="text-sm mt-1">Pictogramas estándar clínico abiertos</div>
  </div>
  <div class="bg-sky-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">3 variaciones</div>
    <div class="text-sm mt-1">El usuario elige la frase que mejor le representa</div>
  </div>
  <div class="bg-teal-400 text-white rounded-lg p-3">
    <div class="text-lg font-bold">Accesibilidad nativa</div>
    <div class="text-sm mt-1">WCAG 2.2 AA, teclado, screen reader</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-6
color: amber-light
align: l-lt-lt
---

:: title ::

# Cómo se construyen frases hoy

:: left ::

### Input: 8 pictogramas

`[yo] [estar] [cansado] [querer] [dormir] [casa] [hoy] [mamá]`

<br>

**Comunicador tradicional:**

> "Mamá, estoy cansado, quiero dormir, casa, hoy"

<br>
<AdmonitionType type="caution" width="100%">

1 única frase, sin variaciones. El usuario no elige cómo expresarse.

</AdmonitionType>

:: right ::

### Con HablaIA (resultado real)

<StickyNote color="emerald" width="100%">

**3 variaciones generadas por IA:**

1. "Mamá, estoy cansado y quiero dormir en casa hoy"
2. "¡Qué cansado estoy, mamá! Hoy quiero descansar en casa"
3. "Mamá, me siento muy cansado. ¿Podemos ir a casa a dormir hoy?"

</StickyNote>
<br>

<AdmonitionType type="tip" width="100%">

Conjugación, artículos, preposiciones y 3 variaciones de tono: directa, expresiva y cortés

</AdmonitionType>

---
layout: top-title
color: sky-light
align: l
---

:: title ::

# Flujo de uso

:: content ::

<div class="flex items-center justify-center gap-2 mt-8">
  <div class="bg-amber-400 text-white rounded-lg px-4 py-3 text-center font-bold">Categoría</div>
  <div class="text-2xl">→</div>
  <div class="bg-emerald-400 text-white rounded-lg px-4 py-3 text-center font-bold">Pictogramas</div>
  <div class="text-2xl">→</div>
  <div class="bg-sky-400 text-white rounded-lg px-4 py-3 text-center font-bold">IA Generativa</div>
  <div class="text-2xl">→</div>
  <div class="bg-violet-400 text-white rounded-lg px-4 py-3 text-center font-bold">3 Frases</div>
  <div class="text-2xl">→</div>
  <div class="bg-rose-400 text-white rounded-lg px-4 py-3 text-center font-bold">Voz</div>
</div>

<br>

**3 variaciones** por petición — el usuario elige la que mejor expresa lo que quiere decir

<video src="/videos/compressed/llm.mp4" controls class="w-full rounded-lg mt-2" style="max-height: 260px" />

---
layout: side-title
side: l
color: amber
titlewidth: is-3
align: rm-lm
---

:: title ::

# Pictogramas

:: content ::

**194 pictogramas base** · **11 categorías** · **Modified Fitzgerald Key**

El vocabulario núcleo (core vocabulary) cubre **~80% de la comunicación diaria** según estudios en SAAC.

<div class="grid grid-cols-2 gap-8">
<div class="ns-c-tight text-xs">

| Categoría | Color | Ejemplo |
|---|---|---|
| Personas | Amarillo | yo, tú, mamá |
| Verbos | Verde | querer, comer |
| Alimentación | Naranja | agua, pan |

*3 de 11 categorías mostradas*

</div>
<div class="text-sm">

- **API oficial** ARASAAC — Centro Aragonés, Gobierno de Aragón (CC BY-NC-SA 4.0)
- **Estándar de referencia** en España y Latinoamérica
- **Buscador** — si un pictograma no está en la base, lo busca en la API de ARASAAC (+30.000) y lo muestra al instante


</div>
</div>


---
layout: default
color: sky-light
---

# El proyecto en números

<div class="grid grid-cols-3 gap-4 w-full mt-4">
  <div class="bg-amber-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">684</div>
    <div class="text-sm mt-1">tests</div>
  </div>
  <div class="bg-sky-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">15</div>
    <div class="text-sm mt-1">ADRs</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">87+</div>
    <div class="text-sm mt-1">Pull Requests</div>
  </div>
  <div class="bg-rose-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">194</div>
    <div class="text-sm mt-1">pictogramas</div>
  </div>
  <div class="bg-violet-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">100</div>
    <div class="text-sm mt-1">Lighthouse A11y</div>
  </div>
  <div class="bg-teal-400 text-white rounded-lg p-4 text-center">
    <div class="text-4xl font-bold">95/99</div>
    <div class="text-sm mt-1">Performance M/D</div>
  </div>
</div>

---
layout: default
color: teal-light
---

# Stack tecnológico

<div class="grid grid-cols-2 gap-4 w-full mt-4">
  <div class="bg-sky-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Backend</div>
    <div class="text-sm mt-1"><b>Symfony 7</b> + PHP 8.4</div>
    <div class="text-sm"><b>Cycle ORM</b> + PostgreSQL 16</div>
    <div class="text-sm"><b>PestPHP</b> + PHPStan level 8</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Frontend</div>
    <div class="text-sm mt-1"><b>Vue 3</b> + TypeScript strict</div>
    <div class="text-sm"><b>Pinia</b> + Tailwind CSS</div>
    <div class="text-sm"><b>shadcn-vue</b> + Radix Vue</div>
    <div class="text-sm"><b>Vitest</b> + Testing Library</div>
  </div>
  <div class="bg-amber-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">IA</div>
    <div class="text-sm mt-1"><b>Groq</b> GPT-OSS 120B (prod)</div>
    <div class="text-sm"><b>Groq</b> Llama 3.3 70B (alternativa)</div>
    <div class="text-sm">Cache <b>SHA256</b> en PostgreSQL</div>
  </div>
  <div class="bg-rose-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Infraestructura</div>
    <div class="text-sm mt-1"><b>Docker</b> + Nginx + Hetzner CX33</div>
    <div class="text-sm"><b>GitHub Actions</b> (3 workflows CI + CD)</div>
    <div class="text-sm"><b>Husky</b> hooks (pre-commit + pre-push)</div>
    <div class="text-sm"><b>Sentry</b> Cloud (frontend + backend)</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-5
color: navy
align: l-lt-lt
---

:: title ::

# Arquitectura — Clean Architecture

:: left ::

Misma estructura en **backend y frontend**:

```
Domain/
  ↑  Entidades, Value Objects
  ↑  Interfaces de repositorio
  ↑  (sin dependencias de framework)

Application/
  ↑  Use Cases, DTOs, Services

Infrastructure/
     Persistencia, HTTP, APIs
```

:: right ::

<Admonition title="¿Por qué?" color="sky-light" icon="mdi-help-circle">

- El dominio **no depende** de Symfony ni de Vue
- Se puede migrar sin reescribir lógica
- Tests de dominio sin montar frameworks
- Misma estructura mental en ambos stacks

</Admonition>

<br>

<Admonition title="Evidencia" color="emerald-light" icon="mdi-check-circle">

- 15 ADRs documentando cada decisión
- Separación mantenida por convención y estructura de carpetas

</Admonition>

---
layout: top-title-two-cols
columns: is-6
color: sky-light
align: l-lt-lt
---

:: title ::

# IA — Generación de frases

:: left ::

### Arquitectura multi-provider

```
PhraseGeneratorInterface
  ├── RealOpenAIPhraseGenerator  (Groq)
  ├── GeminiPhraseGenerator
  └── FakePhraseGenerator
```

Cambiar provider = **1 variable de entorno**

<Admonition title="Prompt" color="violet-light" icon="mdi-robot">

20 ejemplos + reglas semánticas (no repetir verbos, variar estructura, máximo 15 palabras)

</Admonition>

:: right ::

### Cache SHA256

```
Pictogramas → SHA256 hash
                ↓
         ¿Existe en BD?
          /          \
        Sí            No
        ↓              ↓
  Cache hit       Llamada LLM
   (~86ms)            ↓
                Guardar en BD
```

<AdmonitionType type="tip">

~40% del uso SAAC es repetición → el cache tiene impacto real

</AdmonitionType>

---
layout: top-title-two-cols
columns: is-6
color: rose-light
align: l-lt-lt
---

:: title ::

# IA — Decisión de latencia

:: left ::

La latencia es crítica para un usuario SAAC. Cada segundo de espera genera ansiedad.

| Escenario | Latencia |
|---|---|
| LLM sin cache | ~1s (máx. 2.6s) |
| **Cache hit (SHA256)** | **~86ms** |

<br>

<AdmonitionType type="important">

**Las frases más usadas ya están en cache.** El usuario recibe respuesta instantánea sin esperar al LLM.

</AdmonitionType>

:: right ::

### Cache hit — respuesta instantánea

<video src="/videos/compressed/cache.mp4" controls class="w-full rounded-lg" style="max-height: 280px" />

<br>

<Admonition title="Multi-provider" color="sky-light" icon="mdi-swap-horizontal">

Groq GPT-OSS 120B (prod) o Llama 70B (alternativa). Cambiar = 1 variable de entorno.

</Admonition>

---
layout: side-title
side: l
color: emerald
titlewidth: is-4
align: rm-lm
---

:: title ::

# Accesibilidad
## WCAG 2.2 AA

:: content ::

<div class="text-sm">

| Criterio | Implementación |
|---|---|
| **Teclado** | 1-0/? categorías, b buscar, g generar, x limpiar, flechas grid |
| **Screen reader** | Dual aria-live (polite + assertive) |
| **Target size** | Mínimo 44x44px (excepción tablet landscape documentada) |
| **Contraste** | Mínimo 4.5:1 texto, 3:1 UI |
| **Focus visible** | Ring en todos los elementos interactivos |

</div>

<br>

<Admonition title="Lighthouse Accessibility: 100 / 100" color="emerald-light" icon="mdi-check-decagram">

Mobile y desktop. Auditoría asistida por IA criterio por criterio según perfiles SAAC.

</Admonition>

---
layout: top-title-two-cols
columns: is-6
color: amber-light
align: l-lt-lt
---

:: title ::

# Responsive — 3 configuraciones

:: left ::

<div class="text-sm">

| Dispositivo | Layout |
|---|---|
| **Mobile** | Grid compacto, frase fija abajo |
| **Tablet** | Categorías con texto, chips visibles |
| **Desktop** | Footer fijo con atajos de teclado |

</div>

<AdmonitionType type="info">

**Mobile landscape bloqueado** (< 500px altura) — se muestra mensaje informativo

</AdmonitionType>

Diseño adaptativo con cuatro interfaces.

:: right ::

<div class="text-center">
  <img src="/screenshots/responsive/movil.png" class="rounded-lg mx-auto" style="max-height: 360px" />
  <div class="text-sm mt-1 font-bold">Mobile</div>
</div>

---
layout: top-title
color: amber-light
align: l
---

:: title ::

# Responsive — Desktop y Tablet

:: content ::

<div class="grid grid-cols-2 gap-6">
  <div class="text-center">
    <img src="/screenshots/responsive/pc.png" class="rounded-lg mx-auto" style="max-height: 340px" />
    <div class="text-sm mt-1 font-bold">Desktop</div>
  </div>
  <div class="text-center">
    <img src="/screenshots/responsive/tablet.png" class="rounded-lg mx-auto" style="max-height: 340px" />
    <div class="text-sm mt-1 font-bold">Tablet</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-6
color: teal-light
align: l-lt-lt
---

:: title ::

# Testing — 684 tests, TDD

:: left ::

| Nivel | Herramienta | Tests |
|---|---|---|
| **Backend** | PestPHP + PHPStan level 8 | 400 |
| **Frontend** | Vitest + Testing Library | 263 |
| **E2E** | Playwright — 6 suites de test, 5 tamaños de pantalla | 21 |
| **Análisis estático** | PHPStan level 8 (máximo) + ESLint + vue-tsc strict | — |

<br>

### Metodología TDD

```
1. RED    — Test que falla
2. GREEN  — Código mínimo
3. REFACTOR — Mejorar
```

:: right ::

<Admonition title="Cobertura" color="teal-light" icon="mdi-shield-check">

- **100%** Domain (entidades, value objects)
- **80%** Application (stores, use cases)
- **E2E** flujos críticos en 5 tamaños de pantalla

</Admonition>

<br>

<Admonition title="Hooks automáticos" color="amber-light" icon="mdi-hook">

**Pre-commit:** eslint + vue-tsc + vitest + phpstan + pest

**Pre-push:** todos los tests + E2E + npm audit

</Admonition>

---
layout: top-title
color: rose-light
align: l
---

:: title ::

# Seguridad — OWASP Top 10

:: content ::

Auditoría completa basada en **OWASP Top 10** con **0 hallazgos pendientes**.

<br>

<div class="grid grid-cols-2 gap-4">
  <div class="bg-rose-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Protección de inputs</div>
    <div class="text-sm mt-1">SQL Injection, XSS, Prompt Injection — todos los inputs se sanitizan y validan en servidor</div>
  </div>
  <div class="bg-sky-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Límites de uso</div>
    <div class="text-sm mt-1">Rate limiting en rutas críticas (LLM e imágenes), solo llamadas externas a ARASAAC</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Configuración segura</div>
    <div class="text-sm mt-1">Cabeceras de seguridad, 0 secretos en código, contenedores sin privilegios</div>
  </div>
  <div class="bg-violet-400 text-white rounded-lg p-4">
    <div class="text-lg font-bold">Observabilidad</div>
    <div class="text-sm mt-1">Sentry captura cada error y cada intento de acceso no autorizado</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-6
color: sky-light
align: l-lt-lt
---

:: title ::

# Performance — Lighthouse producción

:: left ::

### Lighthouse (21 feb 2026)

| Categoría | Mobile | Desktop |
|---|---|---|
| **Performance** | **98** | **100** |
| **Accessibility** | **100** | **100** |
| **Best Practices** | **100** | **100** |

:: right ::

### ¿Por qué importa?

- La app **carga en menos de 1 segundo** en desktop
- El bundle inicial pesa **92 KB** — carga rápida incluso con conexión lenta
- **Sin saltos visuales** al cargar — la interfaz no se mueve

<br>

<Admonition title="Rendimiento = accesibilidad" color="emerald-light" icon="mdi-speedometer">

Para un usuario SAAC, cada segundo de espera es frustración. La velocidad no es un lujo, es un requisito.

</Admonition>

---
layout: top-title
color: violet-light
align: l
---

:: title ::

# CI/CD — Deploy automático

:: content ::

**CI** — 3 workflows en cada PR:

<div class="text-sm">

| Workflow | Qué ejecuta |
|---|---|
| **backend-ci** | PHPStan level 8 + PestPHP + `composer audit` |
| **frontend-ci** | ESLint + vue-tsc strict + Vitest + `npm audit` + Lighthouse |
| **commitlint** | Conventional Commits en cada commit y en PRs |

</div>

Cada línea de código pasa por **análisis estático, tests y auditoría de dependencias** antes de llegar a producción.

**CD** — Deploy automático a Hetzner al mergear a main:

<div class="flex items-center justify-center gap-2 mt-2">
  <div class="bg-violet-400 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">Push a main</div>
  <div class="text-xl">→</div>
  <div class="bg-sky-400 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">GitHub Actions</div>
  <div class="text-xl">→</div>
  <div class="bg-amber-400 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">SSH Hetzner</div>
  <div class="text-xl">→</div>
  <div class="bg-teal-400 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">Docker build</div>
  <div class="text-xl">→</div>
  <div class="bg-emerald-400 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">Migrations</div>
  <div class="text-xl">→</div>
  <div class="bg-emerald-500 text-white rounded-lg px-3 py-2 text-center text-sm font-bold">Health check</div>
</div>

---
layout: top-title-two-cols
columns: is-6
color: rose-light
align: l-lt-lt
---

:: title ::

# Sentry — Observabilidad

:: left ::

Sentry Cloud integrado en **frontend** y **backend**.

<img src="/screenshots/sentry/eventosSentry.png" class="w-full rounded-lg mt-2" />

<div class="text-sm mt-2">

**288 eventos, 0 usuarios reales** — escaneos automatizados (curl 91%, client_os vacío 95%).

<AdmonitionType type="important">

Sentry capturó **288 intentos de escaneo** — Nginx + backend devolvieron 404 en todos. Ninguna ruta sensible fue expuesta.

</AdmonitionType>

</div>

:: right ::

<img src="/screenshots/sentry/atackurlsentry.png" class="w-full rounded-lg" style="max-height: 400px; object-fit: cover; object-position: top" />

---
layout: top-title-two-cols
columns: is-6
color: teal-light
align: l-lt-lt
---

:: title ::

# Auditorías — 4 revisiones completas

:: left ::

**4 auditorías completas:**

- Seguridad (OWASP Top 10)
- Accesibilidad (WCAG 2.2 AA)
- Performance (Lighthouse + backend)
- QA (cobertura + procesos)

:: right ::

<div class="grid grid-cols-1 gap-3">
  <div class="bg-sky-400 text-white rounded-lg p-4 text-center">
    <div class="text-xl font-bold">1.ª ronda → fixes → 2.ª ronda</div>
    <div class="text-sm mt-1">Ciclo completo de auditoría y corrección</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-4 text-center">
    <div class="text-3xl font-bold">0 pendientes</div>
    <div class="text-sm mt-1">Todo corregido o justificado en la segunda ronda</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-7
color: violet-light
align: l-lt-lt
---

:: title ::

# Roadmap — 7 fases

:: left ::

<div class="text-xs">

| Fase | Contenido | Estado |
|---|---|---|
| **1** | MVP comunicador público | ✅ **Completada** |
| **2** | Emergencia + contexto temporal + predicción | Próxima |
| **3** | Autenticación (logopeda + SAAC + familiar) | Planificada |
| **4** | Frases frecuentes + personalización TTS | Planificada |
| **5** | Gestión de pictogramas y categorías | Planificada |
| **6** | Modo terapeuta (dashboard) | Planificada |
| **7** | Visualización, exportación, app instalable | Planificada |

</div>

:: right ::

<Admonition title="Ideas en exploración" color="sky-light" icon="mdi-lightbulb-outline">

PWA offline · TTS premium · Voice cloning · LLM self-hosted · Eye tracking

</Admonition>

<br>

*Las fases y prioridades pueden variar según necesidades de los usuarios y evolución del proyecto.*

---
layout: top-title-two-cols
columns: is-6
color: navy
align: l-lt-lt
---

:: title ::

# Antes vs Después

:: left ::

### Sin HablaIA

<div class="grid grid-cols-1 gap-3">
  <div class="bg-rose-400 text-white rounded-lg p-3">
    <div class="font-bold">"yo querer agua"</div>
    <div class="text-sm">El usuario conjuga y ordena</div>
  </div>
  <div class="bg-rose-400 text-white rounded-lg p-3">
    <div class="font-bold">Requiere hardware o dispositivo específico</div>
    <div class="text-sm">Licencias de software desde 250 EUR</div>
  </div>
  <div class="bg-rose-400 text-white rounded-lg p-3">
    <div class="font-bold">Una única frase posible</div>
    <div class="text-sm">Sin variaciones ni contexto</div>
  </div>
</div>

:: right ::

### Con HablaIA

<div class="grid grid-cols-1 gap-3">
  <div class="bg-emerald-400 text-white rounded-lg p-3">
    <div class="font-bold">"Me apetece un vaso de agua"</div>
    <div class="text-sm">La IA se encarga de la gramática</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-3">
    <div class="font-bold">Cualquier navegador</div>
    <div class="text-sm">Móvil, tablet o desktop</div>
  </div>
  <div class="bg-emerald-400 text-white rounded-lg p-3">
    <div class="font-bold">3 variaciones para elegir</div>
    <div class="text-sm">El usuario elige cómo quiere expresarse</div>
  </div>
</div>

---
layout: top-title-two-cols
columns: is-6
color: sky-light
align: l-lt-lt
---

:: title ::

# Reflexión — Qué aprendí

:: left ::

<div class="text-sm">

### Del código

- **Proyecto completo en solitario** — diseño, frontend, backend, IA, accesibilidad, deploy y observabilidad
- **IA aplicada** — prompt engineering, cache, latencia, multi-provider
- **TDD real** — 684 tests se ejecutan antes de cada commit
- **Clean Architecture** — separar capas obliga a pensar antes de programar
- **Accesibilidad primero** — diseñarla desde el inicio es más fácil que adaptarla después
- **Calidad no negociable** — 4 auditorías, 0 hallazgos pendientes
- **Desplegar no es trivial** — el salto de local a producción es donde más se aprende

</div>

:: right ::

### Valor diferencial

<Admonition title="Sistema de 12 agentes + skills con IA" color="violet-light" icon="mdi-robot-outline">

Un sistema de desarrollo completo: 12 agentes especializados (architect, TDD developer, security auditor, QA, UX...) con **quality gates** y **restricciones fatales**.

No es solo el código — es un **proceso de desarrollo reutilizable** que se publica junto al proyecto.

</Admonition>

<br>

<AdmonitionType type="important">

Si esta app falla, una persona se queda sin poder comunicarse. No es un e-commerce donde un bug es un inconveniente — aquí un bug es **silenciar a alguien**.

</AdmonitionType>

---
layout: section
color: emerald
---

# Conclusiones

**MVP funcional** desplegado en producción

<hr>

- **684 tests** (TDD) — la calidad no es negociable
- **WCAG 2.2 AA** — Lighthouse Accessibility 100/100
- **IA generativa** — 3 variaciones naturales desde pictogramas
- **Clean Architecture** — preparada para escalar
- **0 hallazgos pendientes** en 4 auditorías
- **12 agentes + skills con IA** — reutilizables en cualquier proyecto

---
layout: cover
color: navy
---

# Gracias

**HablaIA** — Comunicador pictográfico con IA

Luis Miguel González-Mohino · Febrero 2026

<br>

Demo: [https://damevozya.es](https://damevozya.es)

Repo: [github.com/luismiglezmohino/HablaIA](https://github.com/luismiglezmohino/HablaIA)
