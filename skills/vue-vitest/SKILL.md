---
name: vue-vitest
description: Vue.js 3 testing with Vitest including component and composable tests
license: MIT
compatibility: opencode
metadata:
  type: testing
  framework: vitest
  language: typescript
---

# SKILL: Vue.js + Vitest

## 🛠 Tech Stack
- **Framework:** Vue.js 3 (Composition API)
- **Testing:** Vitest
- **Utilities:** Vue Test Utils

## ⚡ Arquitectura & Testing Strategy
1.  **Unit Tests:** Testear composables y funciones puras.
2.  **Component Tests:** Testear renderizado y eventos.
3.  **Integration:** Testear flujos completos con mocks.

## ✅ Patrones (Snippets Reales)
### A. Testing Composables
```typescript
// composables/useAuth.spec.ts
import { describe, it, expect, vi } from 'vitest';
import { useAuth } from './useAuth';

describe('useAuth', () => {
  it('should authenticate user with valid credentials', async () => {
    // Arrange
    const mockLogin = vi.fn().mockResolvedValue({ token: 'abc123' });
    
    // Act
    const { login, isAuthenticated } = useAuth({ loginFn: mockLogin });
    await login('user@test.com', 'password');
    
    // Assert
    expect(isAuthenticated.value).toBe(true);
    expect(mockLogin).toHaveBeenCalledWith('user@test.com', 'password');
  });
});
```

### B. Testing Vue Components
```typescript
// components/UserCard.spec.ts
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import UserCard from './UserCard.vue';

describe('UserCard', () => {
  it('renders user information correctly', () => {
    const wrapper = mount(UserCard, {
      props: {
        user: {
          name: 'John Doe',
          email: 'john@example.com'
        }
      }
    });
    
    expect(wrapper.text()).toContain('John Doe');
    expect(wrapper.text()).toContain('john@example.com');
  });
  
  it('emits event when button is clicked', async () => {
    const wrapper = mount(UserCard, {
      props: { user: { name: 'Test', email: 'test@test.com' } }
    });
    
    await wrapper.find('button').trigger('click');
    
    expect(wrapper.emitted()).toHaveProperty('select');
  });
});
```

### C. Testing with Pinia Store
```typescript
// stores/user.spec.ts
import { setActivePinia, createPinia } from 'pinia';
import { useUserStore } from './user';

describe('User Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });
  
  it('updates user profile', () => {
    const store = useUserStore();
    
    store.updateProfile({ name: 'Jane Doe' });
    
    expect(store.profile.name).toBe('Jane Doe');
  });
});
```

### D. Testing Accesibilidad
```typescript
// components/PictogramCard.spec.ts
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import PictogramCard from './PictogramCard.vue'

describe('PictogramCard - Accesibilidad', () => {
  it('tiene aria-label descriptivo', () => {
    render(PictogramCard, { props: { label: 'Comer', imageSrc: '/comer.png' } })
    expect(screen.getByRole('button', { name: 'Comer' })).toBeDefined()
  })

  it('tiene tamaño mínimo de 44x44px para WCAG 2.2 AA', () => {
    render(PictogramCard, { props: { label: 'Comer', imageSrc: '/comer.png' } })
    const button = screen.getByRole('button')
    expect(button.className).toContain('min-h-[44px]')
    expect(button.className).toContain('min-w-[44px]')
  })

  it('es activable con teclado (Enter y Space)', async () => {
    const { emitted } = render(PictogramCard, {
      props: { label: 'Comer', imageSrc: '/comer.png' }
    })
    const button = screen.getByRole('button')
    button.focus()
    await userEvent.keyboard('{Enter}')
    expect(emitted().select).toBeDefined()
  })
})
```