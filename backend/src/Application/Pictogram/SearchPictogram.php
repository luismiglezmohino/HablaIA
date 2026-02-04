<?php

declare(strict_types=1);

namespace App\Application\Pictogram;

use App\Application\DTO\PictogramDTO;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Service\ImageDownloaderInterface;
use InvalidArgumentException;

/**
 * Use case for searching pictograms.
 *
 * Flow:
 * 1. Search in local database by label (LIKE query)
 * 2. If local results found, return them
 * 3. If NO local results, search in ARASAAC API
 * 4. For each ARASAAC pictogram:
 *    a. Check if already exists locally by arasaacId
 *    b. If not, download image to public/pictograms/
 *    c. Save to database with local path
 * 5. Return pictograms (max 10)
 */
final readonly class SearchPictogram
{
    private const int MAX_RESULTS = 10;
    private const int MIN_QUERY_LENGTH = 2;
    private const string DEFAULT_LANGUAGE = 'es';
    private const string DEFAULT_CATEGORY_ID = '00000000-0000-4000-8000-000000000000';

    public function __construct(
        private PictogramRepository $repository,
        private PictogramProviderInterface $pictogramProvider,
        private ImageDownloaderInterface $imageDownloader,
        private UuidGeneratorInterface $uuidGenerator,
        private string $pictogramsBasePath
    ) {
    }

    /**
     * Search pictograms by keyword.
     *
     * @param string $query The search term (min 2 characters)
     * @return array<PictogramDTO>
     * @throws InvalidArgumentException When query is too short
     */
    public function __invoke(string $query): array
    {
        $sanitizedQuery = $this->sanitizeQuery($query);

        if (mb_strlen($sanitizedQuery) < self::MIN_QUERY_LENGTH) {
            throw new InvalidArgumentException('Search query must be at least 2 characters');
        }

        // Step 1: Search locally
        $localPictograms = $this->repository->findByLabelLike($sanitizedQuery, self::MAX_RESULTS);

        if (!empty($localPictograms)) {
            return $this->mapToDto($localPictograms);
        }

        // Step 2: Search in ARASAAC API
        $arasaacPictograms = $this->pictogramProvider->searchByKeyword($sanitizedQuery, self::DEFAULT_LANGUAGE);

        if (empty($arasaacPictograms)) {
            return [];
        }

        // Step 3: Process ARASAAC results (limit to MAX_RESULTS)
        $pictogramsToProcess = array_slice($arasaacPictograms, 0, self::MAX_RESULTS);
        $savedPictograms = [];

        foreach ($pictogramsToProcess as $arasaacPictogram) {
            $existingPictogram = $this->repository->findByArasaacId(
                $arasaacPictogram->arasaacId()->value()
            );

            if ($existingPictogram !== null) {
                $savedPictograms[] = $existingPictogram;
                continue;
            }

            $savedPictogram = $this->downloadAndSavePictogram($arasaacPictogram);
            if ($savedPictogram !== null) {
                $savedPictograms[] = $savedPictogram;
            }
        }

        return $this->mapToDto($savedPictograms);
    }

    private function sanitizeQuery(string $query): string
    {
        $trimmed = trim($query);

        // Remove potentially dangerous characters for SQL while keeping meaningful search terms
        return preg_replace('/[\'";\\\\%_]/', '', $trimmed) ?? $trimmed;
    }

    private function downloadAndSavePictogram(Pictogram $arasaacPictogram): ?Pictogram
    {
        $arasaacId = $arasaacPictogram->arasaacId()->value();
        $localFileName = "{$arasaacId}.png";
        $localPath = "/pictograms/{$localFileName}";
        $fullTargetPath = "{$this->pictogramsBasePath}/{$localFileName}";

        // Download the image
        $downloadSuccess = $this->imageDownloader->download(
            $arasaacPictogram->imagePath(),
            $fullTargetPath
        );

        if (!$downloadSuccess) {
            return null;
        }

        // Create new pictogram with local path
        $newPictogram = new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            new ArasaacId($arasaacId),
            CategoryId::fromString(self::DEFAULT_CATEGORY_ID),
            $arasaacPictogram->label(),
            $localPath
        );

        $this->repository->save($newPictogram);

        return $newPictogram;
    }

    /**
     * @param array<Pictogram> $pictograms
     * @return array<PictogramDTO>
     */
    private function mapToDto(array $pictograms): array
    {
        return array_map(
            fn (Pictogram $pictogram): PictogramDTO => PictogramDTO::fromEntity($pictogram),
            $pictograms
        );
    }
}
