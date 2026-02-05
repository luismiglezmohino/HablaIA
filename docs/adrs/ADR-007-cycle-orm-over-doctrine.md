# ADR-007: Cycle ORM sobre Doctrine ORM

**Estado:** Aceptado
**Fecha:** 2025-02-03
**Contexto:** HablaIA - SAAC con IA

## Contexto

Durante la implementación de la capa de Infrastructure, se evaluó el uso de Doctrine ORM (estándar en Symfony) para la persistencia de datos. Tras implementar la Fase 1 (Custom Types para Value Objects), se identificaron varios problemas:

1. **Boilerplate excesivo**: Cada Value Object requiere una clase Type separada (~50 líneas por tipo)
2. **Configuración verbose**: Registro manual de tipos en `doctrine.yaml`
3. **Schema validation problemático**: Los COMMENTs de DC2Type causan diferencias falsas en `doctrine:schema:validate`
4. **Complejidad innecesaria**: Proxy objects, lazy loading implícito, Identity Map

Para un proyecto TFM con tiempo limitado y enfoque en la funcionalidad SAAC, esta complejidad añade fricción sin beneficio proporcional.

## Decisión

**Migrar de Doctrine ORM a Cycle ORM** para la capa de persistencia.

### Comparativa técnica

| Aspecto | Doctrine ORM | Cycle ORM |
|---------|--------------|-----------|
| Custom Types | Clase separada por VO | Typecast inline |
| Líneas por entidad | ~150 | ~60 |
| Schema sync | Problemático | Limpio |
| Memoria | Alta (Identity Map) | Baja |
| Configuración | Verbose | Mínima |
| Curva aprendizaje | Alta | Media |

### Ejemplo concreto

**Doctrine (antes):**
```php
// 1. Custom Type (archivo separado)
final class CategoryIdType extends Type {
    public function getSQLDeclaration(...): string { return 'UUID'; }
    public function convertToPHPValue(...): ?CategoryId { /* 15 líneas */ }
    public function convertToDatabaseValue(...): ?string { /* 10 líneas */ }
    public function requiresSQLCommentHint(...): bool { return true; }
}

// 2. doctrine.yaml
doctrine:
    dbal:
        types:
            category_id: App\...\CategoryIdType

// 3. Entidad con getters/setters obligatorios
#[ORM\Entity]
class DoctrineCategory {
    #[ORM\Column(type: 'category_id')]
    private string $id;
    // + getters/setters
}
```

**Cycle ORM (después):**
```php
// 1. NO hay Custom Type - typecast en entidad
#[Entity(table: 'categories')]
class CategoryEntity {
    #[Column(type: 'uuid', primary: true)]
    public string $id;

    #[Column(type: 'string(50)')]
    public string $name;
}

// 2. cycle.yaml mínimo
cycle:
    default: default
```

## Consecuencias

### Positivas

- **Menos código**: Eliminamos 4 Custom Types y 72 tests asociados
- **Configuración simple**: Un archivo YAML mínimo
- **Schema limpio**: Sin COMMENTs problemáticos
- **Mejor DX**: Propiedades públicas, sin proxy magic
- **Menor consumo memoria**: Ideal para workers de IA
- **Debugging más fácil**: Objetos reales, sin proxies

### Negativas

- **Comunidad menor**: Menos recursos en español
- **Integración Symfony**: Bundle externo (cycle/orm-bundle)
- **Reescritura**: Entidades Doctrine → Cycle
- **Documentación limitada**: Principalmente en inglés

### Mitigaciones

- Documentar patrones usados en el proyecto
- Mantener la arquitectura limpia (Domain no depende de ORM)
- Los Mappers aíslan cambios de ORM del Domain

## Impacto en código existente

| Componente | Acción |
|------------|--------|
| `*Type.php` (4 archivos) | Eliminar |
| `*TypeTest.php` (4 archivos) | Eliminar |
| `doctrine.yaml` (tipos custom) | Limpiar |
| `SymfonyUuidGenerator.php` | Mantener |
| Domain entities | Sin cambios |
| Mappers | Ajustes menores |

## Alternativas Consideradas

1. **Continuar con Doctrine**: Descartado por complejidad innecesaria
2. **Doctrine DBAL puro**: Más control pero sin ORM benefits
3. **PDO directo**: Demasiado manual, propenso a errores

## Referencias

- [Cycle ORM Documentation](https://cycle-orm.dev/docs)
- [Cycle ORM Symfony Bundle](https://github.com/cycle/orm-bundle)
- [Comparativa ORMs PHP](https://github.com/cycle/orm#comparison)
