<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Factory that creates the appropriate PhraseGenerator based on configuration.
 *
 * - OPENAI_ENABLED=false → FakeOpenAIPhraseGenerator (free, for dev/tests)
 * - OPENAI_ENABLED=true  → RealOpenAIPhraseGenerator (API calls, costs $)
 */
final class OpenAIPhraseGeneratorFactory
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly bool $enabled,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly float $temperature,
        private readonly int $maxTokens
    ) {
    }

    public function create(): PhraseGeneratorInterface
    {
        if (!$this->enabled) {
            return new FakeOpenAIPhraseGenerator();
        }

        return new RealOpenAIPhraseGenerator(
            $this->httpClient,
            $this->apiKey,
            $this->model,
            $this->temperature,
            $this->maxTokens,
        );
    }
}
