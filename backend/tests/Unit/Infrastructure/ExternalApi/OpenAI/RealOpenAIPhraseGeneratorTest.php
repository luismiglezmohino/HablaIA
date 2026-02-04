<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\ExternalApi\OpenAI\Exception\OpenAIException;
use App\Infrastructure\ExternalApi\OpenAI\RealOpenAIPhraseGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

describe('RealOpenAIPhraseGenerator', function (): void {
    it('implements PhraseGeneratorInterface', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $generator = new RealOpenAIPhraseGenerator(
            $httpClient,
            'sk-test-key',
            'gpt-4o-mini',
            0.7,
            150
        );

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    describe('generate', function (): void {
        it('calls OpenAI API with correct parameters', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => "1. Quiero comer pan\n2. Me gustaría comer pan\n3. Necesito comer pan",
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'POST',
                    'https://api.openai.com/v1/chat/completions',
                    $this->callback(function (array $options): bool {
                        return isset($options['headers']['Authorization'])
                            && str_starts_with($options['headers']['Authorization'], 'Bearer ')
                            && isset($options['json']['model'])
                            && isset($options['json']['messages'])
                            && isset($options['json']['temperature'])
                            && isset($options['json']['max_tokens']);
                    })
                )
                ->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence);
        });

        it('returns parsed variations from JSON API response', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"variations": ["Quiero comer pan", "Me gustaría comer pan", "Necesito comer pan"]}',
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150,
                ['comer', 'pan']
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
            ]);

            $result = $generator->generate($sequence);

            expect($result)->toHaveCount(3);
            expect($result[0])->toBe('Quiero comer pan');
            expect($result[1])->toBe('Me gustaría comer pan');
            expect($result[2])->toBe('Necesito comer pan');
        });

        it('falls back to numbered lines parsing if JSON fails', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => "1. Quiero comer pan\n2. Me gustaría comer pan\n3. Necesito comer pan",
                        ],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150,
                ['comer', 'pan']
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
            ]);

            $result = $generator->generate($sequence);

            expect($result)->toHaveCount(3);
            expect($result[0])->toBe('Quiero comer pan');
        });

        it('throws OpenAIException on API error', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(401);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Invalid API key',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-invalid-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence))
                ->toThrow(OpenAIException::class);
        });

        it('throws OpenAIException on rate limit', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(429);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Rate limit exceeded',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence))
                ->toThrow(OpenAIException::class);
        });

        it('uses configured model', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => "1. Frase uno\n2. Frase dos\n3. Frase tres",
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
                        return $options['json']['model'] === 'gpt-4o';
                    })
                )
                ->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o', // Different model
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence);
        });

        it('uses configured temperature', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => "1. Frase uno\n2. Frase dos\n3. Frase tres",
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
                        return $options['json']['temperature'] === 0.5;
                    })
                )
                ->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.5, // Different temperature
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence);
        });

        it('throws OpenAIException on server error (500)', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(500);
            $response->method('toArray')->willReturn([
                'error' => [
                    'message' => 'Internal server error',
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence))
                ->toThrow(OpenAIException::class);
        });

        it('throws OpenAIException on invalid response without choices', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'id' => 'chatcmpl-123',
                // Missing 'choices' key
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence))
                ->toThrow(OpenAIException::class);
        });

        it('throws OpenAIException on empty choices array', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            expect(fn () => $generator->generate($sequence))
                ->toThrow(OpenAIException::class);
        });

        it('sets timeout in request options', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                'choices' => [
                    [
                        'message' => [
                            'content' => "1. Frase uno\n2. Frase dos\n3. Frase tres",
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

            $generator = new RealOpenAIPhraseGenerator(
                $httpClient,
                'sk-test-key',
                'gpt-4o-mini',
                0.7,
                150
            );

            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $generator->generate($sequence);
        });
    });
});
