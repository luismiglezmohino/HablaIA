# Documentación - HablaIA

> Guía de navegación de toda la documentación del proyecto

**Última actualización:** 24 de febrero de 2026

---

## Documentación General

| Documento | Descripción |
|-----------|-------------|
| [README.md](../README.md) | Descripción del proyecto, stack, instalación, estructura y funcionalidades |
| [ROADMAP.md](ROADMAP.md) | Plan de evolución en 7 fases |
| [TROUBLESHOOTING.md](guides/TROUBLESHOOTING.md) | Solución de problemas comunes de instalación |
| [SECURITY.md](guides/SECURITY.md) | Resumen de postura de seguridad (OWASP Top 10) |
| [PERFORMANCE.md](guides/PERFORMANCE.md) | Resumen de rendimiento y Core Web Vitals |
| [QUALITY.md](guides/QUALITY.md) | Resumen de calidad, tests y gates 100/80/0 |
| [ACCESSIBILITY.md](guides/ACCESSIBILITY.md) | Resumen de conformidad WCAG 2.2 AA |
| [competitive-analysis.md](competitive-analysis.md) | Análisis comparativo HablaIA vs comunicadores SAAC existentes |
| [openapi.yaml](openapi.yaml) | Especificación OpenAPI 3.0 de la API REST |

---

## Backend y Frontend

| Documento | Descripción |
|-----------|-------------|
| [backend/README.md](../backend/README.md) | Setup, arquitectura, comandos y testing del backend Symfony |
| [frontend/README.md](../frontend/README.md) | Setup y desarrollo del frontend Vue.js |

---

## ADRs (Architecture Decision Records)

Decisiones arquitectónicas documentadas con contexto, alternativas y consecuencias.

| ADR | Título | Tema |
|-----|--------|------|
| [ADR-001](adrs/ADR-001-clean-architecture.md) | Clean Architecture | Separación en capas Domain/Application/Infrastructure |
| [ADR-002](adrs/ADR-002-openai-integration.md) | OpenAI Integration | Integración con OpenAI para generación de frases |
| [ADR-003](adrs/ADR-003-arasaac-pictograms.md) | ARASAAC Pictograms | Pictogramas ARASAAC: estrategia local-first, búsqueda accent-insensitive y categoría "Sin categoría" |
| [ADR-004](adrs/ADR-004-tts-strategy.md) | TTS Strategy | Estrategia de Text-to-Speech progresiva |
| [ADR-005](adrs/ADR-005-phrase-caching.md) | Phrase Caching | Caché de frases generadas con hash SHA-256 |
| [ADR-006](adrs/ADR-006-uuid-agnostic-domain.md) | UUID Agnostic Domain | Dominio agnóstico de implementación UUID |
| [ADR-007](adrs/ADR-007-cycle-orm-over-doctrine.md) | Cycle ORM over Doctrine | Elección de Cycle ORM frente a Doctrine |
| [ADR-008](adrs/ADR-008-fitzgerald-key-color-coding.md) | Fitzgerald Key Color Coding | Sistema de colores por categoría según Fitzgerald Key |
| [ADR-009](adrs/ADR-009-multi-provider-llm.md) | Multi-Provider LLM | Arquitectura multi-proveedor para LLM (OpenAI, Gemini, Fake) |
| [ADR-010](adrs/ADR-010-inline-feedback-over-toasts.md) | Inline Feedback over Toasts | Feedback visual inline en vez de toasts para accesibilidad SAAC |
| [ADR-011](adrs/ADR-011-visual-design-system.md) | Visual Design System | Sistema de diseño visual moderno con paleta Indigo y responsive |
| [ADR-012](adrs/ADR-012-cd-pipeline.md) | CD Pipeline | Despliegue continuo con GitHub Actions, SSH y Docker Compose |
| [ADR-013](adrs/ADR-013-keyboard-screenreader-accessibility.md) | Keyboard & Screen Reader | Navegación por teclado, atajos, gestión de foco y anuncios SR |
| [ADR-014](adrs/ADR-014-monorepo-structure.md) | Monorepo Structure | Monorepo con stacks independientes y Docker Compose como unión |
| [ADR-015](adrs/ADR-015-groq-primary-llm-provider.md) | Groq Primary LLM | Groq GPT-OSS 120B como proveedor LLM principal (sustituye Gemini) |

---

## Diagramas

Diagramas Mermaid de la arquitectura del sistema.

| Diagrama | Descripción |
|----------|-------------|
| [domain-layer.md](diagrams/domain-layer.md) | Entidades, Value Objects, repositorios y servicios del dominio |
| [application-layer.md](diagrams/application-layer.md) | Use Cases, DTOs y flujo de la capa de aplicación |
| [infrastructure-layer.md](diagrams/infrastructure-layer.md) | Controllers, persistencia, APIs externas y servicios |
| [api-flow.md](diagrams/api-flow.md) | Flujo de peticiones HTTP a través de las capas |
| [docker-infrastructure.md](diagrams/docker-infrastructure.md) | Contenedores Docker, red interna, Nginx reverse proxy y trusted proxies |

---

## Auditorías

Informes de auditoría por fase del proyecto.

| Informe | Resumen | Descripción |
|---------|---------|-------------|
| [phase1-security-audit.md](audits/phase1-security-audit.md) | [SECURITY.md](guides/SECURITY.md) | Auditoría de seguridad OWASP Top 10 - Fase 1 |
| [phase1-performance-audit.md](audits/phase1-performance-audit.md) | [PERFORMANCE.md](guides/PERFORMANCE.md) | Auditoría de rendimiento y Core Web Vitals - Fase 1 |
| [phase1-qa-review.md](audits/phase1-qa-review.md) | [QUALITY.md](guides/QUALITY.md) | Revisión de calidad, cobertura de tests y gates - Fase 1 |
| [phase1-accessibility-audit.md](audits/phase1-accessibility-audit.md) | [ACCESSIBILITY.md](guides/ACCESSIBILITY.md) | Auditoría WCAG 2.2 AA - Fase 1 (frontend) |

---

## Testing y Validación

| Documento | Descripción |
|-----------|-------------|
| [prompt-tuning-validation.md](testing/prompt-tuning-validation.md) | Validación de prompt tuning LLM multi-modelo |

---

## Tests de API

Scripts de prueba manual para los endpoints REST (curl).

| Script | Endpoint |
|--------|----------|
| [run-all.sh](api-tests/run-all.sh) | Ejecuta todos los scripts de test |
| [categories.sh](api-tests/categories.sh) | `GET /api/categories` |
| [pictograms.sh](api-tests/pictograms.sh) | `GET /api/pictograms` |
| [phrases.sh](api-tests/phrases.sh) | `POST /api/phrases/generate` |
| [health.sh](api-tests/health.sh) | `GET /api/health/*` |
