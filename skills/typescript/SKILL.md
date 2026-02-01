---
name: typescript
description: TypeScript with Zod validation, ESLint and Prettier
license: MIT
compatibility: opencode
metadata:
  type: language
  category: superset
---

# SKILL: TypeScript

## 🛠 Tech Stack
- **Compiler:** TypeScript
- **Linter:** ESLint
- **Formatter:** Prettier
- **Validation:** Zod (para validación en runtime)

## ⚡ Arquitectura & Logs
1.  **Carpetas:** `src/types/` para definiciones de tipos globales, `src/interfaces/` para contratos.
2.  **Log:** Consistente con el formato JSON definido, añadiendo `typeError` o `validationError` en `context` para logs específicos de TypeScript.

## ✅ Patrones (Snippets Reales)
### A. TDD Workflow (Vitest con TypeScript)
```typescript
// 1. Muestra el test fallando (RED)
// src/utils/calculator.spec.ts
import { describe, it, expect } from 'vitest';
import { add } from './calculator';

describe('add function', () => {
  it('should add two numbers correctly', () => {
    expect(add(1, 2)).toBe(3);
    expect(add(0, 0)).toBe(0);
    expect(add(-1, -1)).toBe(-2);
  });
});
```
### B. Secure Input Validation (Type Guards & Zod)
```typescript
// 2. Validación de input con Type Guards y Zod
import { z } from 'zod';

interface UserProfile {
  id: number;
  username: string;
  email: string;
  isActive: boolean;
}

const UserProfileSchema = z.object({
  id: z.number().int().positive(),
  username: z.string().min(3),
  email: z.string().email(),
  isActive: z.boolean(),
});

function validateUserProfile(data: unknown): UserProfile {
  try {
    return UserProfileSchema.parse(data);
  } catch (error) {
    if (error instanceof z.ZodError) {
      console.error("Validation failed for UserProfile:", error.errors);
      throw new Error("Invalid User Profile Data.");
    }
    throw error;
  }
}
```