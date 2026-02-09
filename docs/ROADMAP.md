# HablaIA - Roadmap de Desarrollo

> Plan de evolución del proyecto en 6 fases incrementales

## Estado General del Proyecto

| Fase | Estado | Descripción |
|------|--------|-------------|
| **Fase 1** | 🚧 EN DESARROLLO | Core funcional (pictogramas + IA + TTS básico) |
| **Fase 2** | 📅 Planificada | Contexto temporal y mejoras UX |
| **Fase 3** | 📅 Planificada | Personalización y perfiles de usuario |
| **Fase 4** | 📅 Planificada | TTS Premium (ElevenLabs) |
| **Fase 5** | 📅 Planificada | Modo offline y PWA |
| **Fase 6** | 📅 Planificada | Voice Cloning del usuario |

---

## Fase 1 - Core Funcional (🚧 EN DESARROLLO)

**Objetivo:** MVP funcional con las capacidades básicas del comunicador.

### Backend (✅ Completado)
- ✅ API REST con Symfony 7 + Cycle ORM + PostgreSQL
- ✅ Grid de pictogramas por categorías (10 categorías SAAC, colores Fitzgerald Key)
- ✅ Integración LLM multi-proveedor (`PHRASE_PROVIDER`: gemini, openai, fake)
- ✅ Caché de frases en PostgreSQL (SHA256 hash de secuencia)
- ✅ Sincronización automática con ARASAAC API (197 palabras core vocabulary)
- ✅ Health checks para Kubernetes (liveness, readiness)
- ✅ Rate limiting configurable en generación de frases
- ✅ Tests unitarios: 392 tests, PHPStan level 8

### Frontend (🚧 En Construccion)
- ✅ Clean Architecture frontend (Domain/Application/Infrastructure)
- ✅ Domain entities: Category, Pictogram, PhraseResponse + repository interfaces
- ✅ Zod schemas para validacion de API responses (Zero Trust)
- ✅ ApiClient HTTP + repositorios (HttpCategoryRepository, HttpPictogramRepository, HttpPhraseRepository)
- ✅ Pinia registrado, shadcn-vue configurado (Radix Vue, Lucide Vue)
- ✅ Tests unitarios: 230 tests con Vitest
- ✅ Pinia stores: useCategoryStore + usePictogramStore + usePhraseStore
- ✅ CategoryBar con Fitzgerald Key borders, Lucide icons, keyboard nav
- ✅ PictogramCard + PictogramGrid con Fitzgerald Key border-top, responsive grid
- ✅ PhraseBar: seleccion pictogramas (max 10), generacion frases via API, variaciones
- ✅ App shell: header, skip link, aria-live region
- ✅ Conexion API: fetchCategories + fetchByCategory + generatePhrase
- ✅ WCAG 2.1 AA: 44x44px touch targets, focus rings, aria-labels, role="tablist"
- ✅ Text-to-Speech con Web Speech API (TTSProvider interface, WebSpeechTTS, useTTS composable, SpeakButton)
- ✅ SearchBar con debounce y búsqueda: local DB → fallback ARASAAC API (descarga y persiste pictogramas)
- ✅ Error handling UI: inline feedback (ADR-010), mensajes user-friendly en español, feedback visual en botones (loading/error/retry)
- ✅ Rediseño visual moderno (ADR-011): paleta Indigo, fuente Inter, sombras, motion-safe, Badge/Skeleton shadcn-vue, WCAG 2.2
- ✅ Responsive tres configuraciones: movil, tablet portrait, tablet landscape (media query custom con clases utilitarias independientes)
- ✅ Tablet landscape compacto: busqueda + categorias en header, PhraseBar inline con scroll horizontal, proteccion landscape movil
- ✅ CD pipeline: GitHub Actions SSH deploy a Hetzner CX33, Docker Compose produccion (PHP-FPM + nginx + PostgreSQL), health check con rollback automatico

**Entregables tecnicos:**
- ✅ Clean Architecture backend (Domain/Application/Infrastructure)
- ✅ API REST funcional (Symfony 7)
- ✅ SPA responsiva (Vue 3 + TypeScript) - data layer + categories + pictograms + phrase flow + TTS + search + error feedback + diseño visual moderno + responsive 3 configuraciones
- ✅ Docker Compose con todos los servicios
- ✅ CI/CD con GitHub Actions
- ✅ Documentacion completa (ADRs, guias de desarrollo)

---

## Fase 2 - Contexto Temporal y Mejoras UX (📅 FUTURO)

**Objetivo:** Hacer la IA más contextual y mejorar la experiencia de usuario.

**Funcionalidades planificadas:**
- 🔜 Contexto temporal inteligente:
  - Hora del día: "Buenos días" (mañana), "Buenas tardes" (tarde), "Buenas noches" (noche)
  - Día de la semana: "Feliz fin de semana" (sábado/domingo), "Buen inicio de semana" (lunes)
  - Estaciones del año: Adaptar sugerencias según temporada
- 🔜 Historial de frases frecuentes:
  - Top 10 frases más usadas accesibles con 1 click
  - Persistencia en base de datos
- 🔜 Mejoras UX:
  - Botón "Repetir última frase" (el 40% de uso en SAAC es repetición)
  - Animaciones de feedback visual
  - Loading states más informativos
- 🔜 Analytics básico:
  - Dashboard de uso (pictogramas más usados, frases más generadas)
  - PostHog event tracking

**Impacto esperado:** Reducir tiempo de comunicación en 30% mediante acceso rápido a frases frecuentes.

---

## Fase 3 - Personalización (📅 FUTURO)

**Objetivo:** Permitir que cada usuario adapte el comunicador a sus necesidades específicas.

**Funcionalidades planificadas:**
- 🔜 Perfiles de usuario:
  - Múltiples usuarios en un mismo dispositivo
  - Configuración individual (voz, velocidad, categorías visibles)
- 🔜 Favoritos personalizables:
  - Marcar pictogramas favoritos
  - Categoría custom "Mis Favoritos"
- 🔜 Personalización de categorías:
  - Crear categorías propias (ej: "Escuela", "Familia", "Hobbies")
  - Reorganizar pictogramas entre categorías
- 🔜 Modos de visualización:
  - Modo noche / alto contraste
  - Tamaño de pictogramas ajustable (pequeño, mediano, grande)
  - Densidad de grid (2x2, 3x3, 4x4)
- 🔜 Exportación de datos:
  - Exportar configuración y favoritos (JSON)
  - Importar en otro dispositivo

**Impacto esperado:** Adaptación a necesidades individuales (TEA prefiere consistencia, afasia necesita simplicidad, ELA requiere eficiencia).

---

## Fase 4 - TTS Premium (📅 FUTURO)

**Objetivo:** Mejorar la calidad de la síntesis de voz con voces premium.

**Funcionalidades planificadas:**
- 🔜 Integración ElevenLabs API:
  - Voces en español de calidad premium (indistinguibles de humano)
  - Selección de voz (masculina, femenina, infantil)
  - Control de entonación y velocidad
- 🔜 Fallback automático:
  - Si ElevenLabs falla → Web Speech API (sin interrupción)
  - Indicador visual del proveedor TTS activo
- 🔜 Caché de audio:
  - Guardar MP3 de frases generadas
  - Reproducción instantánea en hits de caché
- 🔜 Configuración por usuario:
  - Elegir proveedor TTS (Web Speech vs ElevenLabs)
  - Modelo freemium (Web Speech gratis, ElevenLabs premium)

**Impacto esperado:** Voz 10x más natural, especialmente valorado por usuarios con afasia post-ictus y ELA (adultos que rechazan voces robotizadas).

---

## Fase 5 - Modo Offline y PWA (📅 FUTURO)

**Objetivo:** Funcionalidad completa sin conexión a internet.

**Funcionalidades planificadas:**
- 🔜 Progressive Web App (PWA):
  - Instalable en dispositivos móviles y tablets
  - Funciona como app nativa
  - Icono en pantalla de inicio
- 🔜 Service Workers:
  - Caché de assets (CSS, JS, imágenes de pictogramas)
  - Estrategia offline-first
- 🔜 Sincronización offline:
  - Guardar frases generadas cuando no hay internet
  - Sync automático cuando se recupera conexión
- 🔜 Base de datos local:
  - IndexedDB para pictogramas y frases frecuentes
  - 200+ pictogramas más usados precargados
- 🔜 Indicadores de conectividad:
  - Banner "Sin conexión" cuando no hay internet
  - Modo offline explícito (sin llamadas a OpenAI)

**Impacto esperado:** Uso en entornos sin internet (colegios rurales, actividades al aire libre, viajes).

---

## Fase 6 - Voice Cloning (📅 FUTURO)

**Objetivo:** Preservar la identidad vocal del usuario mediante clonación de voz.

**Funcionalidades planificadas:**
- 🔜 Grabación de voz del usuario:
  - Asistente guiado para grabar 5 minutos de audio
  - Validación de calidad de grabación
  - Frases predefinidas para maximizar cobertura fonética
- 🔜 Entrenamiento de modelo ElevenLabs:
  - Upload de audio a ElevenLabs Voice Cloning
  - Notificación cuando la voz esté lista
- 🔜 Síntesis con voz clonada:
  - Integración transparente (mismo código que Fase 4)
  - Fallback a voz premium si cloning falla
- 🔜 Gestión de voces clonadas:
  - Dashboard de voces (ver, reproducir muestra, eliminar)
  - Cambio rápido entre voces (voz propia vs voces premium)
- 🔜 Privacidad:
  - Audio de entrenamiento encriptado
  - Consentimiento explícito GDPR
  - Derecho a eliminar voz clonada

**Impacto esperado:** Máxima humanización. Especialmente valioso para personas con ELA que pierden progresivamente la voz (pueden preservar su identidad vocal antes de perderla).

**Restricción comercial:** Voice Cloning requiere ElevenLabs Professional Plan. Solo viable en modelo premium o institucional.

---

## Métricas de Éxito por Fase

| Fase | Métrica Clave | Objetivo |
|------|---------------|----------|
| **Fase 1** | Latencia p95 generación de frase | < 2s (primera vez), < 200ms (caché) |
| **Fase 2** | % Uso de frases frecuentes | > 40% |
| **Fase 3** | Usuarios con perfil personalizado | > 60% |
| **Fase 4** | Satisfacción con calidad de voz | > 8/10 |
| **Fase 5** | % Sesiones offline | > 20% |
| **Fase 6** | Usuarios con voz clonada | > 10% (early adopters) |

---

## Notas

- Algunas fases pueden sufrir modificaciones según feedback de usuarios
- Las métricas se actualizarán con datos reales conforme se complete cada fase
- El orden de las fases puede alterarse según prioridades del proyecto

**Última actualización:** 9 de febrero de 2026
