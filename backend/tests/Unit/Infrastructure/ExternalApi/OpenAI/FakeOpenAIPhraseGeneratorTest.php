<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\ExternalApi\OpenAI\FakeOpenAIPhraseGenerator;

describe('FakeOpenAIPhraseGenerator', function (): void {
    it('implements PhraseGeneratorInterface', function (): void {
        $generator = new FakeOpenAIPhraseGenerator();

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    describe('generate', function (): void {
        it('returns exactly 3 variations', function (): void {
            $generator = new FakeOpenAIPhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            expect($result)->toHaveCount(3);
        });

        it('returns array of strings', function (): void {
            $generator = new FakeOpenAIPhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            foreach ($result as $phrase) {
                expect($phrase)->toBeString();
            }
        });

        it('generates variations with single label', function (): void {
            $generator = new FakeOpenAIPhraseGenerator(['comer']);
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            expect($result[0])->toContain('comer');
        });

        it('generates variations with multiple labels', function (): void {
            $generator = new FakeOpenAIPhraseGenerator(['quiero', 'comer', 'pan']);
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440003'),
            ]);

            $result = $generator->generate($sequence);

            // Al menos una variación debe contener las palabras
            $hasLabels = false;
            foreach ($result as $phrase) {
                if (str_contains($phrase, 'comer') && str_contains($phrase, 'pan')) {
                    $hasLabels = true;
                    break;
                }
            }
            expect($hasLabels)->toBeTrue();
        });

        it('uses predefined templates', function (): void {
            $generator = new FakeOpenAIPhraseGenerator(['agua']);
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            // Debe usar uno de los templates predefinidos
            $knownPrefixes = ['Quiero', 'Me gustaría', 'Necesito'];
            $usesKnownPrefix = false;
            foreach ($result as $phrase) {
                foreach ($knownPrefixes as $prefix) {
                    if (str_starts_with($phrase, $prefix)) {
                        $usesKnownPrefix = true;
                        break 2;
                    }
                }
            }
            expect($usesKnownPrefix)->toBeTrue();
        });

        it('generates different variations', function (): void {
            $generator = new FakeOpenAIPhraseGenerator(['comer']);
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            // Las 3 variaciones deben ser diferentes
            expect($result)->toHaveCount(3);
            expect(array_unique($result))->toHaveCount(3);
        });

        it('works with empty labels array', function (): void {
            $generator = new FakeOpenAIPhraseGenerator([]);
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            expect($result)->toHaveCount(3);
            foreach ($result as $phrase) {
                expect($phrase)->toBeString();
            }
        });

        it('works without labels constructor argument', function (): void {
            $generator = new FakeOpenAIPhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence);

            expect($result)->toHaveCount(3);
        });
    });
});
