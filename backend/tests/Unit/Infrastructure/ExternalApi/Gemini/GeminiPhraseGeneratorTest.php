<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\Gemini;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\ExternalApi\Gemini\Exception\GeminiException;
use App\Infrastructure\ExternalApi\Gemini\GeminiPhraseGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

describe('GeminiPhraseGenerator', function (): void {
    it('implements PhraseGeneratorInterface', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $generator = new GeminiPhraseGenerator(
            $httpClient,
            'https://generativelanguage.googleapis.com/v1beta/models',
            'test-api-key',
            'gemini-2.5-flash-lite',
            0.7,
            256,
            10
        );

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    describe('generate', function (): void {
        it('calls Gemini API with correct parameters', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"variations": ["Quiero comer pan", "Me gustaría comer pan", "Necesito comer pan"]}'],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->callback(function (string $url): bool {
                        return str_contains($url, 'generativelanguage.googleapis.com')
                            && str_contains($url, 'gemini-2.5-flash-lite')
                            && str_contains($url, 'generateContent')
                            && str_contains($url, 'key=');
                    }),
                    $this->callback(function (array $options): bool {
                        return isset($options['json']['contents'])
                            && isset($options['json']['generationConfig'])
                            && $options['json']['generationConfig']['responseMimeType'] === 'application/json';
                    })
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer', 'pan']);
        });

        it('includes labels in the prompt sent to Gemini', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"variations": ["Quiero comer", "Me gustaría comer", "Necesito comer"]}'],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->anything(),
                    $this->callback(function (array $options): bool {
                        $prompt = $options['json']['contents'][0]['parts'][0]['text'] ?? '';
                        return str_contains($prompt, 'comer');
                    })
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer']);
        });

        it('returns parsed variations from JSON API response', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"variations": ["Quiero comer pan", "Me gustaría comer pan", "Necesito comer pan"]}'],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
            ]);

            $result = $generator->generate($sequence, ['comer', 'pan']);

            expect($result)->toHaveCount(3);
            expect($result[0])->toBe('Quiero comer pan');
            expect($result[1])->toBe('Me gustaría comer pan');
            expect($result[2])->toBe('Necesito comer pan');
        });

        it('falls back to numbered lines parsing if JSON fails', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "1. Quiero comer pan\n2. Me gustaría comer pan\n3. Necesito comer pan"],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
            ]);

            $result = $generator->generate($sequence, ['comer', 'pan']);

            expect($result)->toHaveCount(3);
            expect($result[0])->toBe('Quiero comer pan');
        });

        it('throws GeminiException on API error', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(401);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Invalid API key',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'invalid-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('throws GeminiException on rate limit', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(429);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Rate limit exceeded',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('uses configured model', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "1. Frase uno\n2. Frase dos\n3. Frase tres"],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->callback(function (string $url): bool {
                        return str_contains($url, 'gemini-2.0-flash');
                    }),
                    $this->anything()
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.0-flash',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer']);
        });

        it('uses configured temperature', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "1. Frase uno\n2. Frase dos\n3. Frase tres"],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->anything(),
                    $this->callback(function (array $options): bool {
                        return $options['json']['generationConfig']['temperature'] === 0.5;
                    })
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.5,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer']);
        });

        it('throws GeminiException on server error (500)', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(500);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Internal server error',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('throws GeminiException on invalid response without candidates', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'modelVersion' => 'gemini-2.5-flash-lite',
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('throws GeminiException on empty candidates array', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('sets timeout in request options', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "1. Frase uno\n2. Frase dos\n3. Frase tres"],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->anything(),
                    $this->callback(function (array $options): bool {
                        return isset($options['timeout']) && $options['timeout'] > 0;
                    })
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer']);
        });

        it('falls back to secondary model on rate limit (429)', function (): void {
            $rateLimitResponse = $this->createMock(ResponseInterface::class);
            $rateLimitResponse->method('getStatusCode')->willReturn(429);

            $successResponse = $this->createMock(ResponseInterface::class);
            $successResponse->method('getStatusCode')->willReturn(200);
            $successResponse->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"variations": ["Quiero comer", "Me gustaría comer", "Necesito comer"]}'],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->exactly(2))
                ->method('request')
                ->willReturnCallback(function (string $method, string $url) use ($rateLimitResponse, $successResponse) {
                    if (str_contains($url, 'gemini-2.5-flash:')) {
                        return $rateLimitResponse;
                    }

                    return $successResponse;
                });

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash',
                0.7,
                256,
                10,
                'gemini-2.5-flash-lite'
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['comer']);

            expect($result)->toHaveCount(3);
            expect($result[0])->toBe('Quiero comer');
        });

        it('throws on rate limit when no fallback model configured', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(429);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence, ['comer']))
                ->toThrow(GeminiException::class);
        });

        it('sets responseMimeType to application/json', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"variations": ["Frase uno", "Frase dos", "Frase tres"]}'],
                            ],
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    $this->anything(),
                    $this->callback(function (array $options): bool {
                        return $options['json']['generationConfig']['responseMimeType'] === 'application/json';
                    })
                )
                ->willReturn($response);

            $generator = new GeminiPhraseGenerator(
                $httpClient,
                'https://generativelanguage.googleapis.com/v1beta/models',
                'test-api-key',
                'gemini-2.5-flash-lite',
                0.7,
                256,
                10
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence, ['comer']);
        });
    });
});
