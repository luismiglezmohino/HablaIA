# ADR-013: Navegación por Teclado y Soporte Screen Reader

**Estado:** Aceptado<br>
**Fecha:** 2026-02-10<br>
**Contexto:** HablaIA - Comunicador SAAC con IA<br>

## Contexto

HablaIA es un comunicador pictográfico para usuarios con TEA, afasia, parálisis cerebral y ELA. Muchos de estos usuarios dependen de teclado, conmutadores (switches) o lectores de pantalla como única vía de interacción. La interfaz ya cumplía WCAG 2.2 AA en estructura semántica (roles, aria-labels, contraste, targets 44px), pero faltaba gestión de foco tras acciones, anuncios explícitos para screen readers y atajos de teclado para navegación eficiente.

## Decisión

### Atajos de Teclado Globales

Se implementan atajos que solo se activan fuera de campos de texto:

| Atajo | Acción |
|-------|--------|
| `1`-`9`, `0` | Seleccionar categorías 1-10 |
| `?` | Seleccionar categoría 11 |
| `/` | Enfocar barra de búsqueda |
| `Esc` | Limpiar búsqueda / quitar foco |
| `Backspace` | Ir al último chip seleccionado para eliminarlo |

Los atajos se documentan en un footer fijo visible solo en desktop, dado que en tablet y móvil la interacción es táctil.

### Navegación con Teclado

**Categorías**: Tab navega entre categorías. Enter o click selecciona y carga pictogramas.

**Grid de pictogramas**: Las 4 flechas navegan en 2D. El cálculo de columnas es dinámico según el layout responsive. Los límites del grid se respetan sin wrap circular.

### Gestión de Foco

Se diferencia entre selección explícita (click, Enter, atajo numérico) y navegación (Tab). Solo las acciones explícitas mueven el foco al primer pictograma tras la carga, evitando que navegar por categorías con Tab pierda el foco.

Tras eliminar un chip, el foco se mueve al chip adyacente. Tras generar una frase, el foco se mueve a la lista de resultados.

### Anuncios para Screen Reader

Dos live regions separadas por urgencia:

- **Polite**: Cuenta de resultados de búsqueda, categoría seleccionada
- **Assertive**: "Pictograma X añadido a la frase", "Pictograma eliminado de la frase", "Frase generada con N variaciones"

### Chips Accesibles

Cada chip de pictograma seleccionado se estructura como grupo con nombre accesible. La imagen y el texto visual se marcan como decorativos para evitar que VoiceOver repita el nombre tres veces. El resultado es una lectura limpia: "miedo, grupo" y "Eliminar miedo, botón".

### Indicador de Foco

Todos los elementos interactivos muestran un anillo de foco visible al navegar con teclado. Las tarjetas de pictograma aplican el indicador a nivel de tarjeta completa para mayor visibilidad.

## Consecuencias

### Positivas

- Usuarios de teclado/conmutadores pueden operar toda la aplicación sin ratón
- Screen readers anuncian todas las acciones relevantes sin redundancia
- Navegación rápida: un usuario experto selecciona categoría (número) + pictograma (flechas) en 2-3 pulsaciones
- El footer de atajos sirve como descubrimiento progresivo

### Negativas

- 6 atajos globales que el usuario debe aprender (mitigado por footer visible)
- Complejidad adicional en gestión de foco para evitar conflictos entre acciones y navegación

### Mitigaciones

- Footer siempre visible en desktop documenta todos los atajos
- Los atajos usan teclas intuitivas: números para categorías, `/` para buscar, `Backspace` para borrar
- Todos los atajos se ignoran dentro de campos de texto

## Referencias

- [WCAG 2.2 - 2.1.1 Keyboard](https://www.w3.org/WAI/WCAG22/Understanding/keyboard.html)
- [WCAG 2.2 - 2.4.7 Focus Visible](https://www.w3.org/WAI/WCAG22/Understanding/focus-visible.html)
- [WCAG 2.2 - 4.1.3 Status Messages](https://www.w3.org/WAI/WCAG22/Understanding/status-messages.html)
- [WAI-ARIA Grid Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/grid/)
- [WAI-ARIA Tabs Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/tabs/)
- ADR-010: Feedback Inline en Lugar de Toasts
- ADR-011: Sistema de Diseño Visual
