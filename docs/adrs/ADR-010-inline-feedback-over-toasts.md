# ADR-010: Feedback Inline en Lugar de Toasts para Interfaces SAAC

**Estado:** Aceptado
**Fecha:** 2026-02-08
**Contexto:** HablaIA - SAAC con IA

## Contexto

La aplicacion necesita un sistema de feedback para comunicar al usuario el resultado de sus acciones (errores de red, generacion de frases, limites de uso, fallos de TTS). El patron habitual en aplicaciones web es usar **toasts** (notificaciones emergentes temporales que aparecen en una esquina y se auto-ocultan).

Sin embargo, los usuarios objetivo de HablaIA tienen perfiles especificos:

- **TEA (Trastorno del Espectro Autista):** Alta sensibilidad a cambios visuales inesperados. Las interrupciones no solicitadas pueden causar sobrecarga sensorial y ansiedad.
- **Afasia post-ictus:** Capacidad de procesamiento linguistico reducida. Un mensaje que aparece y desaparece en 5 segundos puede no ser procesado a tiempo.
- **Paralisis cerebral / ELA:** Movilidad limitada. Un toast con boton de cierre agrega un target de interaccion innecesario; si se auto-oculta, el usuario puede no alcanzar a leerlo.

## Decision

Usar **feedback inline** y **feedback visual en botones** en lugar de toasts.

### Estrategia de feedback:

| Componente | Feedback | Ubicacion |
|------------|----------|-----------|
| PictogramGrid | Error de carga, estado vacio, loading | Inline en el area del grid |
| CategoryBar | Error de carga, loading | Inline en el area de categorias |
| PhraseBar | Error de generacion, loading, rate limiting | Inline bajo el boton "Generar" |
| Boton "Generar" | Loading spinner, exito breve | Estado visual del propio boton |
| SpeakButton | Error de TTS | Estado visual del propio boton |

### Ciclo de vida del error:

- **Aparece** cuando la accion falla
- **Desaparece** cuando el usuario reintenta la accion o cambia de contexto (otra categoria, nueva busqueda)
- **Sin temporizadores**: no desaparece solo, evitando que el usuario pierda el mensaje antes de leerlo

### Principio SAAC:

> **Nada se mueve sin que el usuario lo espere.** El feedback aparece donde el usuario esta mirando, no en una esquina alejada de su foco de atencion.

## Consecuencias

### Positivas
- Predecibilidad total: los mensajes aparecen siempre en el mismo lugar
- Sin interrupciones visuales inesperadas (critico para TEA)
- Menor carga cognitiva: no requiere buscar informacion en otra zona de la pantalla
- Compatible con screen readers via `role="alert"` y `aria-live="polite"`

### Negativas
- Menos visible que un toast para usuarios sin discapacidad
- Sin agregacion global de errores multiples

### Mitigaciones
- Mensajes de error con color rojo e icono para maximizar visibilidad
- `role="alert"` garantiza anuncio inmediato en screen readers

## Alternativas Consideradas

1. **Toasts (shadcn-vue Toast):** Descartado por sobrecarga sensorial en TEA y tiempo insuficiente de lectura en afasia.
2. **Modal de error:** Descartado por ser mas intrusivo. Bloquea la interaccion.
3. **Zona de estado global fija:** Descartada por requerir que el usuario busque informacion lejos de la accion.

## Referencias
- WCAG 2.1 SC 3.3.1: Error Identification
- WCAG 2.1 SC 4.1.3: Status Messages
