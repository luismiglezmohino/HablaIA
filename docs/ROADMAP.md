# HablaIA - Roadmap de Desarrollo

> Plan de evolución del proyecto en 7 fases incrementales

## Estado General del Proyecto

| Fase | Estado | Descripción |
|------|--------|-------------|
| **Fase 1** | ✅ COMPLETADA | Core funcional (pictogramas + IA + TTS básico) |
| **Fase 2** | 📅 Planificada | Mejoras de comunicación y emergencia |
| **Fase 3** | 📅 Planificada | Autenticación y multiusuario |
| **Fase 4** | 📅 Planificada | Comunicación personalizada |
| **Fase 5** | 📅 Planificada | Gestión de pictogramas y categorías |
| **Fase 6** | 📅 Planificada | Modo terapeuta |
| **Fase 7** | 📅 Planificada | Visualización, compartir y exportación |
| **Futuro** | 💡 Exploración | PWA y modo offline, TTS Premium, Voice Cloning, PostHog, métodos de entrada alternativos |

---

## Fase 1 - Core Funcional (✅ COMPLETADA)

**Objetivo:** MVP funcional con las capacidades básicas del comunicador.

### Backend (✅ Completado)
- ✅ API REST con Symfony 7 + Cycle ORM + PostgreSQL
- ✅ Grid de pictogramas por categorías (11 categorías SAAC, colores Fitzgerald Key)
- ✅ Integración LLM multi-proveedor (`PHRASE_PROVIDER`: openai, gemini, fake)
- ✅ Prompt tuning: 20 ejemplos few-shot, reglas semánticas, auto-validación logopeda
- ✅ Validación con 126 pruebas y 3 modelos (Gemini, Llama 70B, GPT-OSS 120B) → Groq GPT-OSS 120B como principal (ADR-015)
- ✅ Caché de frases en PostgreSQL (SHA256 hash, solo frases generadas por IA)
- ✅ Sincronización automática con ARASAAC API (194 pictogramas core vocabulary)
- ✅ Health checks (`/api/health`)
- ✅ Rate limiting configurable en generación de frases (per-minute + daily por IP)
- ✅ Tests: 400 unitarios (PestPHP), PHPStan level 8

### Frontend (✅ Completado)
- ✅ Clean Architecture frontend (Domain/Application/Infrastructure)
- ✅ Domain entities: Category, Pictogram, PhraseResponse + repository interfaces
- ✅ Zod schemas para validación de API responses (Zero Trust)
- ✅ ApiClient HTTP + repositorios (HttpCategoryRepository, HttpPictogramRepository, HttpPhraseRepository)
- ✅ Pinia registrado, shadcn-vue configurado (Radix Vue, Lucide Vue)
- ✅ Tests: 263 unitarios (Vitest) + 21 E2E (Playwright) = 284 frontend
- ✅ Pinia stores: useCategoryStore + usePictogramStore + usePhraseStore
- ✅ CategoryBar con Fitzgerald Key borders, Lucide icons, keyboard nav
- ✅ PictogramCard + PictogramGrid con Fitzgerald Key border-top, responsive grid
- ✅ PhraseBar: selección pictogramas (max 10), generación frases vía API, variaciones
- ✅ App shell: header, skip link, aria-live region
- ✅ Conexión API: fetchCategories + fetchByCategory + generatePhrase
- ✅ WCAG 2.2 AA: 44x44px touch targets, focus rings, aria-labels, role="tablist"
- ✅ Text-to-Speech con Web Speech API (TTSProvider interface, WebSpeechTTS, useTTS composable, SpeakButton)
- ✅ SearchBar con debounce y búsqueda insensible a acentos: local DB → fallback ARASAAC API (descarga y persiste pictogramas)
- ✅ Error handling UI: inline feedback (ADR-010), mensajes user-friendly en español, feedback visual en botones (loading/error/retry)
- ✅ Rediseño visual moderno (ADR-011): paleta Indigo, fuente Inter, sombras, motion-safe, Badge/Skeleton shadcn-vue, WCAG 2.2
- ✅ Responsive tres configuraciones: móvil, tablet portrait, tablet landscape (media query custom con clases utilitarias independientes)
- ✅ Tablet landscape compacto: búsqueda + categorías en header, PhraseBar inline con scroll horizontal, protección landscape móvil
- ✅ CD pipeline: GitHub Actions SSH deploy a Hetzner CX33, Docker Compose producción (PHP-FPM + nginx + PostgreSQL), health check con rollback automático
- ✅ E2E tests con Playwright (21 tests): app load, pictogram flow, search, phrase limits, error handling, responsive (5 viewports), video on failure

**Entregables técnicos:**
- ✅ Clean Architecture backend (Domain/Application/Infrastructure)
- ✅ API REST funcional (Symfony 7)
- ✅ SPA responsiva (Vue 3 + TypeScript) - data layer + categories + pictograms + phrase flow + TTS + search + error feedback + diseño visual moderno + responsive 3 configuraciones
- ✅ Docker Compose con todos los servicios
- ✅ CI/CD con GitHub Actions
- ✅ Documentación completa (ADRs, guías de desarrollo)

---

## Fase 2 - Mejoras de Comunicación y Emergencia (📅 FUTURO)

**Objetivo:** Mejorar la comunicación del usuario SAAC sin depender de autenticación ni perfiles.

**Funcionalidades planificadas:**
- 🔜 Contexto temporal inteligente:
  - Hora del día: "Buenos días" (mañana), "Buenas tardes" (tarde), "Buenas noches" (noche)
  - Día de la semana: "Feliz fin de semana" (sábado/domingo), "Buen inicio de semana" (lunes)
- 🔜 Predicción de pictogramas:
  - Sugerir pictogramas probables según el contexto de selección actual
  - Heurísticas gramaticales SAAC (Sujeto-Verbo-Objeto) desde el primer uso
- 🔜 Categoría de emergencia:
  - Categoría especial con frases urgentes pregeneradas ("Me duele", "Necesito ayuda", "Necesito ir al baño")
  - Acceso rápido con un toque (sin buscar pictogramas individualmente)
  - Frases precargadas en caché para acceso inmediato

**Impacto esperado:** Comunicación más rápida y natural desde el primer uso. Acceso inmediato a frases de emergencia para seguridad del paciente. No requiere autenticación.

---

## Fase 3 - Autenticación y Multiusuario (📅 FUTURO)

**Objetivo:** Establecer el sistema de usuarios como base para toda la personalización posterior.

**¿Por qué ahora?** Sin usuarios identificados, el historial de frases, favoritos, perfiles y modo terapeuta no pueden funcionar por usuario. En tablets compartidas (centros de terapia, colegios), sin autenticación todos los datos se mezclan. Es el prerequisito técnico para que las fases siguientes sean per-user desde el inicio, evitando migraciones de datos posteriores.

**Funcionalidades planificadas:**
- 🔜 Autenticación de dos niveles:
  - Logopeda/terapeuta: login real (email + contraseña), gestiona perfiles y configuración
  - Paciente SAAC: contraseña por pictogramas (seleccionar 3-4 pictogramas en orden, ~7M combinaciones). Más seguro que PIN y accesible para usuarios no verbales
  - El logopeda puede desactivar la contraseña por pictogramas para pacientes con bajo nivel cognitivo (decisión documentada)
  - Bloqueo tras intentos fallidos (logopeda desbloquea)
- 🔜 Dos paneles de la aplicación:
  - Panel general: selector de perfiles (nombres y avatares, sin datos personales visibles)
  - Panel personalizado: la app con los datos del usuario (historial, pictogramas descargados, preferencias)
- 🔜 Soporte multiusuario en tablet:
  - Cambio rápido de usuario desde el panel personalizado al selector de perfiles
  - Pensado para tablets compartidas en centros de terapia y colegios
  - El logopeda crea y gestiona perfiles de pacientes desde un panel de administración
  - Investigar rol de familiar/cuidador que pueda crear y gestionar perfiles sin depender de un logopeda (uso doméstico)
- 🔜 Asociación de datos por usuario:
  - Pictogramas descargados vinculados al usuario que los buscó
  - Tracking de uso de frases cacheadas por usuario (la caché global se mantiene)
  - Preferencias básicas por usuario (configuración TTS, etc.)
- 🔜 Seguridad de datos de salud:
  - Datos de comunicación son datos sensibles (GDPR)
  - Cada usuario solo accede a sus propios datos
  - Sesión del logopeda requiere re-autenticación para modo terapeuta
  - Gestión de sesión adaptada al contexto SAAC (equilibrio entre seguridad y disponibilidad de comunicación)

**Impacto esperado:** Base técnica sólida que habilita personalización real en todas las fases siguientes. Protección de datos de salud en dispositivos compartidos.

**Principio:** El comunicador básico (seleccionar pictogramas, generar frase, TTS) funciona sin autenticación. Los perfiles habilitan funciones personalizadas (historial, favoritos, predicción) pero no son requisito para comunicar. Un usuario SAAC siempre puede usar la app sin depender de un logopeda ni de un perfil configurado.

**Nota:** Esta fase requiere investigación adicional. La gestión del login, los roles (logopeda, familiar/cuidador, paciente), la política de sesiones y la protección GDPR de datos de salud son aspectos que deben diseñarse con cuidado. Existe una tensión entre facilidad de uso para el usuario SAAC (que puede tener dificultades motoras y cognitivas) y el cumplimiento GDPR (datos de comunicación son datos de salud sensibles). El diseño final debe equilibrar ambos requisitos.

---

## Fase 4 - Comunicación Personalizada (📅 FUTURO)

**Objetivo:** Acelerar la comunicación aprovechando los datos de uso de cada usuario.

**Funcionalidades planificadas:**
- 🔜 Historial de frases frecuentes:
  - Top 10 frases más usadas accesibles con 1 click
  - Persistencia en base de datos por usuario
- 🔜 Personalización TTS básica:
  - Velocidad de voz ajustable (slider)
  - Persistencia de la preferencia por usuario
- 🔜 Predicción de pictogramas por frecuencia:
  - Refinamiento de la predicción (Fase 2) con datos de uso real del usuario
  - Sugerencias cada vez más precisas conforme se acumulan datos

**Impacto esperado:** Reducir tiempo de comunicación mediante acceso rápido a frases frecuentes y predicción adaptada al usuario.

---

## Fase 5 - Gestión de Pictogramas y Categorías (📅 FUTURO)

**Objetivo:** Permitir que cada usuario adapte el vocabulario a sus necesidades específicas.

**Funcionalidades planificadas:**
- 🔜 Gestión de pictogramas:
  - Clasificación automática de pictogramas importados (por IA o tags ARASAAC)
  - Añadir pictogramas propios (fotos de familia, objetos del entorno)
  - Marcar pictogramas favoritos (categoría "Mis Favoritos")
- 🔜 Personalización de categorías:
  - Crear categorías propias (ej: "Escuela", "Familia", "Hobbies")
  - Reorganizar pictogramas entre categorías
  - Generación automática de categorías según uso
- 🔜 Tableros de comunicación:
  - Biblioteca de categorías sugeridas que el logopeda puede activar por paciente
  - Ejemplos: médico, colegio, restaurante, casa, festividades, deportes, emociones avanzadas
  - Secuencias de pictogramas frecuentes preparadas por contexto (ej: en "médico", [yo, dolor, cabeza] listo para generar frase sin buscar pictograma a pictograma)

**Impacto esperado:** Vocabulario adaptado a cada usuario (TEA prefiere consistencia, afasia necesita simplicidad, ELA requiere eficiencia).

---

## Fase 6 - Modo Terapeuta (📅 FUTURO)

**Objetivo:** Herramientas para logopedas en seguimiento de terapia.

**Funcionalidades planificadas:**
- 🔜 Dashboard de uso del paciente:
  - Pictogramas más usados, frases generadas, frecuencia de uso
  - Consultas sobre datos existentes en base de datos (sin infraestructura adicional)
- 🔜 Gestión de frases:
  - Aprobar, rechazar o sobrescribir variaciones generadas por el LLM para cada paciente

**Impacto esperado:** Herramienta útil para logopedas en seguimiento de terapia y control de calidad de las frases generadas.

---

## Fase 7 - Visualización, Compartir y Exportación (📅 FUTURO)

**Objetivo:** Personalización visual avanzada e intercambio de configuraciones.

**Funcionalidades planificadas:**
- 🔜 Configuración avanzada por usuario:
  - Categorías visibles por perfil (ocultar las que no usa)
  - Orden personalizado de categorías
- 🔜 Modos de visualización:
  - Modo noche / alto contraste
  - Tamaño de pictogramas ajustable (pequeño, mediano, grande)
  - Densidad de grid (2x2, 3x3, 4x4)
- 🔜 Compartir tableros y configuraciones:
  - El logopeda prepara tableros y categorías personalizadas para el paciente
  - Envío directo al dispositivo del paciente
- 🔜 Exportación de datos:
  - Exportar configuración y favoritos (JSON)
  - Importar en otro dispositivo
- 🔜 App instalable:
  - Web App Manifest para instalar en pantalla de inicio (sin necesidad de abrir navegador ni escribir URL)
  - Requiere conexión a internet para funcionar

**Impacto esperado:** Adaptación visual a necesidades individuales. Flujo de trabajo logopeda-paciente simplificado.

---

## Trabajo Futuro (💡 EXPLORACIÓN)

Ideas y funcionalidades que podrían explorarse a largo plazo, sin fase asignada:

- **PWA y modo offline (en valoración):** App instalable (icono, pantalla completa), Service Worker para assets, IndexedDB con datos del usuario, sincronización offline. Problemática: sin internet el LLM no humaniza combinaciones nuevas (solo concatenación de etiquetas), y sin IndexedDB los pictogramas no se muestran offline. La mayoría de dispositivos tienen conexión constante, lo que reduce el valor frente a la complejidad. En valoración: decidir si el beneficio justifica la implementación o si basta con que la app funcione siempre con conexión.
- **TTS Premium (ElevenLabs):** Voces en español de mayor calidad, selección de voz (masculina, femenina, infantil), caché de audio MP3. Valorado por usuarios con afasia y ELA (adultos que rechazan voces robotizadas), pero Web Speech API en 2026 ya ofrece calidad aceptable. Modelo freemium (Web Speech gratis, ElevenLabs premium).
- **Voice Cloning:** Clonación de voz del usuario (ElevenLabs). Caso de uso nicho: personas con ELA que pierden progresivamente la voz y desean preservar su identidad vocal. No aporta valor a la mayoría de usuarios SAAC (TEA, afasia, parálisis cerebral). Requiere ElevenLabs Professional Plan y consideraciones GDPR.
- **Analytics avanzado (PostHog):** Event tracking, heatmaps y session recordings para optimizar UX a escala. Requiere infraestructura adicional (self-hosted) o coste (cloud). Los datos básicos de uso ya se recogen en la base de datos.
- **Métodos de entrada alternativos:** Eye tracking, switch scanning y otros dispositivos de acceso para usuarios con movilidad muy reducida.
- **LLM en navegador:** Modelos ligeros ejecutados localmente (WebGPU/WebAssembly) para humanización offline. Actualmente la calidad en español de los modelos pequeños es insuficiente para SAAC, pero la tecnología evoluciona rápidamente.
- **LLM self-hosted:** Modelos de lenguaje ejecutados en servidor propio (Ollama, vLLM) para eliminar dependencia de APIs externas (Groq, Gemini, OpenAI). Reduciría costes recurrentes y latencia, pero requiere infraestructura con GPU y mantenimiento del modelo. Investigar viabilidad según volumen de uso y coste de GPU vs coste de API.

---

## Notas

- Algunas fases pueden sufrir modificaciones según feedback de usuarios, logopedas o por decisión del desarrollador
- Las métricas se actualizarán con datos reales conforme se complete cada fase
- El orden de las fases puede alterarse según prioridades del proyecto

**Última actualización:** 14 de febrero de 2026
