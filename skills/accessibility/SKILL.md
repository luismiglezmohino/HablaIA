---
name: accessibility
description: WCAG 2.1 AA compliance for AAC applications
license: MIT
compatibility: opencode
metadata:
  type: frontend-ux
  standard: wcag-2.1-aa
---

# SKILL: Accessibility (WCAG 2.1 AA)

## 🎯 CRITICAL for PictoSpeak AI
Este proyecto es para usuarios con diversidad funcional. Accesibilidad NO es opcional.

## ✅ Checklist Obligatorio

### Navegación por Teclado
- [ ] Tab order lógico en todos los componentes
- [ ] Focus visible (outline 3px, color contraste 3:1)
- [ ] Skip links para navegación rápida
- [ ] Shortcuts accesibles (evitar conflictos)

### Screen Readers
- [ ] ARIA labels en todos los pictogramas
- [ ] Role="button" en elementos clickables
- [ ] Live regions para feedback dinámico
- [ ] Alt text descriptivo en imágenes

### Contraste de Color
- [ ] Texto normal: ratio 4.5:1 mínimo
- [ ] Texto grande (18pt+): ratio 3:1 mínimo
- [ ] Verificar con herramienta (WebAIM Contrast Checker)

### Tamaño de Click Targets
- [ ] Mínimo 44x44px (WCAG 2.5.5)
- [ ] Separación 8px entre targets

### Testing
```bash
# Lighthouse Accessibility Score
npm run lighthouse -- --only-categories=accessibility

# axe-core automated testing
npm run test:a11y
```

## 🔧 Snippets

### Pictograma Accesible
```vue
<template>
  <button
    type="button"
    :aria-label="pictogram.alt"
    @click="handleClick"
    class="w-24 h-24 focus:outline focus:outline-3 focus:outline-blue-500"
  >
    <img 
      :src="pictogram.url" 
      :alt="pictogram.alt"
      role="img"
    />
    <span class="sr-only">{{ pictogram.text }}</span>
  </button>
</template>
```

### Live Region para Feedback
```vue
<div 
  role="status" 
  aria-live="polite" 
  aria-atomic="true"
  class="sr-only"
>
  {{ statusMessage }}
</div>
```