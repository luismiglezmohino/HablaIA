<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Service;

use App\Domain\Pictogram\Entity\Pictogram;

/**
 * Contract for pictogram provider services.
 *
 * Implementations may use different pictogram sources:
 * - ARASAAC API (default)
 * - Mulberry Symbols
 * - Custom pictogram sets
 */
interface PictogramProviderInterface
{
    /**
     * Search pictograms by keyword.
     *
     * @return array<Pictogram>
     */
    public function searchByKeyword(string $keyword, string $language = 'es'): array;

    /**
     * Fetch a pictogram by its provider-specific ID.
     */
    public function fetchById(int $providerId): ?Pictogram;

    /**
     * Sync pictograms from the provider to local storage.
     *
     * @param array<string> $keywords Keywords to sync
     * @return int Number of pictograms synced
     */
    public function sync(array $keywords): int;
}
