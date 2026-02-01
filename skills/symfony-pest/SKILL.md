---
name: symfony-pest
description: Symfony with Pest PHP testing framework and TDD patterns
license: MIT
compatibility: opencode
metadata:
  type: testing
  framework: pest
  language: php
---

# SKILL: Symfony & Pest PHP

## 🛠 Tech Stack
- **Framework:** Symfony 6/7
- **Testing:** Pest PHP

## ⚡ Arquitectura Clean
1. **Domain:** `src/Domain/`
2. **Application:** `src/Application/`
3. **Infrastructure:** `src/Infrastructure/`

## ✅ Patrones (Snippets Reales)
### A. TDD Workflow (PestPHP)
```php
// 1. Muestra el test fallando (RED)
it('calculates total', function () {
    $calculator = new Calculator();
    expect($calculator->add(1, 1))->toBe(2);
});
```

### B. Feature Test con Pest
```php
// tests/Feature/UserRegistrationTest.php
uses()->group('feature');

it('registers a new user successfully', function () {
    $response = $this->postJson('/api/register', [
        'email' => 'test@example.com',
        'password' => 'securePassword123'
    ]);
    
    $response->assertStatus(201)
             ->assertJsonPath('data.email', 'test@example.com');
});
```