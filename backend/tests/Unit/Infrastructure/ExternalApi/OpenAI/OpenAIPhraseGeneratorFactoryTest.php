<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Infrastructure\ExternalApi\OpenAI\FakeOpenAIPhraseGenerator;
use App\Infrastructure\ExternalApi\OpenAI\OpenAIPhraseGeneratorFactory;
use App\Infrastructure\ExternalApi\OpenAI\RealOpenAIPhraseGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

describe('OpenAIPhraseGeneratorFactory', function (): void {
    it('creates FakeOpenAIPhraseGenerator when disabled', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new OpenAIPhraseGeneratorFactory(
            $httpClient,
            false,
            'sk-test-key',
            'gpt-4o-mini',
            0.7,
            150
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(FakeOpenAIPhraseGenerator::class);
        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    it('creates RealOpenAIPhraseGenerator when enabled', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new OpenAIPhraseGeneratorFactory(
            $httpClient,
            true,
            'sk-test-key',
            'gpt-4o-mini',
            0.7,
            150
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(RealOpenAIPhraseGenerator::class);
        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    it('passes configuration to RealOpenAIPhraseGenerator', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $factory = new OpenAIPhraseGeneratorFactory(
            $httpClient,
            true,
            'sk-custom-key',
            'gpt-4o',
            0.9,
            200
        );

        $generator = $factory->create();

        expect($generator)->toBeInstanceOf(RealOpenAIPhraseGenerator::class);
    });
});
