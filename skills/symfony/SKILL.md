---
name: symfony
description: Symfony framework with PestPHP testing and Clean Architecture patterns
license: MIT
compatibility: opencode
metadata:
  type: backend
  framework: symfony
  language: php
---

# SKILL: Symfony

## 🛠 Tech Stack
- **Testing:** PestPHP
- **Validation:** Symfony Validator Component

## ⚡ Arquitectura & Logs
1.  **Carpetas:** `src/` con `Domain/`, `Application/`, `Infrastructure/`.
2.  **Log:** (Configurado en `monolog.yaml` para output `json`)
    ```json
    {
        "message": "User login failed",
        "context": {
            "username": "test@user.com",
            "reason": "Invalid credentials",
            "correlationId": "xyz-456"
        },
        "level": 400,
        "level_name": "ERROR",
        "channel": "app",
        "datetime": "...",
        "extra": {}
    }
    ```

## ✅ Patrones (Snippets Reales)
### A. TDD Workflow (PestPHP)
```php
// 1. Muestra el test fallando (RED)
// tests/Application/Service/DiscountCalculatorTest.php
use App\Application\Service\DiscountCalculator;

it('calculates a 10% discount correctly', function () {
    $calculator = new DiscountCalculator();
    
    // El test pasa después de implementar la lógica
    expect($calculator->calculate(100, 10))->toBe(90.0);
});
```
### B. Secure Controller (Symfony Validator)
```php
// 2. Validación de DTO en un controlador
// src/Infrastructure/Http/Controller/CreateUserController.php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Application\DTO\CreateUserDTO; // Un DTO con Asserts

#[Route('/api/users', name: 'user_create', methods: ['POST'])]
public function __invoke(Request $request, ValidatorInterface $validator): Response
{
    try {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateUserDTO($data['email'], $data['password']);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            // Devolver errores de validación
            return new Response((string) $errors, Response::HTTP_BAD_REQUEST);
        }
        
        // ... llamar al caso de uso de aplicación
        return new Response('', Response::HTTP_CREATED);

    } catch (\Exception $e) {
        // Loggear y devolver error genérico
        return new Response('An error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```