# Accessibility - HablaIA Frontend

> Resumen de conformidad WCAG 2.2 AA. Informe detallado en [`docs/audits/phase1-accessibility-audit.md`](audits/phase1-accessibility-audit.md)

**Ultima revision:** 13 de febrero de 2026
**Scope:** Frontend Vue 3.5 + TailwindCSS + shadcn-vue
**Fase:** Phase 1 MVP
**Lighthouse Accessibility:** 100/100 (mobile y desktop)

---

## Resumen WCAG 2.2 AA

| Principio | Estado | Notas |
|-----------|--------|-------|
| 1. Perceivable | PASA | Alt text, contraste verificado (15.8:1 texto, 5.7:1 secundario), `prefers-reduced-motion` respetado |
| 2. Operable | PASA | Keyboard nav completa, atajos globales, touch targets 44px, skip link |
| 3. Understandable | PASA | Idioma `lang="es"`, errores descriptivos con `role="alert"`, flujo predecible |
| 4. Robust | PASA | Landmarks, ARIA roles, dual aria-live (polite + assertive) |

**Veredicto: Conformidad substancial WCAG 2.2 AA.** 0 hallazgos criticos. 0 hallazgos pendientes.

---

## Hallazgos (13 total)

| Estado | Cantidad |
|--------|----------|
| Corregido (PR #73) | 6 |
| Pasa | 1 |
| Aceptado con justificacion | 1 |
| No aplica | 2 |
| Documentado (excepcion) | 1 |
| Informativo | 2 |

---

## Navegacion por teclado (ADR-013)

| Atajo | Accion |
|-------|--------|
| `1`-`0`, `?` | Seleccionar categoria (11 categorias) |
| `/` | Abrir buscador |
| `Escape` | Cerrar busqueda / limpiar frase |
| `Backspace` | Ir a chips de frase |
| `g` | Generar frase |
| `x` | Borrar frase |
| Arrow keys | Navegacion 2D en grid de pictogramas |

---

## Contraste (verificado)

| Elemento | Ratio | Minimo WCAG |
|----------|-------|-------------|
| Texto principal (#1c1917 sobre #fafaf9) | 15.3:1 | 4.5:1 |
| Texto secundario (#57534e sobre #ffffff) | 5.9:1 | 4.5:1 |
| Errores (red-700 sobre fondos claros) | 5.3:1 | 4.5:1 |
| Bordes no textuales (surface-400) | 3.1:1 | 3:1 |

---

## Dominio SAAC

| Caracteristica | Implementacion |
|----------------|----------------|
| Fitzgerald Key | Colores por categoria + iconos + texto (nunca solo color) |
| Pictogramas | Fondo blanco, 64x64px, contraste alto |
| Touch targets | Minimo 44x44px global (incluido tablet landscape via pseudo-elemento) |
| TTS | Web Speech API, `rate=0.9` optimizado para SAAC |
| Reduccion fatiga visual | Paleta neutra stone, bordes suaves, animaciones con `motion-safe:` |
| Screen reader | Dual aria-live, chips sin redundancia, landmarks completos |

---

## Pendiente para fases posteriores

- **Patron Tabs WAI-ARIA completo** (tabindex roving, Home/End)
- **TTS aria-live** (anuncios inicio/fin reproduccion)
- **axe-core en CI** (deteccion automatica de regresiones)
- **Validacion con usuarios reales** (logopedas y usuarios SAAC)
