<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity;
use App\Infrastructure\Persistence\Cycle\Repository\CyclePictogramRepository;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

describe('CyclePictogramRepository', function (): void {
    it('implements PictogramRepository interface', function (): void {
        $ormRepository = $this->createMock(Repository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $repository = new CyclePictogramRepository($ormRepository, $em);

        expect($repository)->toBeInstanceOf(PictogramRepository::class);
    });

    describe('findById', function (): void {
        it('returns null when pictogram not found', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findById(PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toBeNull();
        });

        it('returns Pictogram when found', function (): void {
            $entity = new PictogramEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440001';
            $entity->arasaacId = 12345;
            $entity->categoryId = '550e8400-e29b-41d4-a716-446655440000';
            $entity->label = 'comer';
            $entity->imagePath = '/images/12345.png';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findById(PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'));

            expect($result)->toBeInstanceOf(Pictogram::class);
            expect($result->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440001');
            expect($result->label())->toBe('comer');
        });
    });

    describe('findByCategoryId', function (): void {
        it('returns empty array when no pictograms in category', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findByCategoryId(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toBe([]);
        });

        it('returns array of Pictograms when they exist', function (): void {
            $entity1 = new PictogramEntity();
            $entity1->id = '550e8400-e29b-41d4-a716-446655440001';
            $entity1->arasaacId = 12345;
            $entity1->categoryId = '550e8400-e29b-41d4-a716-446655440000';
            $entity1->label = 'comer';
            $entity1->imagePath = '/images/12345.png';

            $entity2 = new PictogramEntity();
            $entity2->id = '550e8400-e29b-41d4-a716-446655440002';
            $entity2->arasaacId = 12346;
            $entity2->categoryId = '550e8400-e29b-41d4-a716-446655440000';
            $entity2->label = 'beber';
            $entity2->imagePath = '/images/12346.png';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([$entity1, $entity2]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findByCategoryId(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toHaveCount(2);
            expect($result[0])->toBeInstanceOf(Pictogram::class);
            expect($result[1])->toBeInstanceOf(Pictogram::class);
            expect($result[0]->label())->toBe('comer');
            expect($result[1]->label())->toBe('beber');
        });
    });

    describe('findAll', function (): void {
        it('returns empty array when no pictograms exist', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findAll();

            expect($result)->toBe([]);
        });

        it('returns array of Pictograms when they exist', function (): void {
            $entity = new PictogramEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440001';
            $entity->arasaacId = 12345;
            $entity->categoryId = '550e8400-e29b-41d4-a716-446655440000';
            $entity->label = 'comer';
            $entity->imagePath = '/images/12345.png';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([$entity]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findAll();

            expect($result)->toHaveCount(1);
            expect($result[0])->toBeInstanceOf(Pictogram::class);
        });
    });

    describe('save', function (): void {
        it('persists pictogram through entity manager', function (): void {
            $ormRepository = $this->createMock(Repository::class);

            $em = $this->createMock(EntityManagerInterface::class);
            $em->expects($this->once())->method('persist');
            $em->expects($this->once())->method('run');

            $repository = new CyclePictogramRepository($ormRepository, $em);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                new ArasaacId(12345),
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'comer',
                '/images/12345.png'
            );

            $repository->save($pictogram);
        });
    });

    describe('findByArasaacId', function (): void {
        it('returns null when pictogram not found by ARASAAC ID', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findByArasaacId(99999);

            expect($result)->toBeNull();
        });

        it('returns Pictogram when found by ARASAAC ID', function (): void {
            $entity = new PictogramEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440001';
            $entity->arasaacId = 12345;
            $entity->categoryId = '550e8400-e29b-41d4-a716-446655440000';
            $entity->label = 'comer';
            $entity->imagePath = '/images/12345.png';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CyclePictogramRepository($ormRepository, $em);
            $result = $repository->findByArasaacId(12345);

            expect($result)->toBeInstanceOf(Pictogram::class);
            expect($result->arasaacId()->value())->toBe(12345);
            expect($result->label())->toBe('comer');
        });
    });
});
