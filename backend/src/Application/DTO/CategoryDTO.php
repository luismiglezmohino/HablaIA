<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Category\Entity\Category;

final readonly class CategoryDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $icon
    ) {}

    public static function fromEntity(Category $category): self
    {
        return new self(
            id: $category->id()->value(),
            name: $category->name(),
            icon: $category->icon()
        );
    }
}
