# HablaIA Frontend

## Stack

- **Framework:** Vue 3 (Composition API + TypeScript) - SPA reactiva
- **State:** Pinia - store global (sesion usuario, pictogramas seleccionados, config TTS)
- **CSS:** TailwindCSS - utility-first, responsive tablet-first
- **UI Components:** shadcn-vue - componentes preconstruidos accesibles (Dialog, Popover, Toast, etc.). El `style` en `components.json` solo afecta a componentes generados con `npx shadcn-vue add <componente>`; los ya existentes no se actualizan. Se puede cambiar en cualquier momento.
- **Primitives:** Radix Vue - primitivas headless accesibles (base de shadcn-vue, WAI-ARIA built-in)
- **Icons:** Lucide Vue - iconos SVG ligeros para categorias y UI
- **Validation:** Zod - validacion de schemas en runtime (formularios, API responses)
- **HTTP:** Fetch nativo - peticiones API sin dependencias extra
- **Utilities:** @vueuse/core - composables reactivos (useSpeechSynthesis, useLocalStorage, useFocusTrap, useMediaQuery)
- **Speech:** Web Speech API - sintesis de voz para lectura de frases generadas
- **Build:** Vite - bundler rapido con HMR
- **Test Unit:** Vitest + Testing Library - tests de componentes y logica
- **Test E2E:** Playwright - tests de flujos criticos en navegador real

## Architecture (Clean Architecture)

```
src/
  domain/           # Entities, ValueObjects (puro TypeScript, sin dependencias)
  application/      # UseCases, Services, Ports (interfaces)
  infrastructure/   # Http (fetch), Storage (localStorage), Speech (Web Speech API)
  presentation/     # Vue components, composables, pages, layouts
tests/
  unit/             # Vitest + Testing Library
  e2e/              # Playwright
```

## Standards

- **TypeScript strict** (`strict: true` en tsconfig)
- **Composition API** exclusivamente (no Options API)
- **WCAG 2.2 AA** obligatorio: click targets >= 44x44px, navegacion por teclado, screen reader compatible
- **Tablet-first** responsive design
- **Coverage:** 100% Domain, 80% Application/Components, E2E en flujos criticos

## Commands

```bash
npm run dev          # Dev server (Vite)
npm run build        # Production build
npm run test         # Vitest unit tests
npm run test:coverage # Vitest con coverage
npm run lint         # ESLint + Prettier check
npm run lint:fix     # Auto-fix lint issues
npx playwright test  # E2E tests
```

## Skills Relevantes

Lee `skills/{skill}/SKILL.md` antes de implementar:
- `vue` - Vue 3 patterns, composables, Clean Architecture
- `vue-vitest` - Component testing con Vitest + Testing Library
- `typescript` - TypeScript + Zod validation
- `tailwind` - TailwindCSS patterns
- `accessibility` - WCAG 2.2 AA para interfaces SAAC

## Patrones del Proyecto

### Composable (Application layer)
```typescript
// src/application/composables/usePictograms.ts
import { ref } from 'vue'
import type { Pictogram } from '@/domain/entities/Pictogram'

export function usePictograms() {
  const pictograms = ref<Pictogram[]>([])
  const loading = ref(false)

  async function search(query: string): Promise<void> {
    loading.value = true
    try {
      const response = await fetch(`/api/pictograms/search?q=${encodeURIComponent(query)}`)
      pictograms.value = await response.json()
    } finally {
      loading.value = false
    }
  }

  return { pictograms, loading, search }
}
```

### Component (Presentation layer)
```vue
<!-- src/presentation/components/PictogramCard.vue -->
<script setup lang="ts">
import type { Pictogram } from '@/domain/entities/Pictogram'

const props = defineProps<{
  pictogram: Pictogram
}>()

const emit = defineEmits<{
  select: [pictogram: Pictogram]
}>()
</script>

<template>
  <button
    class="min-h-[44px] min-w-[44px] p-2 rounded-lg focus:ring-2 focus:ring-blue-500"
    :aria-label="pictogram.label"
    @click="emit('select', pictogram)"
  >
    <img :src="pictogram.imageUrl" :alt="pictogram.label" />
    <span class="text-sm">{{ pictogram.label }}</span>
  </button>
</template>
```

### Test (Vitest + Testing Library)
```typescript
// tests/unit/components/PictogramCard.test.ts
import { render, screen } from '@testing-library/vue'
import { userEvent } from '@testing-library/user-event'
import PictogramCard from '@/presentation/components/PictogramCard.vue'

describe('PictogramCard', () => {
  it('emits select event on click', async () => {
    const pictogram = { id: '1', label: 'Water', imageUrl: '/water.png' }
    const { emitted } = render(PictogramCard, {
      props: { pictogram }
    })

    await userEvent.click(screen.getByRole('button', { name: 'Water' }))

    expect(emitted().select[0]).toEqual([pictogram])
  })
})
```

## Accesibilidad (SAAC)

- Todos los botones de pictogramas: `min-h-[44px] min-w-[44px]`
- Todos los elementos interactivos: `focus:ring-2` visible
- Navegacion completa por teclado (Tab, Enter, Escape, Arrow keys)
- `aria-label` en pictogramas e iconos
- `role="status"` para feedback de voz/TTS
- Contraste minimo 4.5:1 (texto) y 3:1 (UI components)

### Colores Fitzgerald Key (ADR-008)

Las categorias usan el sistema Modified Fitzgerald Key (estandar SAAC). Reglas criticas:

- **`colorHex` de la API se usa como borde/acento** (`border-color`, `border-top`), NUNCA como `background-color` completo
- **Fondo blanco obligatorio** en tarjetas de pictogramas (pictogramas ARASAAC disenados sobre blanco)
- **Texto negro sobre blanco** (contraste 21:1, sin depender del color de categoria)
- Tonos claros (ej. Amber-400) no pasan WCAG AA como fondo, si como borde

```vue
<!-- CORRECTO: color como borde -->
<div :style="{ borderTopColor: category.colorHex }" class="border-t-4 bg-white">

<!-- INCORRECTO: color como fondo -->
<div :style="{ backgroundColor: category.colorHex }">
```

Ver `docs/adrs/ADR-008-fitzgerald-key-color-coding.md` para el mapeo completo de colores por categoria.
