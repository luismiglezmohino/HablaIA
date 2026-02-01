# ADR-001: Adopción de Clean Architecture

**Estado:** Aceptado
**Fecha:** 2026-01-31
**Contexto:** HablaIA - MVP SAAC con IA

## Contexto

Necesitamos una arquitectura escalable que permita evolucionar el proyecto desde un MVP hasta un producto maduro con voice cloning y sincronización multi-dispositivo (6 fases). La arquitectura debe:

1. Facilitar testing (TDD obligatorio)
2. Permitir cambiar infraestructura sin afectar lógica de negocio
3. Desacoplar framework de dominio
4. Soportar evolución gradual (Web Speech → ElevenLabs → Voice Cloning)

## Decisión

Adoptamos **Clean Architecture** (Uncle Bob) con 3 capas concéntricas:

```
┌─────────────────────────────────────┐
│       Infrastructure                │  ← Doctrine, Symfony, HTTP, APIs
│  ┌───────────────────────────────┐  │
│  │      Application              │  │  ← Use Cases, DTOs, Services
│  │  ┌─────────────────────────┐  │  │
│  │  │       Domain            │  │  │  ← Entities, VOs, Repositories
│  │  │                         │  │  │     (SIN dependencias)
│  │  └─────────────────────────┘  │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

### Reglas de Dependencia

1. **Domain:** NO puede depender de Application ni Infrastructure
2. **Application:** Puede depender de Domain, NO de Infrastructure
3. **Infrastructure:** Implementa interfaces definidas en capas internas

### Ejemplo Concreto

```php
// ❌ MAL: Domain depende de Doctrine
namespace App\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Pictogram {
    #[ORM\Id]
    private int $id;
}

// ✅ BIEN: Domain puro, Infrastructure implementa persistencia
namespace App\Domain\Entity;

class Pictogram {
    private PictogramId $id;

    public function __construct(PictogramId $id) {
        $this->id = $id;
    }
}

// Infrastructure implementa
namespace App\Infrastructure\Persistence\Doctrine\Mapping;
// pictogram.orm.xml define mapeo
```

## Consecuencias

### Positivas

- **Testabilidad:** Dominio testeable sin BD/Framework
- **Evolución:** Cambiar TTS (Web Speech → ElevenLabs) sin tocar dominio
- **Mantenibilidad:** Lógica de negocio centralizada
- **Portabilidad:** Migrar de Symfony a otro framework = cambiar Infrastructure

### Negativas

- **Curva de aprendizaje:** Team debe entender capas
- **Overhead inicial:** Más archivos (interfaces + implementaciones)
- **Riesgo de sobre-ingeniería:** Para MVP, tentación de simplificar

### Mitigaciones

- Documentación clara de capas en README
- Code reviews estrictos para evitar violaciones
- Plantillas de código para cada capa

## Alternativas Consideradas

### 1. MVC Tradicional (Symfony estándar)

**Pros:** Rápido para MVP, menos código
**Contras:** Difícil testear, acoplado a framework, migración difícil
**Rechazo:** No escala a 6 fases

### 2. Hexagonal Architecture (Ports & Adapters)

**Pros:** Similar a Clean, desacoplado
**Contras:** Más complejo que Clean Architecture, no aporta ventajas adicionales
**Rechazo:** Clean Architecture es suficiente y más conocido

## Referencias

- [Clean Architecture (Uncle Bob)](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Symfony + Clean Arch](https://herbertograca.com/2017/11/16/explicit-architecture-01-ddd-hexagonal-onion-clean-cqrs-how-i-put-it-all-together/)
