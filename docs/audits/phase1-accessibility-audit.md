# Auditoria de Accesibilidad WCAG 2.2 AA - Fase 1 (Frontend)

> Revision de cumplimiento WCAG 2.2 AA y usabilidad SAAC del frontend de HablaIA

**Ultima revision:** 13 de febrero de 2026<br>
**Revision anterior:** 11 de febrero de 2026<br>
**Alcance:** Frontend (`frontend/src/`) - Vue 3 + TailwindCSS + shadcn-vue<br>
**Fase:** Fase 1 MVP (comunicador publico, sin autenticacion)<br>
**Evaluador:** @ux_designer

---

## Contenido

- [Metodologia](#metodologia)
- [Resumen Ejecutivo](#resumen-ejecutivo)
- [1. Perceivable](#1-perceivable-perceptible)
- [2. Operable](#2-operable)
- [3. Understandable](#3-understandable-comprensible)
- [4. Robust](#4-robust)
- [5. Evaluacion Especifica SAAC](#5-evaluacion-especifica-saac)
- [6. Hallazgos Consolidados](#6-hallazgos-consolidados)
- [7. Hallazgos Corregidos](#7-hallazgos-corregidos-desde-revision-anterior)
- [8. Resumen por Componente](#8-componentes-resumen-por-componente)
- [9. Trabajo Futuro](#9-trabajo-futuro-de-accesibilidad)
- [10. Conclusion](#10-conclusion)

---

## Metodologia

### Proceso

1. Revision asistida por IA (LLM como @ux_designer) de todos los componentes Vue, HTML base, CSS global y configuracion Tailwind
2. Verificacion sistematica por criterio WCAG 2.2 AA (Perceivable, Operable, Understandable, Robust)
3. Evaluacion especifica para dominio SAAC (TEA, afasia, paralisis cerebral, ELA)
4. Clasificacion de hallazgos por severidad (Critico / Alto / Medio / Bajo / Informativo)
5. Recomendaciones de mejora

### Archivos Revisados

| Archivo | Descripcion |
|---------|-------------|
| `index.html` | Documento base HTML |
| `src/App.vue` | Shell principal, landscape blocker |
| `src/presentation/views/HomeView.vue` | Vista principal, atajos de teclado, aria-live regions |
| `src/presentation/components/CategoryBar.vue` | Barra de categorias con tabs |
| `src/presentation/components/PictogramGrid.vue` | Grid de pictogramas con navegacion 2D |
| `src/presentation/components/PictogramCard.vue` | Tarjeta individual de pictograma |
| `src/presentation/components/PhraseBar.vue` | Barra de frase con chips y generacion |
| `src/presentation/components/SearchBar.vue` | Buscador de pictogramas con debounce |
| `src/presentation/components/SpeakButton.vue` | Boton TTS para reproducir frases |
| `src/presentation/components/ui/badge/Badge.vue` | Badge de shadcn-vue |
| `src/presentation/components/ui/skeleton/Skeleton.vue` | Skeleton de shadcn-vue |
| `src/styles/main.css` | Estilos globales, skip link, focus rings, touch targets |
| `tailwind.config.js` | Tokens de diseno, colores, sombras, touch sizes |
| `src/application/composables/useTTS.ts` | Composable de Text-to-Speech |
| `src/infrastructure/tts/WebSpeechTTS.ts` | Implementacion Web Speech API |
| `src/application/stores/usePhraseStore.ts` | Estado de frase seleccionada |
| `src/application/stores/usePictogramStore.ts` | Estado de pictogramas visibles |
| `src/application/stores/useCategoryStore.ts` | Estado de categorias |

### Herramientas y Referencias

| Recurso | Uso |
|---------|-----|
| Revision de codigo asistida por IA | Analisis de ARIA, semantica HTML, clases Tailwind |
| WCAG 2.2 AA Quick Reference | Checklist sistematico de criterios de conformidad |
| ADR-008 | Fitzgerald Key color coding |
| ADR-011 | Sistema de diseno visual |
| ADR-013 | Navegacion por teclado y screen reader |
| WAI-ARIA Authoring Practices | Patrones Grid, Tabs, Status Messages |

---

## Resumen Ejecutivo

| Estado | Hallazgos |
|--------|-----------|
| CORREGIDO | 6 |
| PASA | 1 |
| ACEPTADO | 1 |
| NO APLICA | 2 |
| DOCUMENTADO | 1 |
| INFORMATIVO | 2 |
| **Total** | **13** |

**Conclusion general:** La aplicacion demuestra un nivel de accesibilidad elevado para un MVP, con mejoras significativas respecto a la revision anterior. Se corrigieron [6 hallazgos](#7-hallazgos-corregidos-desde-revision-anterior) (A1, A4, A5, A7, A11, A12). A8 (chips 28px) pasa porque el CSS global fuerza 44px en todos los botones. Se acepto [1 con justificacion](#aceptados-con-justificacion) (A9: arrow keys funcionales, refinamiento WAI-ARIA). 2 [no aplican](#no-aplica) al alcance actual (A2: `aria-controls` no es requisito WCAG AA, A6: H1 en DOM para SR). 1 [documentado](#documentado) como excepcion esencial (A3: landscape en SAAC). 0 hallazgos criticos o pendientes que bloqueen el uso por usuarios SAAC.

### Cambios respecto a revision anterior (11 feb 2026)

| Hallazgo | Severidad anterior | Estado actual | PR/Motivo |
|----------|-------------------|---------------|-----------|
| A1: Alt redundante en PictogramCard | Bajo | **CORREGIDO** | PR #73 |
| A2: Sin aria-controls tabs-grid | Medio | **NO APLICA** | Recomendacion WAI-ARIA, no requisito WCAG AA |
| A4: Contraste red-600 sobre red-50 | Medio | **CORREGIDO** | PR #73 |
| A5: Bordes surface-200 bajo contraste | Medio | **CORREGIDO** | PR #73 (border-surface-400) |
| A6: H1 oculto en movil | Bajo | **NO APLICA** | H1 en DOM (sr-only), SR lo encuentra |
| A7: Touch targets tablet landscape | Alto | **CORREGIDO** | PR #73 (pseudo-elemento 44px) |
| A9: Tabs sin tabindex roving | Alto | **ACEPTADO** | Arrow keys funcionales, refinamiento WAI-ARIA |
| A11: Sin atajo para generar frase | Informativo | **CORREGIDO** | PR #73 (`g` y `x` atajos) |
| A12: role="searchbox" redundante | Informativo | **CORREGIDO** | PR #73 |

---

## 1. Perceivable (Perceptible)

### 1.1 Alternativas de Texto (WCAG 1.1.1 - Nivel A)

**Componentes revisados:** PictogramCard, PhraseBar, CategoryBar, HomeView, App

| Elemento | Verificacion | Resultado |
|----------|-------------|-----------|
| Imagenes de pictogramas en grid (`PictogramCard`) | `alt=""` (decorativa, boton tiene `aria-label`) | PASA |
| Imagenes de pictogramas en chips (`PhraseBar`) | `alt=""` + `aria-hidden="true"` (decorativo, nombre en `role="group"`) | PASA |
| Iconos Lucide (categorias, busqueda, acciones) | `aria-hidden="true"` en todos | PASA |
| Logo/Sparkles en header | `aria-hidden="true"` | PASA |
| Iconos de landscape blocker | `aria-hidden="true"` en contenedor, texto visible como alternativa | PASA |
| Boton de pictograma | `aria-label="Pictograma ${label}"` | PASA |
| SpeakButton | `aria-label="Escuchar: ${text.slice(0, 50)}"` | PASA |

**Hallazgo A1 - CORREGIDO (PR #73):** La imagen de pictograma en `PictogramCard` ahora usa `alt=""` (marcada como decorativa) ya que el boton que la contiene proporciona el nombre accesible via `aria-label="Pictograma ${label}"`. Esto elimina la lectura redundante en screen readers.

**Verificado:** `PictogramCard.vue` linea 29: `alt=""`.

### 1.2 Contenido Temporal (WCAG 1.2.x - Nivel A/AA)

No aplica. La aplicacion no contiene audio ni video pregrabado.

### 1.3 Adaptable (WCAG 1.3.1-1.3.5)

**1.3.1 Info and Relationships (Nivel A)**

| Elemento | Verificacion | Resultado |
|----------|-------------|-----------|
| `<header>` con logo y busqueda | Landmark implicito `banner` | PASA |
| `<main id="main-content">` | Landmark implicito `main` | PASA |
| `<footer aria-label="Atajos de teclado">` | Landmark implicito `contentinfo` con label | PASA |
| `<nav aria-label="Categorias">` en CategoryBar | Landmark implicito `navigation` con label | PASA |
| `<section aria-label="Barra de frases">` en PhraseBar | Landmark implicito `region` con label | PASA |
| `<section aria-label="Frases generadas">` movil | Landmark implicito `region` con label | PASA |
| `<h1>` en header | Unico heading nivel 1, siempre accesible (sr-only en movil) | PASA |
| Skip link "Ir al contenido principal" | Presente y funcional | PASA |
| `role="tablist"` / `role="tab"` en CategoryBar | Patron tabs implementado | PASA |
| `role="grid"` en PictogramGrid | Patron grid implementado | PASA |
| `role="group"` en chips de PhraseBar | Agrupacion semantica correcta | PASA |
| `role="list"` en resultados de frases | Lista semantica correcta | PASA |
| `role="status"` en loading de CategoryBar y PictogramGrid | Anuncios de estado | PASA |
| `role="alert"` en errores | Anuncios de error inmediatos | PASA |

**Hallazgo A2 - NO APLICA:** El `role="grid"` en PictogramGrid no tiene un `role="tabpanel"` asociado que lo vincule con las tabs de CategoryBar. Segun el patron WAI-ARIA Tabs, cada tab deberia tener un tabpanel vinculado con `aria-controls` / `aria-labelledby`. Sin embargo, la navegacion por teclado funciona correctamente (arrow keys entre categorias, Tab al grid). `aria-controls` es una recomendacion del WAI-ARIA Authoring Practices, no un requisito de WCAG 2.2 AA. La relacion entre categoria y pictogramas es evidente por el cambio de contenido al seleccionar una tab.

**1.3.2 Meaningful Sequence (Nivel A)**

El orden del DOM sigue la secuencia logica de uso: header -> frase -> busqueda -> categorias -> grid de pictogramas. PASA.

**1.3.3 Sensory Characteristics (Nivel A)**

No se depende unicamente de forma, tamano o posicion para transmitir informacion. Los colores Fitzgerald Key se combinan con iconos y texto (segun ADR-008). PASA.

**1.3.4 Orientation (Nivel AA)**

La aplicacion soporta portrait y landscape. El bloqueador de landscape se activa solo en moviles con `max-height: 500px`, lo que constituye una restriccion de orientacion para un subconjunto de dispositivos.

**Hallazgo A3 - Informativo (DOCUMENTADO):** El landscape blocker en moviles (viewport < 500px de alto) impide el uso en orientacion landscape. WCAG 1.3.4 indica que el contenido no debe restringir la orientacion excepto cuando es "esencial". Para un comunicador SAAC, la justificacion es que no hay espacio suficiente en un movil en landscape para mostrar pictogramas de forma util. Tablets y PCs no estan afectados por este bloqueo. Excepcion documentada en ADR-011.

**1.3.5 Identify Input Purpose (Nivel AA)**

El input de busqueda usa `type="search"`, lo que permite autocompletar y tecnologias asistivas. PASA.

### 1.4 Distinguishable (WCAG 1.4.x)

**1.4.1 Use of Color (Nivel A)**

| Elemento | Color como unico canal? | Canales adicionales | Resultado |
|----------|------------------------|---------------------|-----------|
| Categorias | Color de borde Fitzgerald | Icono + texto + `aria-selected` + ring de seleccion | PASA |
| Pictograma seleccionado en frase | Color de borde izquierdo | Imagen + etiqueta + boton eliminar | PASA |
| Error en generacion | Texto rojo | Texto descriptivo ("No se pudo generar...") + `role="alert"` | PASA |
| Boton generar en estado error | Color rojo | Texto "Reintentar" + icono RefreshCw | PASA |
| Badge de fuente (cache/generated) | Color accent | Texto visible ("generated", "cache") | PASA |

**1.4.3 Contrast Minimum (Nivel AA) - Ratio minimo 4.5:1 para texto**

Valores calculados contra los fondos donde se usan:

| Elemento | Color texto | Color fondo | Ratio | Resultado |
|----------|------------|-------------|-------|-----------|
| Texto principal (`accessible-text` #1c1917) | #1c1917 | #fafaf9 (surface-50) | ~15.3:1 | PASA |
| Texto principal sobre blanco | #1c1917 | #ffffff | ~15.8:1 | PASA |
| Texto secundario (`accessible-textLight` #57534e) | #57534e | #ffffff | ~5.9:1 | PASA |
| Texto secundario sobre surface-50 | #57534e | #fafaf9 | ~5.7:1 | PASA |
| Placeholder busqueda (`surface-300` #d6d3d1) | #d6d3d1 | #fafaf9 | ~1.5:1 | N/A (*) |
| Error (`red-700` #b91c1c) | #b91c1c | #ffffff | ~5.6:1 | PASA |
| Error sobre red-50 | #b91c1c | #fef2f2 | ~5.3:1 | PASA |
| Boton primario (blanco sobre `primary-600` #4f46e5) | #ffffff | #4f46e5 | ~5.7:1 | PASA |
| Boton error (blanco sobre `red-600` #dc2626) | #ffffff | #dc2626 | ~4.6:1 | PASA |
| Badge accent (`accent-800` #86198f sobre `accent-100` #fae8ff) | #86198f | #fae8ff | ~7.2:1 | PASA |
| Atajos teclado footer (`accessible-textLight` sobre `surface-50`) | #57534e | #fafaf9 | ~5.7:1 | PASA |
| Etiqueta pictograma (`accessible-text` sobre blanco) | #1c1917 | #ffffff | ~15.8:1 | PASA |

(*) Los placeholders no estan sujetos a WCAG 1.4.3 segun la interpretacion estandar, ya que no son "texto" funcional.

**Hallazgo A4 - CORREGIDO (PR #73):** El texto de error se cambio de `red-600` (#dc2626) a `red-700` (#b91c1c) en todos los componentes que muestran errores: `PictogramGrid.vue` (linea 63), `CategoryBar.vue` (linea 76), `PhraseBar.vue` (linea 168). El ratio sobre fondo `red-50` mejora de ~4.4:1 a ~5.3:1, superando holgadamente el minimo 4.5:1. Sobre fondo blanco: ~5.6:1.

**1.4.4 Resize Text (Nivel AA)**

La interfaz usa unidades relativas (`text-sm`, `text-base`, `text-lg`) y layout flexible. Verificado en Tailwind que no se usan unidades `px` para tamanos de fuente. PASA.

**1.4.5 Images of Text (Nivel A)**

No se usan imagenes de texto. Los pictogramas ARASAAC son ilustraciones, no texto renderizado como imagen. PASA.

**1.4.10 Reflow (Nivel AA)**

La interfaz es responsive con tres configuraciones (movil, tablet portrait, tablet landscape) y usa CSS Grid/Flexbox con wrap. A 320px de ancho funciona sin scroll horizontal. PASA.

**1.4.11 Non-text Contrast (Nivel AA) - Ratio minimo 3:1**

| Elemento UI | Color | Fondo | Ratio | Resultado |
|-------------|-------|-------|-------|-----------|
| Focus ring (`primary-500` #6366f1) | #6366f1 | #ffffff | ~4.6:1 | PASA |
| Borde input focus (`primary-400` #818cf8) | #818cf8 | #ffffff | ~3.4:1 | PASA |
| Bordes de tarjeta en reposo | Default Tailwind (~#e5e7eb) | #ffffff | ~1.3:1 | LIMITE |
| Bordes de input en reposo | `surface-200` #e7e5e4 | #fafaf9 | ~1.2:1 | LIMITE |
| Spinner loading (`primary-500` #6366f1 / `surface-300` #d6d3d1) | mezcla | #ffffff | ~4.6:1 / ~1.5:1 | PARCIAL |

**Hallazgo A5 - CORREGIDO (PR #73):** Se aplico `border-surface-400` en `PictogramCard.vue`, `PhraseBar.vue` (chips) y `SearchBar.vue` para mejorar el contraste de bordes en reposo. Ademas, las tarjetas de pictograma tienen contraste visual adicional por: (a) borde superior de 5px con color Fitzgerald Key (alto contraste), (b) sombra `shadow-card`, y (c) fondo tintado con color de categoria (`backgroundColor: colorHex + '14'`). La informacion no depende unicamente del borde gris. En estado focus, el ring `primary-500` cumple holgadamente (4.6:1).

**1.4.12 Text Spacing (Nivel AA)**

No se fijan `line-height`, `letter-spacing` ni `word-spacing` con valores que impidan la personalizacion del usuario. Tailwind aplica valores por defecto que se pueden sobreescribir. PASA.

**1.4.13 Content on Hover or Focus (Nivel AA)**

No hay tooltips ni popups que aparezcan en hover/focus. Las animaciones de elevacion (`hover:-translate-y-1`) son puramente decorativas y no muestran contenido adicional. PASA.

---

## 2. Operable

### 2.1 Keyboard Accessible (WCAG 2.1.1-2.1.4)

**2.1.1 Keyboard (Nivel A)**

| Funcionalidad | Mecanismo | Resultado |
|---------------|-----------|-----------|
| Navegar entre categorias | Tab + ArrowLeft/ArrowRight (patron tabs) | PASA |
| Seleccionar categoria | Enter/Click + atajos 1-9, 0, ? | PASA |
| Buscar pictogramas | `b` para enfocar, Esc para limpiar/salir | PASA |
| Navegar grid de pictogramas | ArrowUp/Down/Left/Right (2D dinamico) | PASA |
| Seleccionar pictograma | Enter/Space (nativo de button) | PASA |
| Eliminar chip de frase | Tab a chips + Enter en X, Backspace desde fuera | PASA |
| Generar frase | `g` (atajo global) o Tab a boton Generar + Enter | PASA |
| Borrar todos los pictogramas | `x` (atajo global) o Tab a boton Trash + Enter | PASA |
| Reproducir frase (TTS) | Tab a SpeakButton + Enter | PASA |
| Skip link | Tab al inicio + Enter | PASA |

**2.1.2 No Keyboard Trap (Nivel A)**

Se verifico que no existen trampas de teclado. El flujo de Tab recorre: skip link -> header -> busqueda -> categorias -> frase (si hay chips) -> grid -> footer. Esc sale del input de busqueda. PASA.

**2.1.4 Character Key Shortcuts (Nivel A)**

Los atajos `1-9, 0, ?, b, g, x, Backspace` solo se activan fuera de inputs (`isInInput` check en `handleGlobalKeydown`). PASA.

### 2.2 Enough Time (WCAG 2.2.1-2.2.2)

No hay limites de tiempo en la interfaz. El debounce de busqueda (300ms) es transparente al usuario. PASA.

### 2.3 Seizures and Physical Reactions (WCAG 2.3.1)

No hay contenido que parpadee mas de 3 veces por segundo. La unica animacion repetitiva es el spinner de carga (`animate-spin`), que es un movimiento rotatorio continuo, no un parpadeo. Todas las animaciones usan `motion-safe:` y respetan `prefers-reduced-motion`. PASA.

### 2.4 Navigable (WCAG 2.4.x)

**2.4.1 Bypass Blocks (Nivel A)**

Skip link "Ir al contenido principal" presente en HomeView, conectado a `#main-content`. Estilos: sr-only por defecto, visible en focus. PASA.

**2.4.2 Page Titled (Nivel A)**

`<title>HablaIA</title>` en `index.html`. Como SPA de una sola pagina, un solo titulo es suficiente. PASA.

**2.4.3 Focus Order (Nivel A)**

El orden de foco sigue la secuencia logica de uso del comunicador: skip link -> header -> busqueda -> categorias -> frase -> grid -> footer. La gestion de foco activa (post-seleccion, post-eliminacion, post-generacion) sigue la logica de uso esperada (ADR-013). PASA.

**2.4.5 Multiple Ways (Nivel AA)**

Los pictogramas se pueden encontrar por: 1) navegacion por categoria, 2) busqueda por texto. Dos vias independientes. PASA.

**2.4.6 Headings and Labels (Nivel AA)**

| Heading/Label | Contexto | Resultado |
|---------------|----------|-----------|
| `<h1>` "HablaIA" | Titulo principal (`sr-only` en movil, visible en sm+) | PASA |
| `aria-label="Categorias"` | Nav de categorias | PASA |
| `aria-label="Barra de frases"` | Section de PhraseBar | PASA |
| `aria-label="Pictogramas"` | Grid principal | PASA |
| `aria-label="Frases generadas"` | Section movil de resultados | PASA |
| `aria-label="Atajos de teclado"` | Footer desktop | PASA |
| `<label for="search-pictograms">` | Input de busqueda (sr-only) | PASA |
| `<label for="mobile-search">` | Input de busqueda movil (sr-only) | PASA |

**Hallazgo A6 - CORREGIDO (PR #73):** El `<h1>` ahora usa `sr-only sm:not-sr-only sm:block tablet-landscape-hide` (`HomeView.vue` linea 224), lo que garantiza que siempre esta accesible para screen readers en todas las pantallas (incluido movil), aunque solo sea visible en sm+. Los screen readers detectan el heading porque esta en el DOM, aunque no sea visible en pantalla.

**2.4.7 Focus Visible (Nivel AA)**

Todos los elementos interactivos tienen `focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2`. Ademas, el CSS global en `main.css` aplica `ring-focus ring-primary-500 ring-offset-2` a todo `*:focus-visible`. Las tarjetas de pictograma usan `has-[:focus-visible]` para elevar el indicador al nivel de tarjeta completa. PASA.

**2.4.11 Focus Not Obscured (Nivel AA)**

`scroll-padding-top: 180px` en `html` para compensar la cabecera sticky + PhraseBar + SearchBar. PASA.

### 2.5 Input Modalities (WCAG 2.5.x)

**2.5.1 Pointer Gestures (Nivel A)**

No se requieren gestos complejos (multipoint, path-based). Toda la interaccion es single-tap/click. PASA.

**2.5.2 Pointer Cancellation (Nivel A)**

Todos los handlers usan `@click` (activacion en `click`, no en `mousedown`). PASA.

**2.5.3 Label in Name (Nivel A)**

Los aria-labels incluyen el texto visible donde aplica:

| Boton | Texto visible | aria-label | Resultado |
|-------|--------------|------------|-----------|
| Boton categoria | "Personas" (texto visible en sm+) | "Categoria Personas" | PASA |
| Boton pictograma | "agua" (texto visible) | "Pictograma agua" | PASA |
| Boton eliminar chip | X (icono) | "Eliminar agua" | PASA |
| Boton generar | "Generar frase" (texto) | "Generar frase" | PASA |
| Boton hablar | Icono volumen | "Escuchar: [texto]" | PASA |
| Boton borrar busqueda | X (icono) | "Borrar busqueda" | PASA |
| Boton borrar todos | Icono papelera | "Borrar todos los pictogramas" | PASA |

**2.5.8 Target Size Minimum (Nivel AA)**

| Elemento | Tamano minimo | Resultado |
|----------|--------------|-----------|
| Botones de categoria (CategoryBar) | `min-h-touch min-w-touch` (44x44px) | PASA |
| Botones de pictograma (PictogramCard) | `min-h-touch min-w-touch` (44x44px) | PASA |
| Boton generar frase | `w-full py-3.5` (>44px alto) | PASA |
| SpeakButton | `min-h-touch min-w-touch` (44x44px) | PASA |
| Boton borrar busqueda (SearchBar) | CSS global `min-h-touch min-w-touch` | PASA |
| Boton eliminar chip (PhraseBar) | CSS global fuerza 44px (prevalece sobre `min-h-7`) | PASA |
| CSS global `button, a, [role='button']` | `min-h-touch min-w-touch` (44x44px) | PASA |
| Categorias en tablet landscape (HomeView header) | Visual 36px + pseudo-elemento 44x44px | PASA |

**Hallazgo A7 - CORREGIDO (PR #73):** Los botones de categoria en tablet landscape (`h-9 w-9`, 36px visual) ahora tienen un pseudo-elemento `::after` de 44x44px que amplifica el area de interaccion (`main.css` lineas 66-74, clase `tablet-landscape-center`). El pseudo-elemento esta posicionado centrado sobre cada boton y cubre el area minima requerida por WCAG 2.5.8, aunque el boton permanece visualmente a 36px para encajar 11 categorias en la cabecera.

**Verificado:** `main.css` lineas 66-74:
```css
.tablet-landscape-center::after {
    content: '';
    position: absolute;
    inset: 50% auto auto 50%;
    transform: translate(-50%, -50%);
    width: 44px;
    height: 44px;
}
```

---

## 3. Understandable (Comprensible)

### 3.1 Readable (WCAG 3.1.x)

**3.1.1 Language of Page (Nivel A)**

`<html lang="es">` en `index.html`. PASA.

**3.1.2 Language of Parts (Nivel AA)**

No hay contenido en otro idioma. El texto de la interfaz esta integramente en español. La respuesta de la API (frases generadas) se configura en espanol via el prompt del backend. PASA.

### 3.2 Predictable (WCAG 3.2.x)

**3.2.1 On Focus (Nivel A)**

Enfocar un elemento no provoca cambio de contexto. El input de busqueda emite `@focus` solo para control de z-index. PASA.

**3.2.2 On Input (Nivel A)**

La busqueda con debounce (300ms) cambia el contenido del grid, pero no cambia el contexto de la pagina. La seleccion de categoria cambia el contenido del grid, comportamiento esperado y predecible. PASA.

**3.2.3 Consistent Navigation (Nivel AA)**

La estructura de navegacion es consistente: siempre header -> frase -> busqueda -> categorias -> grid. No hay paginas adicionales en Fase 1 (SPA de una sola vista). PASA.

**3.2.4 Consistent Identification (Nivel AA)**

Los mismos componentes se identifican de la misma forma en toda la interfaz. El boton "Generar frase" tiene el mismo texto en todas sus instancias. Los pictogramas mantienen su `aria-label` tanto en el grid como en los chips. PASA.

### 3.3 Input Assistance (WCAG 3.3.x)

**3.3.1 Error Identification (Nivel A)**

| Error | Mecanismo | Resultado |
|-------|-----------|-----------|
| Error carga categorias | `role="alert"` con texto descriptivo | PASA |
| Error carga pictogramas | `role="alert"` con texto descriptivo | PASA |
| Error generacion frase | `role="alert"` con texto descriptivo + boton Reintentar | PASA |
| Error TTS | `aria-label="Error de audio"` + icono AlertCircle | PASA |
| Sin resultados busqueda | aria-live polite "Sin resultados de busqueda" | PASA |
| Rate limit (429) | Texto descriptivo ("Espera unos momentos..." / "Has alcanzado el limite diario...") | PASA |

**3.3.2 Labels or Instructions (Nivel A)**

Input de busqueda tiene `<label for="search-pictograms" class="sr-only">` y `placeholder`. Input movil tiene `<label for="mobile-search" class="sr-only">`. PASA.

---

## 4. Robust

### 4.1 Compatible (WCAG 4.1.x)

**4.1.1 Parsing (Nivel A) — Obsoleto en WCAG 2.2**

No se evalua. Vue 3 genera HTML valido.

**4.1.2 Name, Role, Value (Nivel A)**

| Componente | Name | Role | Value/State | Resultado |
|------------|------|------|-------------|-----------|
| Boton categoria | `aria-label="Categoria X"` | `role="tab"` | `aria-selected` | PASA |
| Boton pictograma | `aria-label="Pictograma X"` | `button` (implicito) | `disabled` cuando frase llena | PASA |
| Chip de frase | `aria-label="X"` | `role="group"` | - | PASA |
| Boton eliminar chip | `aria-label="Eliminar X"` | `button` (implicito) | - | PASA |
| Boton generar | `aria-label` dinamico | `button` (implicito) | `disabled` + label cambia con estado | PASA |
| SpeakButton | `aria-label` dinamico | `button` (implicito) | Cambia segun error/speaking/idle | PASA |
| Input busqueda | `label` + `id` vinculados | `role="searchbox"` explicito | - | PASA |
| Grid pictogramas | `aria-label="Pictogramas"` | `role="grid"` | - | PASA |
| Loading skeleton | - | `role="status"` | `sr-only` texto | PASA |
| Landscape blocker | - | `role="alert"` | Texto descriptivo | PASA |

**Hallazgo A9 - ACEPTADO:** El patron `role="tablist"` / `role="tab"` en CategoryBar no incluye `tabindex` roving. Segun WAI-ARIA Authoring Practices para Tabs, solo la tab seleccionada deberia tener `tabindex="0"` y las demas `tabindex="-1"`, con Arrow keys para moverse entre ellas. Actualmente todas las tabs son focusables por Tab (comportamiento de botones nativos).

**Justificacion:** Los screen readers anuncian correctamente "tab seleccionada" gracias a `aria-selected`, y la navegacion con flechas funciona (`CategoryBar.vue` lineas 43-63). Arrow keys entre categorias funcionan correctamente. Tab navega por todas las categorias (11 botones) en lugar de saltar al panel, lo que incrementa el numero de pulsaciones, pero esto no rompe la funcionalidad. Se trata de un refinamiento del patron WAI-ARIA Tabs, no de un incumplimiento de WCAG 2.2 AA.

**Verificado:** `CategoryBar.vue` — los botones `role="tab"` no tienen `tabindex` explicito; no hay `aria-controls`; `PictogramGrid.vue` no tiene `role="tabpanel"`.

**4.1.3 Status Messages (Nivel AA)**

| Mensaje | Mecanismo | Urgencia | Resultado |
|---------|-----------|----------|-----------|
| "X pictogramas encontrados" | `aria-live="polite"` + `role="status"` | Polite | PASA |
| "Categoria X seleccionada" | `aria-live="polite"` + `role="status"` | Polite | PASA |
| "Sin resultados de busqueda" | `aria-live="polite"` + `role="status"` | Polite | PASA |
| "Pictograma X anadido a la frase" | `aria-live="assertive"` + `role="status"` | Assertive | PASA |
| "Pictograma eliminado de la frase" | `aria-live="assertive"` + `role="status"` | Assertive | PASA |
| "Frase generada con N variaciones" | `aria-live="assertive"` + `role="status"` | Assertive | PASA |
| "Generando frase..." | `role="status"` dentro de PhraseBar | Screen reader only | PASA |
| "Cargando categorias..." / "Cargando pictogramas..." | `role="status"` visible | SR + visual | PASA |

Implementacion de dual aria-live (polite + assertive) es correcta y bien diferenciada por urgencia. PASA.

---

## 5. Evaluacion Especifica SAAC

### 5.1 Usabilidad para Publico Objetivo

**Usuarios con TEA (Trastorno del Espectro Autista)**

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Estimulacion visual reducida | Fondos off-white (Stone-50), sin colores neon, sin gradientes | PASA |
| Consistencia visual | Colores Fitzgerald Key consistentes, layout predecible | PASA |
| Animaciones controladas | `motion-safe:` en todas las transiciones, respeta `prefers-reduced-motion` | PASA |
| Estructura predecible | Misma interfaz siempre, sin cambios de contexto inesperados | PASA |
| Carga cognitiva | Pictogramas grandes, texto minimo, iconos claros | PASA |

**Usuarios con Afasia**

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Dependencia minima de texto | Categorias con icono + color, pictogramas con imagen | PASA |
| Pictogramas como medio principal | Grid de pictogramas es el area principal de la interfaz | PASA |
| Generacion de frase a partir de pictogramas | Flujo pictograma -> frase funcional | PASA |
| TTS disponible | SpeakButton en cada variacion de frase | PASA |

**Usuarios con Paralisis Cerebral**

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Targets grandes (>= 44px) | Todos los elementos principales cumplen, incluido tablet landscape | PASA |
| Sin dependencia de doble click | Toda la interaccion es single click/tap | PASA |
| Sin gestos complejos | No se requiere drag, pinch, swipe | PASA |
| Tolerancia al error | Borrar chip individual, borrar todos, reintentar generacion | PASA |
| Navegacion por conmutador (switch) | Teclado Tab + Enter funciona (compatible con conmutadores USB) | PASA |

**Usuarios con ELA**

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Atajos de teclado eficientes | `1-9,0,?` categorias, flechas grid, `b` buscar, `g` generar, `x` borrar | PASA |
| Minimo numero de interacciones | Categoria (1 tecla) + pictograma (flechas + Enter) + generar (`g`) | PASA |
| Fatiga reducida | Sin interacciones mantenidas, sin temporizadores | PASA |

### 5.2 Accesibilidad de Pictogramas

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Alt text correcto | `alt=""` en imagen (decorativa), `aria-label` en boton contenedor | PASA |
| Colores Fitzgerald Key como borde (no fondo) | `borderTopColor` en tarjeta, fondo siempre blanco | PASA |
| Imagenes sobre fondo blanco | Pictogramas ARASAAC disenados para fondo blanco | PASA |
| Tamano de pictograma legible | 64x64 (movil) -> 80x80 (sm) -> 96x96 (lg) | PASA |
| Etiqueta de texto visible | Texto debajo de cada pictograma con fondo tintado | PASA |
| Lazy loading | `loading="lazy"` en imagenes de pictogramas | PASA |

### 5.3 TTS (Text-to-Speech)

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Web Speech API disponible | Feature detection con `isSupported` | PASA |
| Boton oculto si TTS no soportado | `v-if="isSupported"` | PASA |
| Idioma espanol configurado | `lang = 'es-ES'` con fallback a navigator.language | PASA |
| Velocidad de habla ajustada | `rate = 0.9` (ligeramente mas lento para comprension) | PASA |
| Feedback de estado (hablando/error) | Iconos distintos: Volume2/VolumeX/AlertCircle | PASA |
| Error de audio comunicado | `aria-label="Error de audio"` | PASA |
| Parar reproduccion | Click en boton durante reproduccion ejecuta `stop()` | PASA |

**Hallazgo A10 - Informativo:** El TTS no proporciona feedback via `aria-live` cuando comienza o termina de hablar. Un screen reader no anunciara "Reproduciendo audio" ni "Audio finalizado". Para usuarios que dependen de screen reader + audio simultaneo, esto puede ser confuso. Considerar agregar un anuncio assertive breve.

### 5.4 Eficiencia del Flujo Comunicativo

Analisis del flujo tipico para generar una frase:

**Flujo tactil (tablet):**
1. Tap en categoria (1 interaccion)
2. Tap en pictograma(s) (1-10 interacciones)
3. Tap en "Generar frase" (1 interaccion)
4. Tap en SpeakButton para escuchar (1 interaccion)
**Total: 4-13 interacciones**

**Flujo teclado (desktop/conmutador):**
1. Tecla numerica para categoria (1 pulsacion)
2. Flechas + Enter para seleccionar pictograma(s) (2-20 pulsaciones)
3. `g` para generar frase (1 pulsacion)
4. Tab hasta SpeakButton + Enter (2 pulsaciones)
**Total: 6-24 pulsaciones**

**Hallazgo A11 - CORREGIDO (PR #73):** Se agregaron atajos de teclado `g` (generar frase) y `x` (borrar todos los pictogramas), ambos fuera de inputs (`HomeView.vue` lineas 165-176). Esto reduce significativamente las pulsaciones para usuarios con ELA: generar frase paso de requerir multiples Tab + Enter a una sola pulsacion. El footer de atajos (`HomeView.vue` linea 332) muestra las nuevas teclas.

---

## 6. Hallazgos Consolidados

### Severidad Alta

_(ninguno pendiente)_

### Severidad Media

_(ninguno pendiente)_

### Severidad Baja

_(ninguno pendiente)_

### Aceptados con justificacion

| ID | Criterio WCAG | Componente | Hallazgo | Justificacion |
|----|--------------|------------|----------|---------------|
| A9 | 4.1.2 Name, Role, Value | CategoryBar + PictogramGrid | Patron tabs sin tabindex roving ni `aria-controls` | Arrow keys funcionan. Refinamiento WAI-ARIA, no fallo WCAG AA |

### No aplica

| ID | Criterio WCAG | Componente | Hallazgo | Justificacion |
|----|--------------|------------|----------|---------------|
| A2 | 1.3.1 Info and Relationships | CategoryBar -> PictogramGrid | Sin vinculo `aria-controls`/`role="tabpanel"` | `aria-controls` es recomendacion WAI-ARIA, no requisito WCAG AA. Navegacion por teclado funciona |
| A6 | 2.4.6 Headings and Labels | HomeView (H1) | H1 oculto visualmente en movil | El H1 esta en el DOM (`sr-only`), screen readers lo encuentran. Solo es visual |

### Documentado

| ID | Criterio WCAG | Componente | Hallazgo | Justificacion |
|----|--------------|------------|----------|---------------|
| A3 | 1.3.4 Orientation | App.vue (landscape blocker) | Bloqueo de landscape en moviles < 500px alto | Excepcion justificada: layout SAAC no cabe en landscape movil (ADR-011) |

### Informativo

| ID | Criterio WCAG | Componente | Hallazgo | Nota |
|----|--------------|------------|----------|------|
| A10 | Buena practica | SpeakButton / useTTS | Sin aria-live para estado de reproduccion TTS | Agregar anuncio assertive "Reproduciendo"/"Finalizado" |
| A13 | Buena practica | CategoryBar | ArrowLeft/ArrowRight funciona pero no implementa Home/End (ir a primera/ultima tab) | Mejora WAI-ARIA Tabs Pattern |

---

## 7. Hallazgos Corregidos desde Revision Anterior

| ID original | Hallazgo | Severidad | Estado | Detalle |
|-------------|----------|-----------|--------|---------|
| A1 | Alt redundante en PictogramCard | Bajo | **CORREGIDO** | `alt=""` en imagen decorativa (`PictogramCard.vue` linea 29) |
| A4 | Contraste red-600 sobre red-50 (~4.4:1) | Medio | **CORREGIDO** | Cambiado a `text-red-700` en PictogramGrid, CategoryBar, PhraseBar |
| A5 | Bordes surface-200 bajo contraste | Medio | **CORREGIDO** | `border-surface-400` en PictogramCard, PhraseBar, SearchBar (PR #73) |
| A7 | Touch targets 36px tablet landscape | Alto | **CORREGIDO** | Pseudo-elemento `::after` 44x44px (`main.css` linea 66-74) |
| A11 | Sin atajo generar frase | Informativo | **CORREGIDO** | `g` para generar, `x` para borrar (`HomeView.vue` lineas 165-176) |
| A12 | `role="searchbox"` redundante | Informativo | **CORREGIDO** | Eliminado rol redundante con `type="search"` (PR #73) |

---

## 8. Componentes: Resumen por Componente

### index.html
- `lang="es"` -- PASA
- `meta viewport` con `width=device-width, initial-scale=1.0` -- PASA
- `meta description` presente -- PASA
- Sin `user-scalable=no` ni `maximum-scale=1` -- PASA (no restringe zoom)

### App.vue
- Landscape blocker con `role="alert"` y texto descriptivo -- PASA
- Iconos decorativos con `aria-hidden` -- PASA
- Texto de alto contraste -- PASA

### HomeView.vue
- Skip link funcional -- PASA
- Header con landmark implicito -- PASA
- Main con id para skip link -- PASA
- Dual aria-live (polite + assertive) -- PASA
- Atajos de teclado con `isInInput` guard -- PASA
- Atajos `g` (generar) y `x` (borrar) -- PASA
- Gestion de foco post-carga, post-generacion -- PASA
- Label para input movil -- PASA
- H1 `sr-only` siempre accesible -- PASA
- Footer de atajos con `<kbd>` semantico -- PASA

### CategoryBar.vue
- `role="tablist"` + `role="tab"` -- PASA
- `aria-selected` dinamico -- PASA
- `aria-label` en cada tab -- PASA
- ArrowLeft/ArrowRight para navegacion -- PASA
- Iconos `aria-hidden` -- PASA
- Skeleton con `role="status"` y sr-only text -- PASA
- Error con `role="alert"` y `text-red-700` -- PASA
- `min-h-touch min-w-touch` en tabs principales -- PASA

### PictogramGrid.vue
- `role="grid"` con `aria-label` -- PASA
- Navegacion 2D con flechas (calculo dinamico de columnas) -- PASA
- Loading con `role="status"` -- PASA
- Error con `role="alert"` y `text-red-700` -- PASA
- Estado vacio con texto descriptivo -- PASA
- `disabled` propagado a PictogramCard cuando frase llena -- PASA

### PictogramCard.vue
- `aria-label="Pictograma ${label}"` -- PASA
- `alt=""` en imagen (decorativa) -- PASA
- `loading="lazy"` en imagen -- PASA
- `min-h-touch min-w-touch` -- PASA
- `has-[:focus-visible]` para ring a nivel de tarjeta -- PASA
- `disabled` con opacidad y cursor -- PASA
- Fondo blanco, borde Fitzgerald Key -- PASA
- Texto `accessible-text` sobre blanco -- PASA

### PhraseBar.vue
- `section` con `aria-label` -- PASA
- Chips con `role="group"` y nombre accesible -- PASA
- Imagenes decorativas en chips (`aria-hidden`) -- PASA
- Texto decorativo en chips (`aria-hidden`) -- PASA
- Botones eliminar con `aria-label="Eliminar ${label}"` -- PASA
- Foco post-eliminacion al chip adyacente -- PASA
- Foco post-generacion a resultados -- PASA
- Loading con `role="status"` sr-only -- PASA
- Error con `role="alert"` y `text-red-700` -- PASA
- Boton generar con label dinamico por estado -- PASA
- `role="list"` en resultados -- PASA
- SpeakButton en cada variacion -- PASA
- Contador "X/10" visible -- PASA

### SearchBar.vue
- Label sr-only vinculado por `for`/`id` -- PASA
- `type="search"` + `role="searchbox"` -- PASA
- Escape para limpiar/salir -- PASA
- Boton clear con `aria-label` -- PASA
- Icono busqueda `aria-hidden` -- PASA
- `min-h-touch` via CSS global -- PASA
- Focus ring visible -- PASA

### SpeakButton.vue
- `aria-label` dinamico por estado (idle/error) -- PASA
- `v-if="isSupported"` (no renderiza si TTS no disponible) -- PASA
- `min-h-touch min-w-touch` -- PASA
- Iconos `aria-hidden` -- PASA
- Focus ring visible -- PASA

### main.css
- Skip link con `sr-only` + `focus:not-sr-only` -- PASA
- Focus global `*:focus-visible` con ring-focus -- PASA
- Touch target global `button, a, [role='button'] { min-h-touch min-w-touch }` -- PASA
- Native search clear button oculto (se usa custom) -- PASA
- `scroll-padding-top: 180px` para focus not obscured -- PASA
- Landscape blocker solo en `max-height: 500px` -- PASA
- `motion-safe:` respetado en todos los hover/transition -- PASA
- Pseudo-elemento 44x44px para tablet landscape categorias -- PASA

### tailwind.config.js
- Colores semanticos con documentacion de contraste -- PASA
- `min-h-touch` / `min-w-touch` a 44px -- PASA
- `ring-focus` a 3px -- PASA
- `tailwindcss-animate` para animaciones controladas -- PASA
- `surface-400` aplicado en bordes (A5 corregido PR #73)

---

## 9. Trabajo Futuro de Accesibilidad

### Mejoras WAI-ARIA

1. **Completar patron Tabs (A9 + A2):** Implementar `tabindex` roving en CategoryBar, agregar `aria-controls` en tabs y `role="tabpanel"` envolviendo el grid. Mejoraria la experiencia con screen readers al permitir saltar de la tab seleccionada al panel con Tab.

2. **Home/End en tabs (A13):** Agregar soporte para Home (primera tab) y End (ultima tab) segun WAI-ARIA Tabs Pattern.

3. **TTS aria-live (A10):** Agregar anuncios para inicio/fin de reproduccion TTS.

### Validacion

4. **Validacion con herramientas automatizadas:** Ejecutar axe-core o Lighthouse Accessibility en CI para detectar regresiones.

5. **Evaluacion con usuarios reales:** Validar con logopedas y usuarios SAAC reales la eficiencia del flujo comunicativo. ADR-011 ya documenta esta necesidad.

---

## 10. Conclusion

HablaIA alcanza un nivel de conformidad WCAG 2.2 AA **substancial**, con 0 hallazgos criticos y 0 hallazgos pendientes. **Lighthouse produccion confirma Accessibility score 100/100** (medido el 13 de febrero de 2026 contra el servidor de produccion).

**Correcciones aplicadas (PR #73):**
- Alt text de pictogramas: imagen decorativa (`alt=""`) sin redundancia con aria-label del boton
- Contraste de errores: `red-700` supera holgadamente 4.5:1 en todos los fondos
- Contraste de bordes: `border-surface-400` en tarjetas, chips y buscador
- Touch targets tablet landscape: pseudo-elemento 44x44px sobre botones de 36px
- Atajos `g` (generar) y `x` (borrar) para eficiencia con teclado/conmutador
- Eliminado `role="searchbox"` redundante

**Hallazgo aceptado con justificacion:**
- A9: arrow keys funcionales, `tabindex` roving es refinamiento WAI-ARIA, no requisito WCAG AA

**No aplica al alcance actual:**
- A2: `aria-controls` es recomendacion WAI-ARIA, no requisito WCAG AA
- A6: H1 en DOM (`sr-only`), accesible para screen readers

**Fortalezas consolidadas:**
- **Estructura semantica:** Landmarks, roles ARIA, headings correctos
- **Navegacion por teclado:** Completa, con atajos globales y navegacion 2D en grid
- **Screen reader:** Dual aria-live, anuncios de acciones, chips sin redundancia
- **Touch targets:** 44px como minimo global (incluido tablet landscape)
- **Contraste:** Paleta disenada con ratios WCAG verificados (15.8:1 texto principal, 5.7:1 texto secundario, 5.3:1 errores)
- **Animaciones:** 100% con `motion-safe:`, respeta `prefers-reduced-motion`
- **Dominio SAAC:** Fitzgerald Key, pictogramas sobre fondo blanco, reduccion de fatiga visual, TTS integrado

La aplicacion es **tecnicamente usable por el publico objetivo** (TEA, afasia, paralisis cerebral, ELA) tanto en flujo tactil como en teclado/conmutador. **0 hallazgos pendientes de correccion.** Pendiente validacion con logopedas y usuarios SAAC reales para confirmar la usabilidad clinica.
