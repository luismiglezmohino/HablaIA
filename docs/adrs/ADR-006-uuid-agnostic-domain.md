# ADR-006: UUID Agnóstico en Dominio

**Estado:** Aceptado<br>
**Fecha:** 2026-02-01<br>
**Contexto:** HablaIA - Independencia del Dominio<br>

## Contexto

Siguiendo Clean Architecture (ADR-001), el Dominio debe ser puro y no depender de frameworks externos. Inicialmente usábamos `Symfony\Component\Uid\Uuid` para generar UUIDs, lo cual introducía una dependencia del framework en la capa de Dominio.

```php
// ❌ ANTES: Dominio acoplado a Symfony
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final readonly class PictogramId {
    public static function generate(): self {
        return new self(SymfonyUuid::v4()->toRfc4122()); // Dependencia Symfony
    }
}
```

Esto viola la regla de dependencias de Clean Architecture: **Domain NO puede depender de Infrastructure**.

## Decisión

Hacemos el Dominio **agnóstico** respecto a la generación de UUIDs:

1. **Dominio SOLO válida** formato UUID v4 (RFC 4122)
2. **Dominio define contrato** para generación (`UuidGeneratorInterface`)
3. **Infrastructure implementa** la generación (fuera del alcance actual)
4. **Tests usan** `FakeUuidGenerator` que implementa el contrato

### Implementación

```php
// Domain/Shared/ValueObject/Uuid.php - SOLO VALIDA
final class Uuid {
    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public static function isValid(string $value): bool {
        return preg_match(self::UUID_V4_PATTERN, $value) === 1;
    }

    public static function normalize(string $value): string {
        return strtolower($value);
    }
}

// Domain/Shared/Service/UuidGeneratorInterface.php - CONTRATO
interface UuidGeneratorInterface {
    public function generate(): string;
}

// Value Objects usan fromString(), NO generate()
final readonly class PictogramId {
    public static function fromString(string $value): self {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID v4 format');
        }
        return new self(Uuid::normalize($value));
    }
}
```

### Regla de Dependencias

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│   Domain (centro, NO depende de nada)                          │
│   ├── Uuid::isValid()         → Valida formato                 │
│   ├── Uuid::normalize()       → Normaliza a minúsculas         │
│   └── UuidGeneratorInterface  → Define contrato                │
│                                                                 │
│               ▲                                                 │
│               │ depende de                                      │
│                                                                 │
│   Application (usa Domain)                                      │
│   └── Use Cases inyectan UuidGeneratorInterface                │
│                                                                 │
│               ▲                                                 │
│               │ depende de                                      │
│                                                                 │
│   Infrastructure (implementa interfaces de Domain)             │
│   └── SymfonyUuidGenerator implements UuidGeneratorInterface   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Consecuencias

### Positivas

- **Dominio puro:** Sin dependencias de frameworks externos
- **Flexibilidad:** Cambiar implementación UUID sin tocar Dominio
- **Testabilidad:** Tests usan fakes sin necesitar Symfony
- **Clean Architecture:** Cumple regla de dependencias (Infrastructure → Application → Domain)
- **Portabilidad:** El Dominio funciona independiente del framework

### Trade-offs

| Aspecto | Con Framework | Agnóstico |
|---------|---------------|-----------|
| Código a mantener | Menos (delegado) | Más (validación propia) |
| Dependencia | Acoplado a versiones | Independiente |
| Bugs en UUID | Esperar parches | Control total |
| Breaking changes | Riesgo en upgrades | Sin riesgo |
| Testing | Requiere mock del framework | Fakes simples |

### Mitigaciones

- Validación UUID con regex RFC 4122 (estándar bien definido)
- `FakeUuidGenerator` usa `random_bytes()` (CSPRNG del sistema)
- Tests verifican formato y unicidad

## Alternativas Consideradas

### 1. Seguir con Symfony Uuid

**Pros:** Mantenido por comunidad, menos código propio<br>
**Contras:** Viola Clean Architecture, Dominio depende de Infrastructure<br>
**Rechazo:** Incompatible con principio de Dominio puro

### 2. Usar Ramsey/Uuid

**Pros:** Librería especializada, bien mantenida<br>
**Contras:** Sigue siendo dependencia externa en Dominio<br>
**Rechazo:** Mismo problema que Symfony

### 3. Generar UUID en Dominio (implementación propia)

**Pros:** Todo centralizado<br>
**Contras:** Dominio con responsabilidad de generación (no debería)<br>
**Rechazo:** El Dominio solo debe validar, no generar

## Referencias

- [ADR-001: Clean Architecture](./ADR-001-clean-architecture.md)
- [RFC 4122: UUID URN Namespace](https://datatracker.ietf.org/doc/html/rfc4122)
- [PHP random_bytes() - CSPRNG](https://www.php.net/manual/en/function.random-bytes.php)
