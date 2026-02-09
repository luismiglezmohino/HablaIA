# ADR-011: Sistema de Diseno Visual HablaIA

**Estado:** Aceptado
**Fecha:** 2026-02-09
**Contexto:** HablaIA - Comunicador SAAC con IA

## Contexto

La interfaz actual es funcional pero visualmente basica (aspecto 2015). Para la presentacion del TFM y para crear una identidad de producto diferenciada, es necesario un rediseno visual moderno. Los usuarios (TEA, afasia, paralisis cerebral, ELA) utilizan el comunicador durante muchas horas al dia, por lo que la reduccion de la fatiga visual es critica. La aplicacion debe diferenciarse de los comunicadores SAAC existentes en el mercado respetando al mismo tiempo los estandares SAAC.

## Decision

### Paleta de Colores

- **Primary: Indigo** (`#6366f1` / `#4f46e5`) en lugar de azul generico -- diferencia HablaIA de otras apps SAAC, mejor contraste WCAG (5.66:1 vs 4.56:1 para texto blanco sobre botones)
- **Accent: Fuchsia** para elementos relacionados con IA (badges de fuente) -- `fuchsia-100` bg + `fuchsia-800` text (ratio 9.4:1)
- **Surfaces: Stone** (gris calido `#fafaf9`) en lugar de blanco puro -- reduce fatiga visual y deslumbramiento para uso prolongado
- **Text: Stone-900** (`#1c1917`) en lugar de negro puro -- contraste mas suave (15.5:1) que aun excede WCAG AA

### Tipografia

- **Inter** (`@fontsource/inter`, pesos 400-700, self-hosted) -- alta legibilidad en todos los tamanos, consistente entre dispositivos, optimizada para pantallas. Autoalojada para disponibilidad offline
- `antialiased` + `font-smoothing` aplicados globalmente
- Las etiquetas usan `text-base` donde es posible en lugar de `text-sm` para reducir la fatiga ocular

### Estrategia Anti-Fatiga Visual

1. Fondos off-white calidos (Stone-50) reducen el deslumbramiento de pantalla
2. Colores de texto suaves (Stone-900, no negro puro) reducen el contraste agresivo
3. Saturacion de color moderada -- sin colores neon ni vividos
4. Espaciado generoso entre elementos interactivos
5. Colores Fitzgerald Key solo como acentos de borde, nunca como fondos completos (segun ADR-008)

### Politica de Animaciones

- TODAS las transiciones y transforms DEBEN usar el prefijo `motion-safe:` de Tailwind
- Respeta la configuracion del sistema `prefers-reduced-motion` (critico para TEA y sensibilidades vestibulares)
- Elevacion maxima en hover: 4px (`-translate-y-1`)
- Duracion: 200ms para todas las transiciones
- `active:scale-[0.98]` solo en botones grandes (Generar), no en botones de icono pequenos

### Componentes shadcn-vue

- **Badge** -- para etiquetas de fuente ("generated", "cache") en PhraseBar
- **Skeleton** -- para estados de carga en CategoryBar y PictogramGrid
- Construidos sobre Radix Vue (ya instalado) -- headless, compatibles con WAI-ARIA

### Estrategia Responsive: Tres Configuraciones Independientes

La interfaz se adapta a tres escenarios de uso con configuraciones independientes que nunca interfieren entre si:

**1. Movil (< 640px)**
- Busqueda en la cabecera (input siempre visible)
- CategoryBar como tabs con wrap horizontal
- PhraseBar con chips en wrap vertical (`max-h-24`, scroll vertical)
- Resultados de frases fuera del sticky (mas espacio)
- Auto-scroll vertical al anadir pictogramas

**2. Tablet portrait (sm: 640px+)**
- Busqueda debajo de PhraseBar (SearchBar componente completo)
- CategoryBar con texto + iconos
- PhraseBar con chips en wrap vertical (`max-h-32`)
- Resultados de frases dentro de PhraseBar

**3. Tablet landscape (640px-1279px en orientacion landscape)**
- Layout compacto para maximizar espacio con teclado virtual abierto
- Busqueda + categorias (solo iconos) en la cabecera, en una sola linea
- PhraseBar inline: chips en tira horizontal con scroll (`overflow-x: auto`) + contador + borrar + generar a la derecha
- SearchBar y CategoryBar completos ocultos (`tablet-landscape-hide`)
- Titulo "HablaIA" oculto para ganar espacio

**Implementacion tecnica:** Se usa una media query CSS custom `@media (orientation: landscape) and (min-width: 640px) and (max-width: 1279px)` con clases utilitarias propias (`tablet-landscape-show`, `tablet-landscape-hide`, `tablet-landscape-hstrip`, `tablet-landscape-inline`, etc.) para aislar completamente los estilos landscape de los breakpoints Tailwind estandar. Esto evita efectos colaterales entre configuraciones.

**Justificacion SAAC:** La adaptacion por espacio disponible es la estrategia correcta para comunicadores SAAC. El objetivo es maximizar la visibilidad del grid de pictogramas (la herramienta principal de comunicacion) en cada contexto. No se busca unificar las interfaces artificialmente -- cada configuracion prioriza la eficiencia comunicativa segun las restricciones del dispositivo.

### Proteccion contra Landscape en Movil

En moviles en landscape (`max-height: 500px`) se muestra un bloqueador a pantalla completa que solicita rotar el dispositivo. La altura disponible es insuficiente para mostrar pictogramas de forma util en comunicacion SAAC.

### Cumplimiento WCAG 2.2

- **2.4.11 Focus Not Obscured (AA):** `scroll-padding-top` en html para evitar que la cabecera sticky oculte elementos enfocados
- **2.5.8 Target Size Minimum (AA):** Todos los botones 44x44px, botones de eliminar chip 28x28px (`min-h-7 min-w-7`) con espaciado suficiente
- **Focus ring:** 3px Indigo-500 con offset-2 (excede los requisitos AAA del criterio 2.4.13: 2px y contraste 3:1)

## Validacion Pendiente

Esta interfaz ha sido disenada aplicando principios de accesibilidad (WCAG 2.1 AA), estandares SAAC (Fitzgerald Key, targets tactiles, reduccion de fatiga visual) e investigacion publicada sobre consideraciones visuales para soportes AAC. Sin embargo, **debe ser validada por un/a logopeda (terapeuta del habla y lenguaje)** antes de su uso clinico o terapeutico. Los criterios de diseno visual para comunicacion aumentativa requieren evaluacion profesional con usuarios finales reales en contextos de uso autenticos.

## Consecuencias

### Positivas

- Identidad visual unica para HablaIA (indigo+fuchsia vs azul/verde de competidores)
- Fatiga visual reducida para uso prolongado de SAAC
- Mejores ratios de contraste WCAG que la paleta azul anterior
- Todas las animaciones respetan las preferencias de movimiento del usuario
- Apariencia profesional para la presentacion del TFM

### Negativas

- La fuente Inter anade ~100KB a la carga inicial (solo subset latin via woff2)
- El cambio inicial de paleta requirio actualizar las clases de los componentes (coste unico). Futuros cambios de paleta solo requieren modificar `tailwind.config.js` gracias a los tokens semanticos (`primary-*`, `surface-*`, `accent-*`)

### Mitigaciones

- Fuente autoalojada via `@fontsource/inter` (incluida en el bundle, sin dependencia de CDN externo)
- Cadena de fuentes de sistema como fallback (`system-ui`, `-apple-system`, `sans-serif`)
- Solo se descargan los subsets necesarios (latin ~97KB woff2 para 4 pesos)

## Alternativas Consideradas

1. **Mantener la paleta azul generica:** Descartada -- indistinguible de otras apps SAAC, peor contraste
2. **Usar fuente Nunito:** Descartada -- aspecto mas redondeado/infantil, peor legibilidad en tamanos pequenos
3. **Fondos con gradiente en PhraseBar:** Descartada por @ux_designer -- el gradiente anade complejidad visual sin beneficio SAAC, se prefiere un tinte unico para usuarios con TEA
4. **Fondos blancos puros:** Descartado -- causa fatiga visual durante uso prolongado, la investigacion respalda el uso de off-white calido para ayudas de comunicacion basadas en pantalla

## Referencias

- [WCAG 2.2](https://www.w3.org/WAI/WCAG22/Understanding/) - Web Content Accessibility Guidelines
- ADR-008: Modified Fitzgerald Key como Sistema de Colores para Categorias SAAC
- ADR-010: Feedback Inline en Lugar de Toasts para Interfaces SAAC
- [Visual considerations for AAC supports](https://www.perkins.org/) - Perkins School for the Blind
- [shadcn-vue](https://www.shadcn-vue.com/) - Componentes UI basados en Radix Vue