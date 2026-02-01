<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Repository;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\PictogramId;

interface PictogramRepository
{
    public function findById(PictogramId $id): ?Pictogram;

    /**
     * @return array<Pictogram>
     */
    public function findByCategoryId(CategoryId $categoryId): array;

    /**
     * @return array<Pictogram>
     */
    public function findAll(): array;

    public function save(Pictogram $pictogram): void;
}
