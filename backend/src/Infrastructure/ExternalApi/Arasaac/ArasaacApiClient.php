<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Arasaac;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\ExternalApi\Arasaac\Exception\ArasaacApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client for ARASAAC API to search and fetch pictograms.
 *
 * @see https://arasaac.org/developers/api
 */
final class ArasaacApiClient implements PictogramProviderInterface
{
    private const string API_BASE_URL = 'https://api.arasaac.org/v1/pictograms';
    private const string CDN_BASE_URL = 'https://static.arasaac.org/pictograms';
    private const int DEFAULT_IMAGE_RESOLUTION = 500;
    // Valid UUID v4: position 14='4', position 19='8'
    private const string DEFAULT_CATEGORY_ID = '00000000-0000-4000-8000-000000000000';
    private const array ALLOWED_LANGUAGES = ['es', 'en', 'fr', 'de', 'it', 'pt', 'ca', 'eu', 'gl'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?UuidGeneratorInterface $uuidGenerator = null
    ) {
    }

    /**
     * @return array<Pictogram>
     */
    public function searchByKeyword(string $keyword, string $language = 'es'): array
    {
        $safeLanguage = $this->validateLanguage($language);
        $url = sprintf('%s/%s/search/%s', self::API_BASE_URL, $safeLanguage, urlencode($keyword));

        $response = $this->httpClient->request('GET', $url);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw ArasaacApiException::apiError('Search request failed', $statusCode);
        }

        $data = $response->toArray();

        // Prefer AAC pictograms, but fallback to all if none have aac: true
        $aacOnly = array_filter(
            $data,
            fn (array $item): bool => ($item['aac'] ?? false) === true
        );

        $results = !empty($aacOnly) ? $aacOnly : $data;

        return array_values(array_map(
            fn (array $item) => $this->mapToPictogram($item),
            $results
        ));
    }

    public function fetchById(int $providerId): ?Pictogram
    {
        $url = sprintf('%s/es/%d', self::API_BASE_URL, $providerId);

        $response = $this->httpClient->request('GET', $url);
        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            return null;
        }

        if ($statusCode !== 200) {
            throw ArasaacApiException::apiError('Fetch by ID request failed', $statusCode);
        }

        return $this->mapToPictogram($response->toArray());
    }

    /**
     * @param array<string> $keywords
     */
    public function sync(array $keywords): int
    {
        if (empty($keywords)) {
            return 0;
        }

        $count = 0;
        foreach ($keywords as $keyword) {
            $pictograms = $this->searchByKeyword($keyword);
            $count += count($pictograms) > 0 ? 1 : 0;
        }

        return $count;
    }

    /**
     * Get the CDN URL for a pictogram image.
     */
    public function getImageUrl(int $arasaacId, int $resolution = self::DEFAULT_IMAGE_RESOLUTION): string
    {
        return sprintf(
            '%s/%d/%d_%d.png',
            self::CDN_BASE_URL,
            $arasaacId,
            $arasaacId,
            $resolution
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapToPictogram(array $data): Pictogram
    {
        $arasaacId = (int) ($data['_id'] ?? 0);
        $label = $this->extractLabel($data);

        return new Pictogram(
            PictogramId::fromString($this->generateUuid()),
            new ArasaacId($arasaacId),
            CategoryId::fromString(self::DEFAULT_CATEGORY_ID),
            $label,
            $this->getImageUrl($arasaacId)
        );
    }

    private function generateUuid(): string
    {
        if ($this->uuidGenerator !== null) {
            return $this->uuidGenerator->generate();
        }

        // Fallback: use Symfony Uid directly
        return \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractLabel(array $data): string
    {
        $keywords = $data['keywords'] ?? [];

        if (empty($keywords)) {
            return 'sin etiqueta';
        }

        return $keywords[0]['keyword'] ?? 'sin etiqueta';
    }

    /**
     * Validate and sanitize language parameter.
     */
    private function validateLanguage(string $language): string
    {
        $normalized = strtolower(trim($language));

        if (!in_array($normalized, self::ALLOWED_LANGUAGES, true)) {
            return 'es'; // Default to Spanish
        }

        return $normalized;
    }
}
