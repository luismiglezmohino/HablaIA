<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Infrastructure\Persistence\Cycle\Entity\CategoryEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\CategoryMapper;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

final class CycleCategoryRepository implements CategoryRepository
{
    /**
     * @param Repository<CategoryEntity> $repository
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(CategoryId $id): ?Category
    {
        $entity = $this->repository->findByPK($id->value());

        if (!$entity instanceof CategoryEntity) {
            return null;
        }

        return CategoryMapper::toDomain($entity);
    }

    public function findByName(string $name): ?Category
    {
        $entity = $this->repository->findOne(['name' => $name]);

        if (!$entity instanceof CategoryEntity) {
            return null;
        }

        return CategoryMapper::toDomain($entity);
    }

    /**
     * @return array<Category>
     */
    public function findAll(): array
    {
        /** @var array<CategoryEntity> $entities */
        $entities = iterator_to_array($this->repository->findAll());

        return array_map(
            fn (CategoryEntity $entity) => CategoryMapper::toDomain($entity),
            $entities
        );
    }

    public function save(Category $category): void
    {
        $entity = CategoryMapper::toEntity($category);
        $this->entityManager->persist($entity);
        $this->entityManager->run();
    }
}
