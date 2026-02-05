<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Infrastructure\Persistence\Cycle\Entity\CategoryEntity;

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
        $entity->icon = $domain->icon();
        $entity->colorHex = $domain->colorHex();
        $entity->displayOrder = $domain->displayOrder();

        return $entity;
    }
}
