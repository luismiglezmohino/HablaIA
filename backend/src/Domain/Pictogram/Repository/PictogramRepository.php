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

    /**
     * Search pictograms by label using LIKE query (case insensitive).
     *
     * @param string $query The search term
     * @param int $limit Maximum number of results
     * @return array<Pictogram>
     */
    public function findByLabelLike(string $query, int $limit = 10): array;

    /**
     * Find a pictogram by its ARASAAC ID.
     */
    public function findByArasaacId(int $arasaacId): ?Pictogram;

    public function save(Pictogram $pictogram): void;
}
