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
- **Framework:** Symfony 7
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

### B. Functional Test con Pest + Symfony WebTestCase
```php
// tests/Functional/Infrastructure/Http/Controller/CreateUserControllerTest.php
uses(Symfony\Bundle\FrameworkBundle\Test\WebTestCase::class);

it('registers a new user successfully', function () {
    $client = static::createClient();
    $client->request('POST', '/api/users', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'email' => 'test@example.com',
        'password' => 'securePassword123'
    ]));

    expect($client->getResponse()->getStatusCode())->toBe(201);
});
```

## Errores Comunes PestPHP + Symfony

### 1. NO usar `self::` fuera de closures
**Problema:** `self::createClient()` en scope global falla porque Pest functions son standalone.
**Solución:** Siempre usar `static::` DENTRO del closure de `it()`:
```php
// MAL - fuera del closure:
$client = self::createClient();
it('test', function () use ($client) { ... });

// BIEN - dentro del closure:
it('test', function () {
    $client = static::createClient();
});
```

### 2. Mock injection: SIEMPRE inline en el closure
**Problema:** Mocks creados en `beforeEach()` no se inyectan correctamente en el container de Symfony.
**Solución:** Crear mocks inline dentro de cada `it()`:
```php
// MAL - en beforeEach:
beforeEach(function () {
    $this->mock = $this->createMock(ServiceInterface::class);
});

// BIEN - inline en it():
it('does something', function () {
    $mock = $this->createMock(ServiceInterface::class);
    $mock->method('execute')->willReturn('result');
    static::getContainer()->set(ServiceInterface::class, $mock);

    $client = static::createClient();
    // ... test
});
```

### 3. `static::createClient()` solo dentro del closure
**Problema:** Llamar `createClient()` fuera del `it()` falla con "kernel not booted".
**Solución:** Toda interacción con Symfony kernel debe ser dentro del closure de `it()` o `test()`.

### 4. Functional vs Unit: usa WebTestCase solo para funcionales
```php
// Unit tests - NO necesitan WebTestCase:
it('validates email format', function () {
    $email = new Email('test@example.com');
    expect($email->value())->toBe('test@example.com');
});

// Functional tests - SI necesitan WebTestCase:
uses(WebTestCase::class);
it('returns 200 on health check', function () {
    $client = static::createClient();
    $client->request('GET', '/api/health');
    expect($client->getResponse()->getStatusCode())->toBe(200);
});
```

### 5. Ejecutar ambas suites
```bash
# Unit tests solamente
./vendor/bin/pest tests/Unit

# Functional tests solamente
./vendor/bin/pest tests/Functional

# AMBAS suites (obligatorio antes de commit)
./vendor/bin/pest
```