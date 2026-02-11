<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Phrase;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Phrase\FakePhraseGenerator;

describe('FakePhraseGenerator', function (): void {
    it('implements PhraseGeneratorInterface', function (): void {
        $generator = new FakePhraseGenerator();

        expect($generator)->toBeInstanceOf(PhraseGeneratorInterface::class);
    });

    describe('generate', function (): void {
        it('returns exactly 3 variations', function (): void {
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['comer']);

            expect($result)->toHaveCount(3);
        });

        it('returns array of strings', function (): void {
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['comer']);

            foreach ($result as $phrase) {
                expect($phrase)->toBeString();
            }
        });

        it('generates variations with single label', function (): void {
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['comer']);

            expect($result[0])->toContain('comer');
        });

        it('generates variations with multiple labels', function (): void {
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002'),
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440003'),
            ]);

            $result = $generator->generate($sequence, ['quiero', 'comer', 'pan']);

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
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['agua']);

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
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, ['comer']);

            expect($result)->toHaveCount(3);
            expect(array_unique($result))->toHaveCount(3);
        });

        it('falls back to "esto" with empty labels', function (): void {
            $generator = new FakePhraseGenerator();
            $sequence = new PictogramSequence([
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
            ]);

            $result = $generator->generate($sequence, []);

            expect($result)->toHaveCount(3);
            expect($result[0])->toContain('esto');
        });
    });
});
