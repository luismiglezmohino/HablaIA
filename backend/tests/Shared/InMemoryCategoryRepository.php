<?php

declare(strict_types=1);

namespace Tests\Shared;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;

/**
 * In-memory implementation of CategoryRepository for testing.
 */
final class InMemoryCategoryRepository implements CategoryRepository
{
    /** @var array<string, Category> */
    private array $categories = [];

    public function findById(CategoryId $id): ?Category
    {
        return $this->categories[$id->value()] ?? null;
    }

    public function findByName(string $name): ?Category
    {
        foreach ($this->categories as $category) {
            if ($category->name() === $name) {
                return $category;
            }
        }

        return null;
    }

    /** @return array<Category> */
    public function findAll(): array
    {
        return array_values($this->categories);
    }

    public function save(Category $category): void
    {
        $this->categories[$category->id()->value()] = $category;
    }
}
