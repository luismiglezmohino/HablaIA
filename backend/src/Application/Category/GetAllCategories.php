<?php

declare(strict_types=1);

namespace App\Application\Category;

use App\Application\DTO\CategoryDTO;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;

final readonly class GetAllCategories
{
    public function __construct(
        private CategoryRepository $repository
    ) {}

    /** @return array<CategoryDTO> */
    public function __invoke(): array
    {
        $categories = $this->repository->findAll();

        return array_map(
            function (Category $category): CategoryDTO {
                return CategoryDTO::fromEntity($category);
            },
            $categories
        );
    }
}
