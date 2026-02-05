<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Infrastructure\ExternalApi\OpenAI\Exception\OpenAIException;
use App\Infrastructure\ExternalApi\Shared\PhrasePrompt;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RealOpenAIPhraseGenerator implements PhraseGeneratorInterface
{
    /** @var array<string> */
    private array $currentLabels = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly float $temperature,
        private readonly int $maxTokens,
        private readonly int $timeout,
    ) {
    }

    /**
     * @return array<string>
     */
    public function generate(PictogramSequence $sequence, array $labels): array
    {
        $this->currentLabels = $labels;

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $this->buildRequestBody(),
            'timeout' => $this->timeout,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRequestBody(): array
    {
        return [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => PhrasePrompt::SYSTEM,
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildUserPrompt(),
                ],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];
    }

    private function buildUserPrompt(): string
    {
        $sanitizedLabels = array_map(
            fn (string $label) => $this->sanitizeLabel($label),
            $this->currentLabels
        );

        $labelsText = empty($sanitizedLabels) ? 'pictogramas' : implode(', ', $sanitizedLabels);

        return sprintf(PhrasePrompt::USER_TEMPLATE, $labelsText);
    }

    /**
     * Sanitize label to prevent prompt injection.
     */
    private function sanitizeLabel(string $label): string
    {
        // Remove potentially dangerous characters for prompt injection
        $sanitized = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $label);
        $sanitized = trim($sanitized ?? '');

        // Limit length
        if (mb_strlen($sanitized) > PhrasePrompt::MAX_LABEL_LENGTH) {
            $sanitized = mb_substr($sanitized, 0, PhrasePrompt::MAX_LABEL_LENGTH);
        }

        return $sanitized ?: 'elemento';
    }

    /**
     * @return array<string>
     */
    private function handleResponse(mixed $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw OpenAIException::rateLimitExceeded();
        }

        if ($statusCode !== 200) {
            // Sanitize error message - don't expose raw API response details
            throw OpenAIException::apiError('Phrase generation request failed', $statusCode);
        }

        return $this->parseResponse($response->toArray());
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string>
     */
    private function parseResponse(array $data): array
    {
        if (!isset($data['choices']) || !is_array($data['choices'])) {
            throw OpenAIException::invalidResponse('Missing choices');
        }

        if (empty($data['choices'])) {
            throw OpenAIException::invalidResponse('Empty choices array');
        }

        $content = $data['choices'][0]['message']['content'] ?? '';

        if (empty($content)) {
            throw OpenAIException::invalidResponse('Empty content');
        }

        return $this->parseVariations($content);
    }

    /**
     * @return array<string>
     */
    private function parseVariations(string $content): array
    {
        // Try to parse as JSON first
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['variations']) && is_array($decoded['variations'])) {
            return array_slice($decoded['variations'], 0, PhrasePrompt::VARIATIONS_COUNT);
        }

        // Fallback: parse numbered lines (for backwards compatibility)
        $lines = explode("\n", $content);
        $variations = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Remove numbering (e.g., "1. ", "2. ", "3. ")
            $cleaned = preg_replace('/^\d+\.\s*/', '', $line);
            if ($cleaned !== null && $cleaned !== '') {
                $variations[] = $cleaned;
            }
        }

        return array_slice($variations, 0, PhrasePrompt::VARIATIONS_COUNT);
    }
}

