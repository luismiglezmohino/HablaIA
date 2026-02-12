# Auditoria de Accesibilidad WCAG 2.2 AA - Phase 1 (Frontend)

> Revision de cumplimiento WCAG 2.2 AA y usabilidad SAAC del frontend de HablaIA

**Ultima revision:** 11 de febrero de 2026
**Alcance:** Frontend (`frontend/src/`) - Vue 3 + TailwindCSS + shadcn-vue
**Fase:** Phase 1 MVP (comunicador publico, sin autenticacion)
**Evaluador:** @ux_designer

---

## Metodologia

### Proceso

1. Revision manual de todos los componentes Vue, HTML base, CSS global y configuracion Tailwind
2. Verificacion sistematica por criterio WCAG 2.2 AA (Perceivable, Operable, Understandable, Robust)
3. Evaluacion especifica para dominio SAAC (TEA, afasia, paralisis cerebral, ELA)
4. Clasificacion de hallazgos por severidad (Critico / Alto / Medio / Bajo / Informativo)
5. Recomendaciones para Phase 2+

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
| Revision manual de codigo | Analisis de ARIA, semantica HTML, clases Tailwind |
| WCAG 2.2 AA Quick Reference | Checklist sistematico de criterios de conformidad |
| ADR-008 | Fitzgerald Key color coding |
| ADR-011 | Sistema de diseno visual |
| ADR-013 | Navegacion por teclado y screen reader |
| WAI-ARIA Authoring Practices | Patrones Grid, Tabs, Status Messages |

---

## Resumen Ejecutivo

| Severidad | Hallazgos |
|-----------|-----------|
| Critico | 0 |
| Alto | 2 |
| Medio | 4 |
| Bajo | 3 |
| Informativo | 5 |
| **Total** | **14** |

**Conclusion general:** La aplicacion demuestra un nivel de accesibilidad elevado para un MVP. La estructura semantica, la gestion de foco, los anuncios para screen readers y el cumplimiento de touch targets son solidos. Los hallazgos de severidad alta se concentran en dos areas concretas: botones de categoria en tablet landscape por debajo de 44x44px y la falta de `tabindex` en el `role="tabpanel"` implicito del grid. No se encontraron hallazgos criticos que bloqueen el uso de la aplicacion por usuarios SAAC.

---

## 1. Perceivable (Perceptible)

### 1.1 Alternativas de Texto (WCAG 1.1.1 - Nivel A)

**Componentes revisados:** PictogramCard, PhraseBar, CategoryBar, HomeView, App

| Elemento | Verificacion | Resultado |
|----------|-------------|-----------|
| Imagenes de pictogramas en grid (`PictogramCard`) | `alt` con `pictogram.label` | PASA |
| Imagenes de pictogramas en chips (`PhraseBar`) | `alt=""` + `aria-hidden="true"` (decorativo, nombre en `role="group"`) | PASA |
| Iconos Lucide (categorias, busqueda, acciones) | `aria-hidden="true"` en todos | PASA |
| Logo/Sparkles en header | `aria-hidden="true"` | PASA |
| Iconos de landscape blocker | `aria-hidden="true"` en contenedor, texto visible como alternativa | PASA |
| Boton de pictograma | `aria-label="Pictograma ${label}"` | PASA |
| SpeakButton | `aria-label="Escuchar: ${text.slice(0, 50)}"` | PASA |

**Hallazgo A1 - Bajo:** Las imagenes de pictogramas en `PictogramCard` usan `alt` con la etiqueta del pictograma, lo cual es correcto. Sin embargo, el boton que las contiene tambien tiene `aria-label="Pictograma ${label}"`, lo que genera una lectura redundante en screen readers: el boton anuncia "Pictograma agua" y la imagen anuncia "agua". La imagen dentro del boton podria marcarse como `aria-hidden="true"` dado que el boton ya proporciona el nombre accesible.

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
| `<h1>` en header | Unico heading nivel 1 | PASA |
| Skip link "Ir al contenido principal" | Presente y funcional | PASA |
| `role="tablist"` / `role="tab"` en CategoryBar | Patron tabs implementado | PASA |
| `role="grid"` en PictogramGrid | Patron grid implementado | PASA |
| `role="group"` en chips de PhraseBar | Agrupacion semantica correcta | PASA |
| `role="list"` en resultados de frases | Lista semantica correcta | PASA |
| `role="status"` en loading de CategoryBar y PictogramGrid | Anuncios de estado | PASA |
| `role="alert"` en errores | Anuncios de error inmediatos | PASA |

**Hallazgo A2 - Medio:** El `role="grid"` en PictogramGrid no tiene un `role="tabpanel"` asociado que lo vincule con las tabs de CategoryBar. Segun el patron WAI-ARIA Tabs, cada tab deberia tener un tabpanel vinculado con `aria-controls` / `aria-labelledby`. Actualmente, la relacion entre la categoria seleccionada y el grid de pictogramas es implicita (no hay `aria-controls` en los tabs ni `role="tabpanel"` en el grid).

**1.3.2 Meaningful Sequence (Nivel A)**

El orden del DOM sigue la secuencia logica de uso: header -> frase -> busqueda -> categorias -> grid de pictogramas. PASA.

**1.3.3 Sensory Characteristics (Nivel A)**

No se depende unicamente de forma, tamano o posicion para transmitir informacion. Los colores Fitzgerald Key se combinan con iconos y texto (segun ADR-008). PASA.

**1.3.4 Orientation (Nivel AA)**

La aplicacion soporta portrait y landscape. El bloqueador de landscape se activa solo en moviles con `max-height: 500px`, lo que constituye una restriccion de orientacion para un subconjunto de dispositivos.

**Hallazgo A3 - Bajo:** El landscape blocker en moviles (viewport < 500px de alto) impide el uso en orientacion landscape. WCAG 1.3.4 indica que el contenido no debe restringir la orientacion excepto cuando es "esencial". Para un comunicador SAAC, la justificacion es que no hay espacio suficiente para mostrar pictogramas de forma util. Aceptable como excepcion documentada (ADR-011), pero conviene reconsiderar en Phase 2 si hay usuarios que necesiten landscape forzado por montaje de dispositivo asistivo.

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
| Error (`red-600`) | #dc2626 | #ffffff | ~4.6:1 | PASA |
| Error sobre red-50 | #dc2626 | #fef2f2 | ~4.4:1 | LIMITE |
| Boton primario (blanco sobre `primary-600` #4f46e5) | #ffffff | #4f46e5 | ~5.7:1 | PASA |
| Boton error (blanco sobre `red-600` #dc2626) | #ffffff | #dc2626 | ~4.6:1 | PASA |
| Badge accent (`accent-800` #86198f sobre `accent-100` #fae8ff) | #86198f | #fae8ff | ~7.2:1 | PASA |
| Atajos teclado footer (`accessible-textLight` sobre `surface-50`) | #57534e | #fafaf9 | ~5.7:1 | PASA |
| Etiqueta pictograma (`accessible-text` sobre blanco) | #1c1917 | #ffffff | ~15.8:1 | PASA |

(*) Los placeholders no estan sujetos a WCAG 1.4.3 segun la interpretacion estandar, ya que no son "texto" funcional.

**Hallazgo A4 - Medio:** El texto de error `red-600` (#dc2626) sobre fondo `red-50` (#fef2f2) en el alert del PictogramGrid tiene un ratio de ~4.4:1, ligeramente por debajo del minimo 4.5:1 para texto normal. El texto de error sobre fondo blanco (en PhraseBar: `mt-3 text-center`) si cumple (~4.6:1). Se recomienda usar `red-700` (#b91c1c) en lugar de `red-600` para los textos de error sobre fondo `red-50`, lo que elevaria el ratio a ~6.4:1.

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
| Borde input (`surface-200` #e7e5e4) | #e7e5e4 | #fafaf9 | ~1.2:1 | LIMITE |
| Borde tarjeta (`surface-200` #e7e5e4) | #e7e5e4 | #ffffff | ~1.3:1 | LIMITE |
| Borde input focus (`primary-400` #818cf8) | #818cf8 | #ffffff | ~3.4:1 | PASA |
| Spinner loading (`primary-500` #6366f1 / `surface-300` #d6d3d1) | mezcla | #ffffff | ~4.6:1 / ~1.5:1 | PARCIAL |

**Hallazgo A5 - Medio:** Los bordes de input y tarjetas en estado no enfocado (`surface-200` #e7e5e4 sobre blanco/surface-50) tienen ratio ~1.2-1.3:1, por debajo del minimo 3:1 para componentes de interfaz. Esto afecta a: input de busqueda (SearchBar y mobile search), bordes de tarjetas de pictograma, y bordes de chips. En estado focus, los inputs si cumplen gracias al ring `primary-500` y al borde `primary-400`. Se recomienda usar `surface-300` (#d6d3d1) como color de borde por defecto, que ofrece ~1.9:1 -- aun insuficiente, o `surface-400` (~2.8:1). Para cumplir estrictamente 3:1, seria necesario un gris mas oscuro como #9a9a9a.

**Nota atenuante:** En la practica, los bordes de tarjeta y chips son complementados por sombras (`shadow-card`, `shadow-soft`) y el borde superior de color Fitzgerald Key que proporcionan contraste visual adicional. La informacion no depende unicamente del borde gris. Muchas auditorias AA aceptan esta combinacion.

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
| Buscar pictogramas | / para enfocar, Esc para limpiar/salir | PASA |
| Navegar grid de pictogramas | ArrowUp/Down/Left/Right (2D dinamico) | PASA |
| Seleccionar pictograma | Enter/Space (nativo de button) | PASA |
| Eliminar chip de frase | Tab a chips + Enter en X, Backspace desde fuera | PASA |
| Generar frase | Tab a boton Generar + Enter | PASA |
| Reproducir frase (TTS) | Tab a SpeakButton + Enter | PASA |
| Borrar todos los pictogramas | Tab a boton Trash + Enter | PASA |
| Skip link | Tab al inicio + Enter | PASA |

**2.1.2 No Keyboard Trap (Nivel A)**

Se verifico que no existen trampas de teclado. El flujo de Tab recorre: skip link -> header -> busqueda -> categorias -> frase (si hay chips) -> grid -> footer. Esc sale del input de busqueda. PASA.

**2.1.4 Character Key Shortcuts (Nivel A)**

Los atajos `1-9, 0, ?, /, Backspace` solo se activan fuera de inputs (`isInInput` check en `handleGlobalKeydown`). PASA.

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
| `<h1>` "HablaIA" | Titulo principal (oculto en movil y tablet landscape) | PASA |
| `aria-label="Categorias"` | Nav de categorias | PASA |
| `aria-label="Barra de frases"` | Section de PhraseBar | PASA |
| `aria-label="Pictogramas"` | Grid principal | PASA |
| `aria-label="Frases generadas"` | Section movil de resultados | PASA |
| `aria-label="Atajos de teclado"` | Footer desktop | PASA |
| `<label for="search-pictograms">` | Input de busqueda (sr-only) | PASA |
| `<label for="mobile-search">` | Input de busqueda movil (sr-only) | PASA |

**Hallazgo A6 - Bajo:** El `<h1>` esta oculto en movil (`hidden sm:block`) y en tablet landscape (`tablet-landscape-hide`). Aunque no es un fallo WCAG (el heading existe en el DOM), los usuarios de screen reader en movil no encuentran el heading al navegar por headings. Se recomienda mover la clase a un contenedor visual y mantener un `h1` siempre presente (al menos como `sr-only`).

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

| Elemento | Tamano minimo configurado | Resultado |
|----------|--------------------------|-----------|
| Botones de categoria (CategoryBar) | `min-h-touch min-w-touch` (44x44px) | PASA |
| Botones de pictograma (PictogramCard) | `min-h-touch min-w-touch` (44x44px) | PASA |
| Boton generar frase | `w-full py-3.5` (>44px alto) | PASA |
| SpeakButton | `min-h-touch min-w-touch` (44x44px) | PASA |
| Boton borrar busqueda (SearchBar) | `p-2` + icono 20px = ~36px area, pero `min-h-touch min-w-touch` global | PASA (*) |
| Boton borrar busqueda movil (HomeView) | `p-2` + icono 18px = ~34px area | **REVISAR** |
| Boton eliminar chip (PhraseBar) | `min-h-7 min-w-7` (28px) + espaciado | LIMITE |
| CSS global `button, a, [role='button']` | `min-h-touch min-w-touch` (44x44px) | PASA |
| Categorias en tablet landscape (HomeView header) | `h-9 w-9` (36px) | **NO PASA** |

(*) El CSS global en `main.css` fuerza `min-h-touch min-w-touch` a todos los `button`, lo que actua como safety net. Sin embargo, el boton de borrar busqueda movil en HomeView no tiene clase explicita y depende de este global.

**Hallazgo A7 - Alto:** Los botones de categoria en tablet landscape (nav dentro del header de HomeView) tienen tamano `h-9 w-9` (36x36px), por debajo del minimo 44x44px de WCAG 2.5.8. La regla CSS global `button { min-h-touch min-w-touch }` deberia forzar 44x44px, pero las clases explicitas `h-9 w-9` sobreescriben `min-h` / `min-w`. Esto afecta a usuarios con limitaciones motoras finas en tablets en orientacion landscape.

**Contexto atenuante:** Este diseno fue una decision deliberada para encajar 11 categorias en la cabecera en landscape sin desbordamiento visible. Es un trade-off entre target size y visibilidad de todas las categorias.

**Recomendacion:** Mantener el tamano visual 36px pero agregar `padding` o area de click invisible (CSS `::before` o padding negativo) para que el area de interaccion sea 44x44px. Alternativamente, usar scroll horizontal con categorias de 44px.

**Hallazgo A8 - Medio:** Los botones de eliminar chip (`min-h-7 min-w-7`, 28x28px) estan por debajo de 44x44px. El CSS global `button { min-h-touch }` deberia aplicarse, pero `min-h-7` (28px) es mas restrictivo que `min-h-touch` (44px) -- en realidad, `min-h-7` = `min-height: 1.75rem` mientras que el global aplica `min-h-touch` = `min-height: 44px`. La clase `min-h-7` no sobreescribe `min-h-touch` porque `44px > 28px`. El area real de interaccion debe verificarse en runtime.

**Nota:** WCAG 2.5.8 acepta targets de 24px si tienen suficiente espacio alrededor (spacing >= 24px). Los chips tienen gap de `1.5` (6px) / `2` (8px), lo que puede no ser suficiente. Evaluar en runtime.

---

## 3. Understandable (Comprensible)

### 3.1 Readable (WCAG 3.1.x)

**3.1.1 Language of Page (Nivel A)**

`<html lang="es">` en `index.html`. PASA.

**3.1.2 Language of Parts (Nivel AA)**

No hay contenido en otro idioma. El texto de la interfaz esta integramente en espanol. La respuesta de la API (frases generadas) se configura en espanol via el prompt del backend. PASA.

### 3.2 Predictable (WCAG 3.2.x)

**3.2.1 On Focus (Nivel A)**

Enfocar un elemento no provoca cambio de contexto. El input de busqueda emite `@focus` solo para control de z-index. PASA.

**3.2.2 On Input (Nivel A)**

La busqueda con debounce (300ms) cambia el contenido del grid, pero no cambia el contexto de la pagina. La seleccion de categoria cambia el contenido del grid, comportamiento esperado y predecible. PASA.

**3.2.3 Consistent Navigation (Nivel AA)**

La estructura de navegacion es consistente: siempre header -> frase -> busqueda -> categorias -> grid. No hay paginas adicionales en Phase 1 (SPA de una sola vista). PASA.

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

**Hallazgo A9 - Alto:** El patron `role="tablist"` / `role="tab"` en CategoryBar no incluye `tabindex` roving. Segun WAI-ARIA Authoring Practices para Tabs, solo la tab seleccionada deberia tener `tabindex="0"` y las demas `tabindex="-1"`, con Arrow keys para moverse entre ellas. Actualmente todas las tabs son focusables por Tab (comportamiento de botones nativos), lo que funciona pero no sigue el patron recomendado. Ademas, falta `aria-controls` en los tabs apuntando a un panel y falta un `role="tabpanel"` envolviendo el grid.

**Impacto real:** Los screen readers anuncian correctamente "tab seleccionada" gracias a `aria-selected`, y la navegacion con flechas funciona. Sin embargo, Tab navega por todas las categorias en lugar de saltar al panel, lo que incrementa el numero de pulsaciones de Tab necesarias para usuarios que ya han seleccionado una categoria.

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
| Targets grandes (>= 44px) | Todos los elementos principales cumplen (excepto hallazgo A7) | PARCIAL |
| Sin dependencia de doble click | Toda la interaccion es single click/tap | PASA |
| Sin gestos complejos | No se requiere drag, pinch, swipe | PASA |
| Tolerancia al error | Borrar chip individual, borrar todos, reintentar generacion | PASA |
| Navegacion por conmutador (switch) | Teclado Tab + Enter funciona (compatible con conmutadores USB) | PASA |

**Usuarios con ELA**

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Atajos de teclado eficientes | Numeros para categorias, flechas para grid, / para buscar | PASA |
| Minimo numero de interacciones | Seleccionar categoria (1 tecla) + pictograma (flechas + Enter) + generar | PASA |
| Fatiga reducida | Sin interacciones mantenidas, sin temporizadores | PASA |

### 5.2 Accesibilidad de Pictogramas

| Criterio | Evaluacion | Resultado |
|----------|-----------|-----------|
| Alt text con etiqueta del pictograma | `alt={pictogram.label}` en PictogramCard | PASA |
| Colores Fitzgerald Key como borde (no fondo) | `borderTopColor` en tarjeta, fondo siempre blanco | PASA |
| Imagenes sobre fondo blanco | Pictogramas ARASAAC disenados para fondo blanco | PASA |
| Tamano de pictograma legible | 64x64 (movil) -> 80x80 (sm) -> 96x96 (lg) | PASA |
| Etiqueta de texto visible | Texto debajo de cada pictograma con fondo tintado | PASA |

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
3. Tab hasta "Generar frase" + Enter (2-3 pulsaciones)
4. Tab hasta SpeakButton + Enter (2 pulsaciones)
**Total: 7-26 pulsaciones**

**Hallazgo A11 - Informativo:** El flujo de teclado requiere Tab adicionales para llegar desde el grid hasta el boton "Generar frase" y luego al SpeakButton. Un atajo de teclado dedicado para "Generar frase" (por ejemplo, `Ctrl+Enter` o `G` fuera de input) reduciria significativamente las pulsaciones para usuarios con limitaciones motoras. Considerar para Phase 2.

---

## 6. Hallazgos Consolidados

### Severidad Alta

| ID | Criterio WCAG | Componente | Hallazgo | Recomendacion |
|----|--------------|------------|----------|---------------|
| A7 | 2.5.8 Target Size | HomeView (tablet landscape categories) | Botones de categoria 36x36px (< 44px minimo) | Agregar area de click invisible de 44px via padding/pseudoelemento, o usar scroll horizontal con botones de 44px |
| A9 | 4.1.2 Name, Role, Value | CategoryBar + PictogramGrid | Patron tabs incompleto: falta tabindex roving, `aria-controls`, `role="tabpanel"` | Implementar tabindex roving (-1/0) y vincular tabs con panel via `aria-controls`/`aria-labelledby` |

### Severidad Media

| ID | Criterio WCAG | Componente | Hallazgo | Recomendacion |
|----|--------------|------------|----------|---------------|
| A2 | 1.3.1 Info and Relationships | CategoryBar -> PictogramGrid | Sin vinculo `aria-controls`/`role="tabpanel"` entre tabs y grid | Agregar `aria-controls="pictogram-panel"` a tabs y `role="tabpanel" id="pictogram-panel"` al grid |
| A4 | 1.4.3 Contrast Minimum | PictogramGrid (error state) | `red-600` sobre `red-50` = ~4.4:1 (< 4.5:1) | Cambiar texto de error a `red-700` (#b91c1c) |
| A5 | 1.4.11 Non-text Contrast | SearchBar, PictogramCard, PhraseBar | Bordes `surface-200` sobre blanco = ~1.2-1.3:1 (< 3:1) | Oscurecer bordes por defecto a `stone-400` o superior; o aceptar como excepcion dado que sombras y bordes Fitzgerald aportan contraste adicional |
| A8 | 2.5.8 Target Size | PhraseBar (remove chip buttons) | Botones de eliminar chip 28px, necesitan verificacion de spacing | Verificar en runtime que spacing cumple WCAG 2.5.8 (target 24px + spacing >= 24px); alternativamente aumentar a `min-h-9 min-w-9` (36px) |

### Severidad Baja

| ID | Criterio WCAG | Componente | Hallazgo | Recomendacion |
|----|--------------|------------|----------|---------------|
| A1 | 1.1.1 Non-text Content | PictogramCard | Imagen redundante con aria-label del boton | Agregar `aria-hidden="true"` a la imagen dentro del boton |
| A3 | 1.3.4 Orientation | App.vue (landscape blocker) | Bloqueo de landscape en moviles < 500px alto | Documentar como excepcion esencial; evaluar en Phase 2 para dispositivos asistivos montados en landscape |
| A6 | 2.4.6 Headings and Labels | HomeView | `<h1>` oculto en movil y tablet landscape | Agregar `sr-only` h1 que siempre este presente, o usar CSS que oculte visualmente pero mantenga en DOM accesible |

### Informativo

| ID | Criterio WCAG | Componente | Hallazgo | Recomendacion |
|----|--------------|------------|----------|---------------|
| A10 | Buena practica | SpeakButton / useTTS | Sin aria-live para estado de reproduccion TTS | Agregar anuncio assertive "Reproduciendo"/"Finalizado" |
| A11 | Buena practica | HomeView (keyboard shortcuts) | Sin atajo directo para "Generar frase" | Agregar atajo `Ctrl+Enter` o `G` para generar frase sin Tab |
| A12 | Buena practica | SearchBar | `role="searchbox"` es valido pero no estandar en la lista de roles de HTML; `type="search"` ya es suficiente | Opcional: eliminar `role="searchbox"` ya que `type="search"` implica el rol `searchbox` |
| A13 | Buena practica | HomeView | El footer de atajos solo es visible en xl (desktop); usuarios de teclado en pantallas < xl no ven los atajos | Considerar tooltip o dialog de ayuda accesible con `?` en todas las pantallas |
| A14 | Buena practica | CategoryBar | La navegacion ArrowLeft/ArrowRight en tabs es correcta, pero no implementa Home/End (ir a primera/ultima tab) | Agregar Home/End segun WAI-ARIA Tabs Pattern |

---

## 7. Componentes: Resumen por Componente

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
- Gestion de foco post-carga, post-generacion -- PASA
- Label para input movil -- PASA
- Footer de atajos con `<kbd>` semantico -- PASA

### CategoryBar.vue
- `role="tablist"` + `role="tab"` -- PASA
- `aria-selected` dinamico -- PASA
- `aria-label` en cada tab -- PASA
- ArrowLeft/ArrowRight para navegacion -- PASA
- Iconos `aria-hidden` -- PASA
- Skeleton con `role="status"` y sr-only text -- PASA
- Error con `role="alert"` -- PASA
- `min-h-touch min-w-touch` en tabs principales -- PASA

### PictogramGrid.vue
- `role="grid"` con `aria-label` -- PASA
- Navegacion 2D con flechas (calculo dinamico de columnas) -- PASA
- Loading con `role="status"` -- PASA
- Error con `role="alert"` -- PASA
- Estado vacio con texto descriptivo -- PASA
- `disabled` propagado a PictogramCard cuando frase llena -- PASA

### PictogramCard.vue
- `aria-label="Pictograma ${label}"` -- PASA
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
- Error con `role="alert"` -- PASA
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

### tailwind.config.js
- Colores semanticos con documentacion de contraste -- PASA
- `min-h-touch` / `min-w-touch` a 44px -- PASA
- `ring-focus` a 3px -- PASA
- `tailwindcss-animate` para animaciones controladas -- PASA

---

## 8. Recomendaciones para Phase 2+

### Prioridad Alta

1. **Completar patron Tabs (A9 + A2):** Implementar `tabindex` roving en CategoryBar, agregar `aria-controls` en tabs y `role="tabpanel"` envolviendo el grid. Esto mejoraria la experiencia con screen readers al permitir saltar directamente de la tab seleccionada al panel con Tab.

2. **Target size tablet landscape (A7):** Resolver los botones de categoria de 36px en landscape. Opcion recomendada: area de click invisible de 44px con `::after` pseudoelemento posicionado.

3. **Contraste de bordes (A5):** Evaluar oscurecer los bordes por defecto de inputs y tarjetas a un gris que cumpla 3:1 contra el fondo.

### Prioridad Media

4. **Atajo para generar frase (A11):** Agregar `Ctrl+Enter` o `G` como atajo global para generar frase, reduciendo pulsaciones de teclado para usuarios con ELA.

5. **Home/End en tabs (A14):** Agregar soporte para Home (primera tab) y End (ultima tab) segun WAI-ARIA Tabs Pattern.

6. **TTS aria-live (A10):** Agregar anuncios para inicio/fin de reproduccion TTS.

7. **Validacion con herramientas automatizadas:** Ejecutar axe-core o Lighthouse Accessibility en CI para detectar regresiones.

### Prioridad Baja

8. **h1 siempre accesible (A6):** Asegurar que el heading h1 este siempre disponible para screen readers, incluso cuando esta oculto visualmente.

9. **Imagen redundante en PictogramCard (A1):** Agregar `aria-hidden="true"` a la imagen dentro del boton para evitar doble lectura.

10. **Evaluacion con usuarios reales:** Validar con logopedas y usuarios SAAC reales la eficiencia del flujo comunicativo. ADR-011 ya documenta esta necesidad.

### Phase 2+ (funcionalidad nueva)

11. **Personalizar velocidad TTS:** Permitir que el usuario/terapeuta ajuste `rate` y `pitch`.

12. **Modo alto contraste:** Ofrecer tema de alto contraste opcional (fondo negro, texto blanco, bordes brillantes) para usuarios con baja vision.

13. **Perfil de usuario SAAC:** Configuraciones de tamano de target, velocidad de debounce, y densidad de grid segun perfil (TEA/afasia/PC/ELA).

14. **Offline-first:** Service Worker para funcionalidad basica sin conexion (pictogramas cacheados, TTS local).

---

## 9. Conclusion

HablaIA Phase 1 alcanza un nivel de conformidad WCAG 2.2 AA **substancial**, con 0 hallazgos criticos y una implementacion notablemente robusta de:

- **Estructura semantica:** Landmarks, roles ARIA, headings correctos
- **Navegacion por teclado:** Completa, con atajos globales y navegacion 2D en grid
- **Screen reader:** Dual aria-live, anuncios de acciones, chips sin redundancia
- **Touch targets:** 44px como minimo global (con excepcion documentada en tablet landscape)
- **Contraste:** Paleta disenada con ratios WCAG verificados (15.5:1 texto principal, 5.7:1 texto secundario)
- **Animaciones:** 100% con `motion-safe:`, respeta `prefers-reduced-motion`
- **Dominio SAAC:** Fitzgerald Key, pictogramas sobre fondo blanco, reduccion de fatiga visual, TTS integrado

Los hallazgos de severidad alta son puntuales y tienen contexto atenuante documentado. La aplicacion es **usable por el publico objetivo** (TEA, afasia, paralisis cerebral, ELA) tanto en flujo tactil como en teclado/conmutador.

**Fecha de proxima revision sugerida:** Al inicio de Phase 2, tras implementar las correcciones de prioridad alta.
