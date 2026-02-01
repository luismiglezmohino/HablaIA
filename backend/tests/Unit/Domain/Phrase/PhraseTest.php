<?php

declare(strict_types=1);

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Exception\InvalidPhraseVariationsException;
use App\Domain\Phrase\Exception\InvalidPictogramSequenceException;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;

describe('Phrase Entity', function (): void {
    it('can be created with valid data', function (): void {
        $id = PhraseId::generate();
        $pictogramIds = [PictogramId::generate(), PictogramId::generate()];
        $sequence = new PictogramSequence($pictogramIds);
        $variations = [
            'Quiero comer',
            'Me gustaría comer algo',
            'Tengo ganas de comer',
        ];
        $createdAt = new DateTimeImmutable();

        $phrase = new Phrase(
            id: $id,
            pictogramSequence: $sequence,
            variations: $variations,
            createdAt: $createdAt
        );

        expect($phrase->id())->toBe($id);
        expect($phrase->pictogramSequence())->toBe($sequence);
        expect($phrase->variations())->toBe($variations);
        expect($phrase->createdAt())->toBe($createdAt);
    });

    it('requires at least one variation', function (): void {
        $id = PhraseId::generate();
        $sequence = new PictogramSequence([PictogramId::generate()]);

        new Phrase(
            id: $id,
            pictogramSequence: $sequence,
            variations: [],
            createdAt: new DateTimeImmutable()
        );
    })->throws(InvalidPhraseVariationsException::class, 'Phrase must have at least one variation');

    it('requires maximum 3 variations', function (): void {
        $id = PhraseId::generate();
        $sequence = new PictogramSequence([PictogramId::generate()]);

        new Phrase(
            id: $id,
            pictogramSequence: $sequence,
            variations: ['a', 'b', 'c', 'd'],
            createdAt: new DateTimeImmutable()
        );
    })->throws(InvalidPhraseVariationsException::class, 'Too many phrase variations: 4 (max: 3)');

    it('rejects empty variation strings', function (): void {
        $id = PhraseId::generate();
        $sequence = new PictogramSequence([PictogramId::generate()]);

        new Phrase(
            id: $id,
            pictogramSequence: $sequence,
            variations: ['Valid phrase', ''],
            createdAt: new DateTimeImmutable()
        );
    })->throws(InvalidPhraseVariationsException::class, 'Phrase must have at least one variation');

    it('rejects variations exceeding 500 characters', function (): void {
        $id = PhraseId::generate();
        $sequence = new PictogramSequence([PictogramId::generate()]);

        new Phrase(
            id: $id,
            pictogramSequence: $sequence,
            variations: [str_repeat('a', 501)],
            createdAt: new DateTimeImmutable()
        );
    })->throws(InvalidPhraseVariationsException::class, 'Phrase variation at index 0 is too long: 501 characters (max: 500)');
});

describe('PhraseId Value Object', function (): void {
    it('can be generated', function (): void {
        $id = PhraseId::generate();

        expect($id)->toBeInstanceOf(PhraseId::class);
        expect($id->value())->toBeString();
        expect(strlen($id->value()))->toBe(36);
    });

    it('can be created from string', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = PhraseId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    it('rejects invalid UUID format', function (): void {
        PhraseId::fromString('invalid-uuid');
    })->throws(InvalidArgumentException::class);

    it('can be compared for equality', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = PhraseId::fromString($uuid);
        $id2 = PhraseId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });
});

describe('PictogramSequence Value Object', function (): void {
    it('can be created with pictogram IDs', function (): void {
        $pictogramIds = [PictogramId::generate(), PictogramId::generate()];
        $sequence = new PictogramSequence($pictogramIds);

        expect($sequence->pictogramIds())->toBe($pictogramIds);
        expect($sequence->count())->toBe(2);
    });

    it('requires at least one pictogram', function (): void {
        new PictogramSequence([]);
    })->throws(InvalidPictogramSequenceException::class, 'Pictogram sequence cannot be empty');

    it('requires maximum 10 pictograms', function (): void {
        $pictogramIds = array_map(fn () => PictogramId::generate(), range(1, 11));
        new PictogramSequence($pictogramIds);
    })->throws(InvalidPictogramSequenceException::class, 'Too many pictograms in sequence: 11 (max: 10)');

    it('generates consistent hash for same sequence', function (): void {
        $id1 = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $id2 = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $sequence1 = new PictogramSequence([$id1, $id2]);
        $sequence2 = new PictogramSequence([$id1, $id2]);

        expect($sequence1->hash())->toBe($sequence2->hash());
    });

    it('generates different hash for different order', function (): void {
        $id1 = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $id2 = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $sequence1 = new PictogramSequence([$id1, $id2]);
        $sequence2 = new PictogramSequence([$id2, $id1]);

        expect($sequence1->hash())->not()->toBe($sequence2->hash());
    });
});
