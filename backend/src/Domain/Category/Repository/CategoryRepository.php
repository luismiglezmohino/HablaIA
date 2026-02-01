<?php

declare(strict_types=1);

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;

interface CategoryRepository
{
    public function findById(CategoryId $id): ?Category;

    public function findByName(string $name): ?Category;

    /**
     * @return array<Category>
     */
    public function findAll(): array;

    public function save(Category $category): void;
}
