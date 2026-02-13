# ADR-010: Feedback Inline en Lugar de Toasts para Interfaces SAAC

**Estado:** Aceptado<br>
**Fecha:** 2026-02-08<br>
**Contexto:** HablaIA - SAAC con IA<br>

## Contexto

La aplicación necesita un sistema de feedback para comunicar al usuario el resultado de sus acciones (errores de red, generación de frases, límites de uso, fallos de TTS). El patrón habitual en aplicaciones web es usar **toasts** (notificaciones emergentes temporales que aparecen en una esquina y se auto-ocultan).

Sin embargo, los usuarios objetivo de HablaIA tienen perfiles específicos:

- **TEA (Trastorno del Espectro Autista):** Alta sensibilidad a cambios visuales inesperados. Las interrupciones no solicitadas pueden causar sobrecarga sensorial y ansiedad.
- **Afasia post-ictus:** Capacidad de procesamiento lingüístico reducida. Un mensaje que aparece y desaparece en 5 segundos puede no ser procesado a tiempo.
- **Parálisis cerebral / ELA:** Movilidad limitada. Un toast con botón de cierre agrega un target de interacción innecesario; si se auto-oculta, el usuario puede no alcanzar a leerlo.

## Decisión

Usar **feedback inline** y **feedback visual en botones** en lugar de toasts.

### Estrategia de feedback:

| Componente | Feedback | Ubicación |
|------------|----------|-----------|
| PictogramGrid | Error de carga, estado vacío, loading | Inline en el área del grid |
| CategoryBar | Error de carga, loading | Inline en el área de categorías |
| PhraseBar | Error de generación, loading, rate limiting | Inline bajo el botón "Generar" |
| Botón "Generar" | Loading spinner, éxito breve | Estado visual del propio botón |
| SpeakButton | Error de TTS | Estado visual del propio botón |

### Ciclo de vida del error:

- **Aparece** cuando la acción falla
- **Desaparece** cuando el usuario reintenta la acción o cambia de contexto (otra categoría, nueva búsqueda)
- **Sin temporizadores**: no desaparece solo, evitando que el usuario pierda el mensaje antes de leerlo

### Principio SAAC:

> **Nada se mueve sin que el usuario lo espere.** El feedback aparece donde el usuario está mirando, no en una esquina alejada de su foco de atención.

## Consecuencias

### Positivas
- Predecibilidad total: los mensajes aparecen siempre en el mismo lugar
- Sin interrupciones visuales inesperadas (crítico para TEA)
- Menor carga cognitiva: no requiere buscar información en otra zona de la pantalla
- Compatible con screen readers vía `role="alert"` y `aria-live="polite"`

### Negativas
- Menos visible que un toast para usuarios sin discapacidad
- Sin agregación global de errores múltiples

### Mitigaciones
- Mensajes de error con color rojo e icono para maximizar visibilidad
- `role="alert"` garantiza anuncio inmediato en screen readers

## Alternativas Consideradas

1. **Toasts (shadcn-vue Toast):** Descartado por sobrecarga sensorial en TEA y tiempo insuficiente de lectura en afasia.
2. **Modal de error:** Descartado por ser más intrusivo. Bloquea la interacción.
3. **Zona de estado global fija:** Descartada por requerir que el usuario busque información lejos de la acción.

## Referencias
- WCAG 2.1 SC 3.3.1: Error Identification
- WCAG 2.1 SC 4.1.3: Status Messages
