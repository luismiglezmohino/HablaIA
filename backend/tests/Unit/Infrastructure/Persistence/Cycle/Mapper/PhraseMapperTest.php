<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PhraseEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\PhraseMapper;
use DateTimeImmutable;

describe('PhraseMapper', function (): void {
    it('converts PhraseEntity to Phrase domain entity', function (): void {
        $createdAt = new DateTimeImmutable('2024-01-15 10:30:00');

        $entity = new PhraseEntity();
        $entity->id = '550e8400-e29b-41d4-a716-446655440002';
        $entity->sequenceHash = hash('sha256', 'uuid-1|uuid-2');
        $entity->pictogramIds = [
            '550e8400-e29b-41d4-a716-446655440010',
            '550e8400-e29b-41d4-a716-446655440011',
        ];
        $entity->variations = ['Quiero comer pan', 'Me gustaría comer pan'];
        $entity->createdAt = $createdAt;

        $domain = PhraseMapper::toDomain($entity);

        expect($domain)->toBeInstanceOf(Phrase::class);
        expect($domain->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440002');
        expect($domain->variations())->toBe(['Quiero comer pan', 'Me gustaría comer pan']);
        expect($domain->createdAt())->toEqual($createdAt);
        expect($domain->pictogramSequence()->count())->toBe(2);
    });

    it('converts Phrase domain entity to PhraseEntity', function (): void {
        $phraseId = PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $pictogramSequence = new PictogramSequence([
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440011'),
        ]);
        $createdAt = new DateTimeImmutable('2024-01-15 10:30:00');

        $domain = new Phrase($phraseId, $pictogramSequence, ['Quiero comer pan'], $createdAt);

        $entity = PhraseMapper::toEntity($domain);

        expect($entity)->toBeInstanceOf(PhraseEntity::class);
        expect($entity->id)->toBe('550e8400-e29b-41d4-a716-446655440002');
        expect($entity->sequenceHash)->toBe($pictogramSequence->hash());
        expect($entity->pictogramIds)->toBe([
            '550e8400-e29b-41d4-a716-446655440010',
            '550e8400-e29b-41d4-a716-446655440011',
        ]);
        expect($entity->variations)->toBe(['Quiero comer pan']);
        expect($entity->createdAt)->toEqual($createdAt);
    });

    it('preserves data through domain to entity to domain conversion', function (): void {
        $originalId = PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $pictogramSequence = new PictogramSequence([
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440011'),
        ]);
        $createdAt = new DateTimeImmutable('2024-01-15 10:30:00');
        $variations = ['Quiero comer pan', 'Me gustaría comer pan'];

        $original = new Phrase($originalId, $pictogramSequence, $variations, $createdAt);

        $entity = PhraseMapper::toEntity($original);
        $restored = PhraseMapper::toDomain($entity);

        expect($restored->id()->value())->toBe($original->id()->value());
        expect($restored->variations())->toBe($original->variations());
        expect($restored->createdAt())->toEqual($original->createdAt());
        expect($restored->pictogramSequence()->count())->toBe($original->pictogramSequence()->count());
    });

    it('preserves pictogram sequence order through conversion', function (): void {
        $phraseId = PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $pictogramSequence = new PictogramSequence([
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440011'),
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440012'),
        ]);

        $original = new Phrase($phraseId, $pictogramSequence, ['Test'], new DateTimeImmutable());

        $entity = PhraseMapper::toEntity($original);
        $restored = PhraseMapper::toDomain($entity);

        $originalIds = array_map(
            fn (PictogramId $id) => $id->value(),
            $original->pictogramSequence()->pictogramIds()
        );
        $restoredIds = array_map(
            fn (PictogramId $id) => $id->value(),
            $restored->pictogramSequence()->pictogramIds()
        );

        expect($restoredIds)->toBe($originalIds);
    });

    it('generates correct sequence hash from pictogram sequence', function (): void {
        $phraseId = PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $pictogramSequence = new PictogramSequence([
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
            PictogramId::fromString('550e8400-e29b-41d4-a716-446655440011'),
        ]);

        $domain = new Phrase($phraseId, $pictogramSequence, ['Test'], new DateTimeImmutable());
        $entity = PhraseMapper::toEntity($domain);

        expect($entity->sequenceHash)->toBe($pictogramSequence->hash());
    });

    it('preserves data through entity to domain to entity conversion', function (): void {
        $createdAt = new DateTimeImmutable('2024-01-15 10:30:00');

        $original = new PhraseEntity();
        $original->id = '550e8400-e29b-41d4-a716-446655440002';
        $original->sequenceHash = 'somehash';
        $original->pictogramIds = [
            '550e8400-e29b-41d4-a716-446655440010',
            '550e8400-e29b-41d4-a716-446655440011',
        ];
        $original->variations = ['Quiero comer pan', 'Me gustaría comer pan'];
        $original->createdAt = $createdAt;

        $domain = PhraseMapper::toDomain($original);
        $restored = PhraseMapper::toEntity($domain);

        expect($restored->id)->toBe($original->id);
        expect($restored->pictogramIds)->toBe($original->pictogramIds);
        expect($restored->variations)->toBe($original->variations);
        expect($restored->createdAt)->toEqual($original->createdAt);
    });
});
