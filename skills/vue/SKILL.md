---
name: vue
description: Vue.js 3 with TypeScript, Vitest testing and Clean Architecture
license: MIT
compatibility: opencode
metadata:
  type: frontend
  framework: vue
  language: typescript
---

# SKILL: Vue.js 3 + TypeScript

## 🛠 Tech Stack
- **Testing:** Vitest
- **Validation:** Zod

## ⚡ Arquitectura & Logs
1.  **Carpetas:** `src/` con `components/`, `views/`, `composables/`, y las capas de Clean Architecture (`domain/`, `application/`, `infrastructure/`).
2.  **Log:**
    ```json
    {
      "level": "error",
      "message": "Failed to fetch user data",
      "correlationId": "abc-123",
      "timestamp": "2024-01-01T12:00:00Z",
      "extra": { "userId": 123 }
    }
    ```

## ✅ Patrones (Snippets Reales)
### A. TDD Workflow (Vitest)
```typescript
// 1. Muestra el test fallando (RED)
// composables/useCounter.spec.ts
import { describe, it, expect } from 'vitest';
import { useCounter } from './useCounter';

describe('useCounter', () => {
  it('should increment the count', () => {
    const { count, increment } = useCounter();
    expect(count.value).toBe(0);
    
    increment();
    
    // El test pasa después de implementar la función
    expect(count.value).toBe(1); 
  });
});
```
### B. Secure Input Validation (Zod)
```typescript
// 2. Validación de input con Zod
import { z } from 'zod';

const UserSchema = z.object({
  username: z.string().min(3, "El nombre de usuario debe tener al menos 3 caracteres."),
  email: z.string().email("El email no es válido."),
});

function saveUser(user: unknown) {
  try {
    const validatedUser = UserSchema.parse(user);
    console.log("Usuario validado:", validatedUser);
    // ...lógica para guardar usuario
  } catch (error) {
    if (error instanceof z.ZodError) {
      console.error("Error de validación:", error.errors);
      // Lanzar excepción customizada o devolver error
      throw new Error("Datos de usuario inválidos.");
    }
  }
}
```

### C. Pinia Store (Composition API)
```typescript
// stores/usePictogramStore.ts
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Pictogram } from '@/domain/entities/Pictogram'

export const usePictogramStore = defineStore('pictogram', () => {
  const items = ref<Pictogram[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isEmpty = computed(() => items.value.length === 0)

  async function fetchByCategory(categoryId: string): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(`/api/pictograms?categoryId=${categoryId}`)
      items.value = await response.json()
    } catch (e) {
      error.value = 'Error al cargar pictogramas'
    } finally {
      loading.value = false
    }
  }

  return { items, loading, error, isEmpty, fetchByCategory }
})
```

### D. Componente Accesible (WCAG 2.2 AA)
```vue
<script setup lang="ts">
defineProps<{ label: string; imageSrc: string }>()
defineEmits<{ select: [] }>()
</script>

<template>
  <button
    class="min-h-[44px] min-w-[44px] rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500"
    :aria-label="label"
    @click="$emit('select')"
  >
    <img :src="imageSrc" :alt="label" />
    <span class="text-sm">{{ label }}</span>
  </button>
</template>
```