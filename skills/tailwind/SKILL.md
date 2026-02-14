---
name: tailwind
description: Tailwind CSS with PostCSS, security patterns and visual testing
license: MIT
compatibility: opencode
metadata:
  type: styling
  category: css-framework
---

# SKILL: Tailwind CSS

## 🛠 Tech Stack
- **Framework:** Tailwind CSS
- **Post-processor:** PostCSS, Autoprefixer
- **Build:** Vite / Webpack (con PostCSS)

## ⚡ Arquitectura & Logs
1.  **Carpetas:** Archivos de configuración en la raíz (e.g., `tailwind.config.js`, `postcss.config.js`). CSS base importado en `src/assets/main.css` o similar.
2.  **Log:** Los errores de construcción de CSS o los mensajes de optimización de Tailwind JIT aparecerán en los logs del proceso de `build`.

## ✅ Patrones (Snippets Reales)
### A. TDD Workflow (Visual Regression Testing Concept)
Dado que Tailwind es un framework CSS, el TDD directo se aplica más a los componentes que a los estilos en sí. El equivalente a "test en rojo" para CSS es un cambio visual inesperado.

```javascript
// 1. Concepto: Visual Regression Testing (e.g., con Cypress + Percy/Chromatic)
// tests/e2e/specs/button.spec.js (Ejemplo)
it('should display the primary button correctly', () => {
  cy.visit('/components/button-primary'); // Navegar a la página del botón primario
  cy.get('.btn-primary').should('be.visible').percySnapshot('Primary Button State'); // Tomar snapshot visual
  // Un "fallo en rojo" sería un cambio inesperado en el snapshot visual en la CI/CD
});

// Nota: Los tests unitarios para componentes Vue/React que usan Tailwind
// se centrarán en la lógica y la renderización de clases correctas,
// no en la apariencia visual directa de Tailwind.
```
### B. Responsive Tablet-First
```html
<!-- Mobile: 2 columnas, Tablet portrait: 4, Tablet landscape: 6, Desktop: 8 -->
<div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
  <button class="min-h-[44px] min-w-[44px] p-2 rounded-lg
    focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
    <!-- Contenido -->
  </button>
</div>
```

### C. Secure UI Patterns (Avoiding Injection)
La seguridad en CSS se enfoca más en prevenir inyecciones a través de contenido dinámico.

```html
<!-- 2. Sanear contenido HTML generado por usuario para evitar inyecciones de estilo -->
<!-- Usar una librería de saneamiento (e.g., DOMPurify en JS, HTML Purifier en PHP) -->
<div 
  class="prose" 
  v-html="sanitizedUserContent"
></div>

<script>
// En Vue, por ejemplo
import DOMPurify from 'dompurify';

export default {
  data() {
    return {
      rawUserContent: '<p class="text-red-500">Contenido malicioso</p><img src="x" onerror="alert(\'XSS\')">',
    };
  },
  computed: {
    sanitizedUserContent() {
      // DOMPurify elimina atributos y etiquetas peligrosas
      return DOMPurify.sanitize(this.rawUserContent, { USE_PROFILES: { html: true } });
    },
  },
};
</script>
```