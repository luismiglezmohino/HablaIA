<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DataFixtures;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\DataFixtures\CategoryFixtures;

describe('CategoryFixtures', function (): void {
    describe('getCategories', function (): void {
        it('returns array with 10 SAAC standard categories', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories)->toBeArray();
            expect($categories)->toHaveCount(10);
        });

        it('contains Social category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[7])->toBe([
                'name' => 'Social', 'icon' => 'message-circle', 'colorHex' => '#EC4899', 'displayOrder' => 8,
            ]);
        });

        it('contains Tiempo category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[8])->toBe([
                'name' => 'Tiempo', 'icon' => 'clock', 'colorHex' => '#8B5CF6', 'displayOrder' => 9,
            ]);
        });

        it('contains Descriptivos category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[9])->toBe([
                'name' => 'Descriptivos', 'icon' => 'sliders', 'colorHex' => '#14B8A6', 'displayOrder' => 10,
            ]);
        });

        it('contains Personas category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[0])->toBe([
                'name' => 'Personas', 'icon' => 'users', 'colorHex' => '#FBBF24', 'displayOrder' => 1,
            ]);
        });

        it('contains Acciones category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[1])->toBe([
                'name' => 'Acciones', 'icon' => 'play', 'colorHex' => '#22C55E', 'displayOrder' => 2,
            ]);
        });

        it('contains Emociones category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[2])->toBe([
                'name' => 'Emociones', 'icon' => 'heart', 'colorHex' => '#3B82F6', 'displayOrder' => 3,
            ]);
        });

        it('contains Lugares category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[3])->toBe([
                'name' => 'Lugares', 'icon' => 'map-pin', 'colorHex' => '#F97316', 'displayOrder' => 4,
            ]);
        });

        it('contains Objetos category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[4])->toBe([
                'name' => 'Objetos', 'icon' => 'box', 'colorHex' => '#FB923C', 'displayOrder' => 5,
            ]);
        });

        it('contains Comida category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[5])->toBe([
                'name' => 'Comida', 'icon' => 'utensils', 'colorHex' => '#EA580C', 'displayOrder' => 6,
            ]);
        });

        it('contains Transporte category with Fitzgerald color', function (): void {
            $categories = CategoryFixtures::getCategories();

            expect($categories[6])->toBe([
                'name' => 'Transporte', 'icon' => 'car', 'colorHex' => '#F59E0B', 'displayOrder' => 7,
            ]);
        });

        it('has categories ordered by displayOrder', function (): void {
            $categories = CategoryFixtures::getCategories();

            for ($i = 0; $i < count($categories) - 1; $i++) {
                expect($categories[$i]['displayOrder'])->toBeLessThan($categories[$i + 1]['displayOrder']);
            }
        });
    });

    describe('load', function (): void {
        it('loads all 10 categories when repository is empty', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->method('generate')
                ->willReturnOnConsecutiveCalls(
                    '550e8400-e29b-41d4-a716-446655440001',
                    '550e8400-e29b-41d4-a716-446655440002',
                    '550e8400-e29b-41d4-a716-446655440003',
                    '550e8400-e29b-41d4-a716-446655440004',
                    '550e8400-e29b-41d4-a716-446655440005',
                    '550e8400-e29b-41d4-a716-446655440006',
                    '550e8400-e29b-41d4-a716-446655440007',
                    '550e8400-e29b-41d4-a716-446655440008',
                    '550e8400-e29b-41d4-a716-446655440009',
                    '550e8400-e29b-41d4-a716-446655440010'
                );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findByName')->willReturn(null);
            $repository->expects($this->exactly(10))->method('save');

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $count = $fixtures->load();

            expect($count)->toBe(10);
        });

        it('skips existing categories', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->method('generate')
                ->willReturnOnConsecutiveCalls(
                    '550e8400-e29b-41d4-a716-446655440001',
                    '550e8400-e29b-41d4-a716-446655440002',
                    '550e8400-e29b-41d4-a716-446655440003',
                    '550e8400-e29b-41d4-a716-446655440004',
                    '550e8400-e29b-41d4-a716-446655440005',
                    '550e8400-e29b-41d4-a716-446655440006',
                    '550e8400-e29b-41d4-a716-446655440007',
                    '550e8400-e29b-41d4-a716-446655440008'
                );

            $existingCategory = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'Personas',
                'users',
                '#FBBF24',
                1
            );

            $existingCategory2 = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440099'),
                'Acciones',
                'play',
                '#22C55E',
                2
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
            $repository->expects($this->exactly(8))->method('save');

            $fixtures = new CategoryFixtures($uuidGenerator, $repository);
            $count = $fixtures->load();

            expect($count)->toBe(8);
        });

        it('returns 0 when all categories already exist', function (): void {
            $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
            $uuidGenerator->expects($this->never())->method('generate');

            $existingCategory = new Category(
                \App\Domain\Category\ValueObject\CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                'SomeCategory',
                'icon',
                '#3B82F6',
                1
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

            expect(count(array_unique($savedCategories)))->toBe(10);
        });
    });
});
