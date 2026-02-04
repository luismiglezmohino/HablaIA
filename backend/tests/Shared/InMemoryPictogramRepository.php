<?php

declare(strict_types=1);

namespace Tests\Shared;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\PictogramId;

/**
 * In-memory implementation of PictogramRepository for testing.
 */
final class InMemoryPictogramRepository implements PictogramRepository
{
    /** @var array<string, Pictogram> */
    private array $pictograms = [];

    public function findById(PictogramId $id): ?Pictogram
    {
        return $this->pictograms[$id->value()] ?? null;
    }

    /** @return array<Pictogram> */
    public function findByCategoryId(CategoryId $categoryId): array
    {
        return array_values(
            array_filter(
                $this->pictograms,
                fn(Pictogram $p) => $p->categoryId()->equals($categoryId)
            )
        );
    }

    /** @return array<Pictogram> */
    public function findAll(): array
    {
        return array_values($this->pictograms);
    }

    /** @return array<Pictogram> */
    public function findByLabelLike(string $query, int $limit = 10): array
    {
        $normalizedQuery = mb_strtolower(trim($query));

        $results = array_filter(
            $this->pictograms,
            fn(Pictogram $p) => str_contains(mb_strtolower($p->label()), $normalizedQuery)
        );

        return array_slice(array_values($results), 0, $limit);
    }

    public function findByArasaacId(int $arasaacId): ?Pictogram
    {
        foreach ($this->pictograms as $pictogram) {
            if ($pictogram->arasaacId()->value() === $arasaacId) {
                return $pictogram;
            }
        }

        return null;
    }

    public function save(Pictogram $pictogram): void
    {
        $this->pictograms[$pictogram->id()->value()] = $pictogram;
    }
}
