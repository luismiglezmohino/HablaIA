<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Repository\PhraseRepository;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PhraseEntity;
use App\Infrastructure\Persistence\Cycle\Repository\CyclePhraseRepository;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;
use DateTimeImmutable;

describe('CyclePhraseRepository', function (): void {
    it('implements PhraseRepository interface', function (): void {
        $ormRepository = $this->createMock(Repository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $repository = new CyclePhraseRepository($ormRepository, $em);

        expect($repository)->toBeInstanceOf(PhraseRepository::class);
    });

    describe('findById', function (): void {
        it('returns null when phrase not found', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePhraseRepository($ormRepository, $em);
            $result = $repository->findById(PhraseId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toBeNull();
        });

        it('returns Phrase when found', function (): void {
            $entity = new PhraseEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440002';
            $entity->sequenceHash = 'somehash';
            $entity->pictogramIds = ['550e8400-e29b-41d4-a716-446655440010'];
            $entity->variations = ['Quiero comer pan'];
            $entity->createdAt = new DateTimeImmutable('2024-01-15 10:30:00');

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePhraseRepository($ormRepository, $em);
            $result = $repository->findById(PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002'));

            expect($result)->toBeInstanceOf(Phrase::class);
            expect($result->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440002');
            expect($result->variations())->toBe(['Quiero comer pan']);
        });
    });

    describe('findBySequenceHash', function (): void {
        it('returns null when phrase not found', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePhraseRepository($ormRepository, $em);
            $result = $repository->findBySequenceHash('nonexistenthash');

            expect($result)->toBeNull();
        });

        it('returns Phrase when found by hash', function (): void {
            $entity = new PhraseEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440002';
            $entity->sequenceHash = 'existinghash';
            $entity->pictogramIds = ['550e8400-e29b-41d4-a716-446655440010'];
            $entity->variations = ['Me gustaría comer pan'];
            $entity->createdAt = new DateTimeImmutable('2024-01-15 10:30:00');

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePhraseRepository($ormRepository, $em);
            $result = $repository->findBySequenceHash('existinghash');

            expect($result)->toBeInstanceOf(Phrase::class);
            expect($result->variations())->toBe(['Me gustaría comer pan']);
        });
    });

    describe('save', function (): void {
        it('persists phrase through entity manager', function (): void {
            $ormRepository = $this->createMock(Repository::class);

            $em = $this->createMock(EntityManagerInterface::class);
            $em->expects($this->once())->method('persist');
            $em->expects($this->once())->method('run');

            $repository = new CyclePhraseRepository($ormRepository, $em);

            $phrase = new Phrase(
                PhraseId::fromString('550e8400-e29b-41d4-a716-446655440002'),
                new PictogramSequence([
                    PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                ]),
                ['Quiero comer pan'],
                new DateTimeImmutable()
            );

            $repository->save($phrase);
        });
    });
});
