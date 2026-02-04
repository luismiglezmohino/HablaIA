<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Infrastructure\ExternalApi\OpenAI\Exception\OpenAIException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RealOpenAIPhraseGenerator implements PhraseGeneratorInterface
{
    private const string API_URL = 'https://api.openai.com/v1/chat/completions';
    private const int TIMEOUT_SECONDS = 7;
    private const int VARIATIONS_COUNT = 3;
    private const int MAX_LABEL_LENGTH = 50;

    private const string SYSTEM_PROMPT = <<<PROMPT
Eres un asistente especializado en comunicación aumentativa y alternativa (SAAC).
Tu tarea es convertir palabras clave de pictogramas en frases naturales en español.
Genera exactamente 3 variaciones de la frase.
Las frases deben ser naturales, gramaticalmente correctas y apropiadas para usuarios de SAAC.
Responde SOLO con un JSON válido con este formato exacto:
{"variations": ["frase 1", "frase 2", "frase 3"]}
PROMPT;

    private const string USER_PROMPT_TEMPLATE = 'Genera 3 variaciones de frase natural para las siguientes palabras: %s';

    /**
     * @param array<string> $labels
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly float $temperature,
        private readonly int $maxTokens,
        private readonly array $labels = []
    ) {
    }

    /**
     * @return array<string>
     */
    public function generate(PictogramSequence $sequence): array
    {
        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $this->buildRequestBody(),
            'timeout' => self::TIMEOUT_SECONDS,
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
                    'content' => self::SYSTEM_PROMPT,
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
            $this->labels
        );

        $labelsText = empty($sanitizedLabels) ? 'pictogramas' : implode(', ', $sanitizedLabels);

        return sprintf(self::USER_PROMPT_TEMPLATE, $labelsText);
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
        if (mb_strlen($sanitized) > self::MAX_LABEL_LENGTH) {
            $sanitized = mb_substr($sanitized, 0, self::MAX_LABEL_LENGTH);
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
            return array_slice($decoded['variations'], 0, self::VARIATIONS_COUNT);
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

        return array_slice($variations, 0, self::VARIATIONS_COUNT);
    }
}

