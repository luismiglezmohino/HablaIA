<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DataFixtures;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\DataFixtures\CategoryFixtures;

describe('CategoryFixtures', function (): void {
    describe('getCategories', function (): void {
        it('returns array with 7 SAAC standard categories', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toBeArray();
            expect($categories)->toHaveCount(7);
        });

        it('contains Personas category with users icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Personas', 'icon' => 'users']);
        });

        it('contains Acciones category with play icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Acciones', 'icon' => 'play']);
        });

        it('contains Emociones category with heart icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Emociones', 'icon' => 'heart']);
        });

        it('contains Lugares category with map-pin icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Lugares', 'icon' => 'map-pin']);
        });

        it('contains Objetos category with box icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Objetos', 'icon' => 'box']);
        });

        it('contains Comida category with utensils icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Comida', 'icon' => 'utensils']);
        });

        it('contains Transporte category with car icon', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toContain(['name' => 'Transporte', 'icon' => 'car']);
        });
    });

    describe('load', function (): void {
        it('loads all 7 categories when repository is empty', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->method('generate')
                ->willReturnOnConsecutiveCalls(
                    '550e8400-e29b-41d4-a716-446655440001',
                    '550e8400-e29b-41d4-a716-446655440002',
                    '550e8400-e29b-41d4-a716-446655440003',
                    '550e8400-e29b-41d4-a716-446655440004',
                    '550e8400-e29b-41d4-a716-446655440005',
                    '550e8400-e29b-41d4-a716-446655440006',
                    '550e8400-e29b-41d4-a716-446655440007'
                );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findByName')->willReturn(null);
            $repository->expects($this->exactly(7))->method('save');

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $count = $fixtures->load();

            expect($count)->toBe(7);
        });

        it('skips existing categories', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->method('generate')
                ->willReturnOnConsecutiveCalls(
                    '550e8400-e29b-41d4-a716-446655440001',
                    '550e8400-e29b-41d4-a716-446655440002',
                    '550e8400-e29b-41d4-a716-446655440003',
                    '550e8400-e29b-41d4-a716-446655440004',
                    '550e8400-e29b-41d4-a716-446655440005'
                );

            $existingCategory = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'Personas',
                'users'
            );

            $existingCategory2 = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440099'),
                'Acciones',
                'play'
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findByName')
                ->willReturnCallback(function (string $name) use ($existingCategory, $existingCategory2): ?Category {
                    return match ($name) {
                        'Personas' => $existingCategory,
                        'Acciones' => $existingCategory2,
                        default => null,
                    };
                });
            $repository->expects($this->exactly(5))->method('save');

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $count = $fixtures->load();

            expect($count)->toBe(5);
        });

        it('returns 0 when all categories already exist', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->expects($this->never())->method('generate');

            $existingCategory = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'SomeCategory',
                'icon'
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findByName')->willReturn($existingCategory);
            $repository->expects($this->never())->method('save');

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $count = $fixtures->load();

            expect($count)->toBe(0);
        });

        it('generates unique UUIDs for each category', function (): void {
            $generatedUuids = [];

            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->method('generate')
                ->willReturnCallback(function () use (&$generatedUuids): string {
                    $uuid = sprintf('550e8400-e29b-41d4-a716-4466554400%02d', count($generatedUuids) + 1);
                    $generatedUuids[] = $uuid;
                    return $uuid;
                });

            $savedCategories = [];
            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findByName')->willReturn(null);
            $repository->method('save')
                ->willReturnCallback(function (Category $category) use (&$savedCategories): void {
                    $savedCategories[] = $category->id()->value();
                });

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $fixtures->load();

            expect(count(array_unique($savedCategories)))->toBe(7);
        });
    });
});
