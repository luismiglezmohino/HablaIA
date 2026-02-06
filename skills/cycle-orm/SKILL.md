---
name: cycle-orm
description: Cycle ORM patterns, configuration and common pitfalls for Symfony integration
license: MIT
compatibility: opencode
metadata:
  type: orm
  framework: cycle-orm
  language: php
---

# SKILL: Cycle ORM (Symfony Integration)

## Tech Stack
- **ORM:** Cycle ORM v2
- **Framework:** Symfony 7 (sin bundle oficial, configuracion manual)
- **Database:** PostgreSQL
- **ADR:** Ver `docs/adrs/ADR-007-cycle-orm-over-doctrine.md`

## Arquitectura

Cycle ORM se usa SOLO en la capa de Infrastructure. El Domain NO conoce el ORM.

```
Domain/          -> Entidades puras, Repository interfaces
Infrastructure/
  Persistence/
    Cycle/
      Entity/      -> Entidades Cycle (anemicas, propiedades publicas)
      Repository/  -> Implementaciones de repos del Domain
      Mapper/      -> Conversion Domain <-> Cycle Entity
      OrmFactory.php
      DatabaseFactory.php
```

## Patrones del Proyecto

### A. Entidad Cycle (Infrastructure)
```php
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(table: 'categories')]
class CategoryEntity
{
    #[Column(type: 'uuid', primary: true)]
    public string $id = '';

    #[Column(type: 'string(50)')]
    public string $name = '';

    #[Column(type: 'integer', default: 0)]
    public int $displayOrder = 0;
}
```

### B. JSON Columns (Typecast obligatorio)
```php
// SIN typecast: devuelve string crudo, NO array
#[Column(type: 'json')]
public array $pictogramIds = []; // BUG: sera string

// CON typecast: devuelve array correctamente
#[Column(type: 'json', typecast: 'json')]
public array $pictogramIds = []; // CORRECTO: sera array
```

### C. Relaciones
```php
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[BelongsTo(target: CategoryEntity::class, innerKey: 'categoryId')]
public ?CategoryEntity $category = null;
```

### D. Repository (envuelve Cycle Repository)
```php
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

final class CycleCategoryRepository implements CategoryRepository
{
    /** @param Repository<CategoryEntity> $repository */
    public function __construct(
        private readonly Repository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(CategoryId $id): ?Category
    {
        $entity = $this->repository->findByPK($id->value());
        if (!$entity instanceof CategoryEntity) {
            return null;
        }
        return CategoryMapper::toDomain($entity);
    }

    public function save(Category $category): void
    {
        $entity = CategoryMapper::toEntity($category);
        $this->entityManager->persist($entity);
        $this->entityManager->run();
    }
}
```

### E. Fragment para SQL crudo
```php
use Cycle\Database\Injection\Fragment;

// Para funciones SQL como LOWER, UPPER, COALESCE:
$entities = $this->repository
    ->select()
    ->where(new Fragment('LOWER("label") LIKE ?', "%{$query}%"))
    ->limit($limit)
    ->fetchAll();
```

### F. Mapper (Domain <-> Entity)
```php
final class CategoryMapper
{
    public static function toDomain(CategoryEntity $entity): Category
    {
        return new Category(
            CategoryId::fromString($entity->id),
            $entity->name,
            $entity->icon,
            $entity->colorHex,
            $entity->displayOrder
        );
    }

    public static function toEntity(Category $domain): CategoryEntity
    {
        $entity = new CategoryEntity();
        $entity->id = $domain->id()->value();
        $entity->name = $domain->name();
        // ... mapear todos los campos
        return $entity;
    }
}
```

## Configuracion DI (Symfony)

### cycle.yaml - Registro manual de servicios
```yaml
services:
    # 1. DatabaseManager (factory)
    Cycle\Database\DatabaseManager:
        factory: ['App\Infrastructure\Persistence\Cycle\DatabaseFactory', 'create']
        arguments: ['%env(DATABASE_URL)%']

    # 2. ORM (factory)
    Cycle\ORM\ORM:
        factory: ['App\Infrastructure\Persistence\Cycle\OrmFactory', 'create']
        arguments:
            - '@Cycle\Database\DatabaseManager'
            - '%kernel.project_dir%/src/Infrastructure/Persistence/Cycle/Entity'

    # 3. EntityManager
    Cycle\ORM\EntityManagerInterface:
        class: Cycle\ORM\EntityManager
        arguments: ['@Cycle\ORM\ORM']

    # 4. Repositorios Cycle (factory desde ORM)
    cycle.repository.category:
        class: Cycle\ORM\Select\Repository
        factory: ['@Cycle\ORM\ORM', 'getRepository']
        arguments: [App\Infrastructure\Persistence\Cycle\Entity\CategoryEntity]

    # 5. Wrapper repository con inyeccion nombrada
    App\Infrastructure\Persistence\Cycle\Repository\CycleCategoryRepository:
        arguments:
            $repository: '@cycle.repository.category'
            $entityManager: '@Cycle\ORM\EntityManagerInterface'
```

### repositories.yaml - Alias de interfaces Domain
```yaml
services:
    App\Domain\Category\Repository\CategoryRepository:
        alias: App\Infrastructure\Persistence\Cycle\Repository\CycleCategoryRepository
        public: true
```

## Errores Comunes y Soluciones

### 1. JSON column devuelve string en vez de array
**Problema:** `#[Column(type: 'json')]` sin typecast devuelve string crudo.
**Solucion:** Siempre añadir `typecast: 'json'`:
```php
#[Column(type: 'json', typecast: 'json')]
public array $data = [];
```

### 2. LOWER/UPPER no funciona en WHERE
**Problema:** `->where('LOWER(label)', 'LIKE', $query)` genera SQL invalido.
**Solucion:** Usar `Fragment`:
```php
->where(new Fragment('LOWER("label") LIKE ?', "%{$query}%"))
```

### 3. Default values desincronizados
**Problema:** Default en PHP difiere del default en la BD.
**Solucion:** Definir en AMBOS lugares:
```php
// En la entidad Cycle:
#[Column(type: 'string(7)', default: '#6B7280')]
public string $colorHex = '#6B7280'; // AMBOS deben coincidir

// En la migracion SQL:
// ALTER TABLE ... ADD COLUMN color_hex VARCHAR(7) DEFAULT '#6B7280'
```

### 4. Repository no se resuelve en DI
**Problema:** Cycle repositories no son autowireables (Symfony no sabe crearlos).
**Solucion:** Registrar como factory en `cycle.yaml`:
```yaml
# 1. Crear el repo Cycle via factory del ORM
cycle.repository.pictogram:
    class: Cycle\ORM\Select\Repository
    factory: ['@Cycle\ORM\ORM', 'getRepository']
    arguments: [App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity]

# 2. Inyectar en el wrapper
App\Infrastructure\Persistence\Cycle\Repository\CyclePictogramRepository:
    arguments:
        $repository: '@cycle.repository.pictogram'
        $entityManager: '@Cycle\ORM\EntityManagerInterface'

# 3. Alias a la interfaz del Domain
App\Domain\Pictogram\Repository\PictogramRepository:
    alias: App\Infrastructure\Persistence\Cycle\Repository\CyclePictogramRepository
```

### 5. Entidad no encontrada por el schema compiler
**Problema:** Nueva entidad Cycle no es detectada por el ORM.
**Solucion:** Verificar que:
- El archivo esta en el directorio configurado en `OrmFactory` (`src/Infrastructure/Persistence/Cycle/Entity`)
- Tiene el atributo `#[Entity(table: 'xxx')]`
- Ejecutar `php bin/console cache:clear`

### 6. persist() no guarda cambios
**Problema:** `$entityManager->persist($entity)` sin efecto.
**Solucion:** Siempre llamar `run()` despues:
```php
$this->entityManager->persist($entity);
$this->entityManager->run(); // NECESARIO: flush al DB
```

## Checklist Nuevo Repository

- [ ] Crear entidad Cycle en `Infrastructure/Persistence/Cycle/Entity/`
- [ ] Crear mapper en `Infrastructure/Persistence/Cycle/Mapper/`
- [ ] Crear interface en `Domain/.../Repository/`
- [ ] Crear implementacion en `Infrastructure/Persistence/Cycle/Repository/`
- [ ] Registrar factory en `config/packages/cycle.yaml`
- [ ] Crear alias en `config/packages/repositories.yaml`
- [ ] Ejecutar `cache:clear`
- [ ] Verificar que el DI container compila
