<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Infrastructure\Persistence\Cycle\Entity\CategoryEntity;
use App\Infrastructure\Persistence\Cycle\Repository\CycleCategoryRepository;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

describe('CycleCategoryRepository', function (): void {
    it('implements CategoryRepository interface', function (): void {
        $ormRepository = $this->createMock(Repository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $repository = new CycleCategoryRepository($ormRepository, $em);

        expect($repository)->toBeInstanceOf(CategoryRepository::class);
    });

    describe('findById', function (): void {
        it('returns null when category not found', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findById(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toBeNull();
        });

        it('returns Category when found', function (): void {
            $entity = new CategoryEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440000';
            $entity->name = 'Acciones';
            $entity->icon = 'running';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findByPK')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findById(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'));

            expect($result)->toBeInstanceOf(Category::class);
            expect($result->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
            expect($result->name())->toBe('Acciones');
            expect($result->icon())->toBe('running');
        });
    });

    describe('findByName', function (): void {
        it('returns null when category not found', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn(null);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findByName('NonExistent');

            expect($result)->toBeNull();
        });

        it('returns Category when found', function (): void {
            $entity = new CategoryEntity();
            $entity->id = '550e8400-e29b-41d4-a716-446655440000';
            $entity->name = 'Emociones';
            $entity->icon = 'smile';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findOne')->willReturn($entity);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findByName('Emociones');

            expect($result)->toBeInstanceOf(Category::class);
            expect($result->name())->toBe('Emociones');
        });
    });

    describe('findAll', function (): void {
        it('returns empty array when no categories exist', function (): void {
            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findAll();

            expect($result)->toBe([]);
        });

        it('returns array of Categories when they exist', function (): void {
            $entity1 = new CategoryEntity();
            $entity1->id = '550e8400-e29b-41d4-a716-446655440001';
            $entity1->name = 'Acciones';
            $entity1->icon = 'running';

            $entity2 = new CategoryEntity();
            $entity2->id = '550e8400-e29b-41d4-a716-446655440002';
            $entity2->name = 'Emociones';
            $entity2->icon = 'smile';

            $ormRepository = $this->createMock(Repository::class);
            $ormRepository->method('findAll')->willReturn([$entity1, $entity2]);

            $em = $this->createMock(EntityManagerInterface::class);

            $repository = new CycleCategoryRepository($ormRepository, $em);
            $result = $repository->findAll();

            expect($result)->toHaveCount(2);
            expect($result[0])->toBeInstanceOf(Category::class);
            expect($result[1])->toBeInstanceOf(Category::class);
            expect($result[0]->name())->toBe('Acciones');
            expect($result[1]->name())->toBe('Emociones');
        });
    });

    describe('save', function (): void {
        it('persists category through entity manager', function (): void {
            $ormRepository = $this->createMock(Repository::class);

            $em = $this->createMock(EntityManagerInterface::class);
            $em->expects($this->once())->method('persist');
            $em->expects($this->once())->method('run');

            $repository = new CycleCategoryRepository($ormRepository, $em);

            $category = new Category(
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'Acciones',
                'running'
            );

            $repository->save($category);
        });
    });
});
