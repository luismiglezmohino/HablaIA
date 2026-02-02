<?php

declare(strict_types=1);

namespace App\Application\Pictogram;

use App\Application\DTO\PictogramDTO;
use App\Application\Exception\CategoryNotFoundException;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;

final readonly class GetPictogramsByCategory
{
    public function __construct(
        private PictogramRepository $pictogramRepository,
        private CategoryRepository $categoryRepository
    ) {}

    /** @return array<PictogramDTO> */
    public function __invoke(string $categoryIdString): array
    {
        $categoryId = CategoryId::fromString($categoryIdString);

        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw CategoryNotFoundException::withId($categoryIdString);
        }

        $pictograms = $this->pictogramRepository->findByCategoryId($categoryId);

        return array_map(
            function (Pictogram $pictogram): PictogramDTO {
                return PictogramDTO::fromEntity($pictogram);
            },
            $pictograms
        );
    }
}
