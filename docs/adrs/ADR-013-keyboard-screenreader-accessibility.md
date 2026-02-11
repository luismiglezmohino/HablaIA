# ADR-013: Navegacion por Teclado y Soporte Screen Reader

**Estado:** Aceptado
**Fecha:** 2026-02-10
**Contexto:** HablaIA - Comunicador SAAC con IA

## Contexto

HablaIA es un comunicador pictografico para usuarios con TEA, afasia, paralisis cerebral y ELA. Muchos de estos usuarios dependen de teclado, conmutadores (switches) o lectores de pantalla como unica via de interaccion. La interfaz ya cumplia WCAG 2.1 AA en estructura semantica (roles, aria-labels, contraste, targets 44px), pero faltaba gestion de foco tras acciones, anuncios explicitos para screen readers y atajos de teclado para navegacion eficiente.

## Decision

### Atajos de Teclado Globales

Se implementan atajos que solo se activan fuera de campos de texto:

| Atajo | Accion |
|-------|--------|
| `1`-`9`, `0` | Seleccionar categorias 1-10 |
| `?` | Seleccionar categoria 11 |
| `/` | Enfocar barra de busqueda |
| `Esc` | Limpiar busqueda / quitar foco |
| `Backspace` | Ir al ultimo chip seleccionado para eliminarlo |

Los atajos se documentan en un footer fijo visible solo en desktop, dado que en tablet y movil la interaccion es tactil.

### Navegacion con Teclado

**Categorias**: Tab navega entre categorias. Enter o click selecciona y carga pictogramas.

**Grid de pictogramas**: Las 4 flechas navegan en 2D. El calculo de columnas es dinamico segun el layout responsive. Los limites del grid se respetan sin wrap circular.

### Gestion de Foco

Se diferencia entre seleccion explicita (click, Enter, atajo numerico) y navegacion (Tab). Solo las acciones explicitas mueven el foco al primer pictograma tras la carga, evitando que navegar por categorias con Tab pierda el foco.

Tras eliminar un chip, el foco se mueve al chip adyacente. Tras generar una frase, el foco se mueve a la lista de resultados.

### Anuncios para Screen Reader

Dos live regions separadas por urgencia:

- **Polite**: Cuenta de resultados de busqueda, categoria seleccionada
- **Assertive**: "Pictograma X anadido a la frase", "Pictograma eliminado de la frase", "Frase generada con N variaciones"

### Chips Accesibles

Cada chip de pictograma seleccionado se estructura como grupo con nombre accesible. La imagen y el texto visual se marcan como decorativos para evitar que VoiceOver repita el nombre tres veces. El resultado es una lectura limpia: "miedo, grupo" y "Eliminar miedo, boton".

### Indicador de Foco

Todos los elementos interactivos muestran un anillo de foco visible al navegar con teclado. Las tarjetas de pictograma aplican el indicador a nivel de tarjeta completa para mayor visibilidad.

## Consecuencias

### Positivas

- Usuarios de teclado/conmutadores pueden operar toda la aplicacion sin raton
- Screen readers anuncian todas las acciones relevantes sin redundancia
- Navegacion rapida: un usuario experto selecciona categoria (numero) + pictograma (flechas) en 2-3 pulsaciones
- El footer de atajos sirve como descubrimiento progresivo

### Negativas

- 6 atajos globales que el usuario debe aprender (mitigado por footer visible)
- Complejidad adicional en gestion de foco para evitar conflictos entre acciones y navegacion

### Mitigaciones

- Footer siempre visible en desktop documenta todos los atajos
- Los atajos usan teclas intuitivas: numeros para categorias, `/` para buscar, `Backspace` para borrar
- Todos los atajos se ignoran dentro de campos de texto

## Referencias

- [WCAG 2.1 - 2.1.1 Keyboard](https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html)
- [WCAG 2.1 - 2.4.7 Focus Visible](https://www.w3.org/WAI/WCAG21/Understanding/focus-visible.html)
- [WCAG 2.1 - 4.1.3 Status Messages](https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html)
- [WAI-ARIA Grid Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/grid/)
- [WAI-ARIA Tabs Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/tabs/)
- ADR-010: Feedback Inline en Lugar de Toasts
- ADR-011: Sistema de Diseno Visual
