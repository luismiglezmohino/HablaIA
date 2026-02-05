# Documentacion - HablaIA

> Guia de navegacion de toda la documentacion del proyecto

**Ultima actualizacion:** 5 de febrero de 2026

---

## Documentacion General

| Documento | Descripcion |
|-----------|-------------|
| [README.md](../README.md) | Descripcion del proyecto, stack, instalacion, estructura y funcionalidades |
| [ROADMAP.md](ROADMAP.md) | Plan de evolucion en 6 fases con metricas de exito |
| [SECURITY.md](SECURITY.md) | Resumen de postura de seguridad (OWASP Top 10) |
| [openapi.yaml](openapi.yaml) | Especificacion OpenAPI 3.0 de la API REST |

---

## Backend y Frontend

| Documento | Descripcion |
|-----------|-------------|
| [backend/README.md](../backend/README.md) | Setup, arquitectura, comandos y testing del backend Symfony |
| [frontend/README.md](../frontend/README.md) | Setup y desarrollo del frontend Vue.js |

---

## ADRs (Architecture Decision Records)

Decisiones arquitectonicas documentadas con contexto, alternativas y consecuencias.

| ADR | Titulo | Tema |
|-----|--------|------|
| [ADR-001](adrs/ADR-001-clean-architecture.md) | Clean Architecture | Separacion en capas Domain/Application/Infrastructure |
| [ADR-002](adrs/ADR-002-openai-integration.md) | OpenAI Integration | Integracion con OpenAI para generacion de frases |
| [ADR-003](adrs/ADR-003-arasaac-pictograms.md) | ARASAAC Pictograms | Integracion con API de pictogramas ARASAAC |
| [ADR-004](adrs/ADR-004-tts-strategy.md) | TTS Strategy | Estrategia de Text-to-Speech progresiva |
| [ADR-005](adrs/ADR-005-phrase-caching.md) | Phrase Caching | Cache de frases generadas con hash SHA-256 |
| [ADR-006](adrs/ADR-006-uuid-agnostic-domain.md) | UUID Agnostic Domain | Dominio agnostico de implementacion UUID |
| [ADR-007](adrs/ADR-007-cycle-orm-over-doctrine.md) | Cycle ORM over Doctrine | Eleccion de Cycle ORM frente a Doctrine |
| [ADR-008](adrs/ADR-008-fitzgerald-key-color-coding.md) | Fitzgerald Key Color Coding | Sistema de colores por categoria segun Fitzgerald Key |
| [ADR-009](adrs/ADR-009-multi-provider-llm.md) | Multi-Provider LLM | Arquitectura multi-proveedor para LLM (OpenAI, Gemini, Fake) |

---

## Diagramas

Diagramas Mermaid de la arquitectura del sistema.

| Diagrama | Descripcion |
|----------|-------------|
| [domain-layer.md](diagrams/domain-layer.md) | Entidades, Value Objects, repositorios y servicios del dominio |
| [application-layer.md](diagrams/application-layer.md) | Use Cases, DTOs y flujo de la capa de aplicacion |
| [infrastructure-layer.md](diagrams/infrastructure-layer.md) | Controllers, persistencia, APIs externas y servicios |
| [api-flow.md](diagrams/api-flow.md) | Flujo de peticiones HTTP a traves de las capas |
| [docker-infrastructure.md](diagrams/docker-infrastructure.md) | Contenedores Docker, red interna, Nginx reverse proxy y trusted proxies |

---

## Auditorias

Informes de auditoria por fase del proyecto.

| Informe | Descripcion |
|---------|-------------|
| [phase1-security-audit.md](audits/phase1-security-audit.md) | Auditoria de seguridad OWASP Top 10 - Phase 1 (backend) |
| [phase1-qa-review.md](audits/phase1-qa-review.md) | Revision de calidad, cobertura de tests y gates - Phase 1 (backend) |

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
