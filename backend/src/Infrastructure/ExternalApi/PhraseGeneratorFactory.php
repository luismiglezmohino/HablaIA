<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Infrastructure\ExternalApi\Gemini\GeminiPhraseGenerator;
use App\Infrastructure\Phrase\FakePhraseGenerator;
use App\Infrastructure\ExternalApi\OpenAI\RealOpenAIPhraseGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhraseGeneratorFactory
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $provider,
        private readonly string $geminiApiUrl,
        private readonly string $geminiApiKey,
        private readonly string $geminiModel,
        private readonly float $temperature,
        private readonly int $maxTokens,
        private readonly int $timeout,
        private readonly string $openaiApiUrl,
        private readonly string $openaiApiKey,
        private readonly string $openaiModel,
    ) {
    }

    public function create(): PhraseGeneratorInterface
    {
        return match ($this->provider) {
            'gemini' => new GeminiPhraseGenerator(
                $this->httpClient,
                $this->geminiApiUrl,
                $this->geminiApiKey,
                $this->geminiModel,
                $this->temperature,
                $this->maxTokens,
                $this->timeout,
            ),
            'openai' => new RealOpenAIPhraseGenerator(
                $this->httpClient,
                $this->openaiApiUrl,
                $this->openaiApiKey,
                $this->openaiModel,
                $this->temperature,
                $this->maxTokens,
                $this->timeout,
            ),
            'fake' => new FakePhraseGenerator(),
            default => new FakePhraseGenerator(), // Fallback seguro para valores inválidos
        };
    }
}
