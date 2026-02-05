<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Infrastructure\ExternalApi\Gemini\GeminiPhraseGenerator;
use App\Infrastructure\ExternalApi\OpenAI\FakeOpenAIPhraseGenerator;
use App\Infrastructure\ExternalApi\OpenAI\RealOpenAIPhraseGenerator;
use App\Infrastructure\ExternalApi\PhraseGeneratorFactory;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

describe('PhraseGeneratorFactory', function (): void {
    it('creates GeminiPhraseGenerator when provider is gemini', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'gemini',
            'https://generativelanguage.googleapis.com/v1beta/models',
            'gemini-api-key',
            'gemini-2.5-flash-lite',
            0.7,
            256,
            10,
            'https://api.openai.com/v1/chat/completions',
            'openai-api-key',
            'gpt-4o-mini'
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
        expect($generator)->toBeInstanceOf(GeminiPhraseGenerator::class);
    });

    it('creates RealOpenAIPhraseGenerator when provider is openai', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'openai',
            'https://generativelanguage.googleapis.com/v1beta/models',
            'gemini-api-key',
            'gemini-2.5-flash-lite',
            0.7,
            256,
            10,
            'https://api.openai.com/v1/chat/completions',
            'openai-api-key',
            'gpt-4o-mini'
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
        expect($generator)->toBeInstanceOf(RealOpenAIPhraseGenerator::class);
    });

    it('creates FakeOpenAIPhraseGenerator when provider is fake', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'fake',
            'https://generativelanguage.googleapis.com/v1beta/models',
            'gemini-api-key',
            'gemini-2.5-flash-lite',
            0.7,
            256,
            10,
            'https://api.openai.com/v1/chat/completions',
            'openai-api-key',
            'gpt-4o-mini'
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
        expect($generator)->toBeInstanceOf(FakeOpenAIPhraseGenerator::class);
    });

    it('throws InvalidArgumentException for unknown provider', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'unknown',
            'https://generativelanguage.googleapis.com/v1beta/models',
            'gemini-api-key',
            'gemini-2.5-flash-lite',
            0.7,
            256,
            10,
            'https://api.openai.com/v1/chat/completions',
            'openai-api-key',
            'gpt-4o-mini'
        );

        expect(fn () => $factory->create())
            ->toThrow(InvalidArgumentException::class, 'Unknown phrase provider "unknown". Supported: gemini, openai, fake');
    });

    it('passes correct parameters to Gemini generator', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'gemini',
            'https://custom-gemini-url.com',
            'custom-gemini-key',
            'custom-model',
            0.5,
            512,
            15,
            'https://api.openai.com/v1/chat/completions',
            'openai-api-key',
            'gpt-4o-mini'
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(GeminiPhraseGenerator::class);
    });

    it('passes correct parameters to OpenAI generator', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new PhraseGeneratorFactory(
            $httpClient,
            'openai',
            'https://generativelanguage.googleapis.com/v1beta/models',
            'gemini-api-key',
            'gemini-2.5-flash-lite',
            0.5,
            512,
            15,
            'https://custom-openai-url.com',
            'custom-openai-key',
            'gpt-4o'
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(RealOpenAIPhraseGenerator::class);
    });
});
