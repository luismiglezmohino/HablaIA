<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Infrastructure\Persistence\Cycle\Entity\CategoryEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\CategoryMapper;

describe('CategoryMapper', function (): void {
    it('converts CategoryEntity to Category domain entity', function (): void {
        $entity = new CategoryEntity();
        $entity->id = '550e8400-e29b-41d4-a716-446655440000';
        $entity->name = 'Acciones';
        $entity->icon = 'running';
        $entity->colorHex = '#22C55E';
        $entity->displayOrder = 2;

        $domain = CategoryMapper::toDomain($entity);

        expect($domain)->toBeInstanceOf(Category::class);
        expect($domain->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($domain->name())->toBe('Acciones');
        expect($domain->icon())->toBe('running');
        expect($domain->colorHex())->toBe('#22C55E');
        expect($domain->displayOrder())->toBe(2);
    });

    it('converts Category domain entity to CategoryEntity', function (): void {
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $domain = new Category($categoryId, 'Acciones', 'running', '#22C55E', 2);

        $entity = CategoryMapper::toEntity($domain);

        expect($entity)->toBeInstanceOf(CategoryEntity::class);
        expect($entity->id)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($entity->name)->toBe('Acciones');
        expect($entity->icon)->toBe('running');
        expect($entity->colorHex)->toBe('#22C55E');
        expect($entity->displayOrder)->toBe(2);
    });

    it('preserves data through domain to entity to domain conversion', function (): void {
        $originalId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $original = new Category($originalId, 'Acciones', 'running', '#22C55E', 2);

        $entity = CategoryMapper::toEntity($original);
        $restored = CategoryMapper::toDomain($entity);

        expect($restored->id()->value())->toBe($original->id()->value());
        expect($restored->name())->toBe($original->name());
        expect($restored->icon())->toBe($original->icon());
        expect($restored->colorHex())->toBe($original->colorHex());
        expect($restored->displayOrder())->toBe($original->displayOrder());
    });

    it('handles null icon correctly in both directions', function (): void {
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $domain = new Category($categoryId, 'Otros', null, '#F97316', 4);

        $entity = CategoryMapper::toEntity($domain);
        expect($entity->icon)->toBeNull();

        $restored = CategoryMapper::toDomain($entity);
        expect($restored->icon())->toBeNull();
    });

    it('preserves data through entity to domain to entity conversion', function (): void {
        $original = new CategoryEntity();
        $original->id = '550e8400-e29b-41d4-a716-446655440000';
        $original->name = 'Emociones';
        $original->icon = 'smile';
        $original->colorHex = '#3B82F6';
        $original->displayOrder = 3;

        $domain = CategoryMapper::toDomain($original);
        $restored = CategoryMapper::toEntity($domain);

        expect($restored->id)->toBe($original->id);
        expect($restored->name)->toBe($original->name);
        expect($restored->icon)->toBe($original->icon);
        expect($restored->colorHex)->toBe($original->colorHex);
        expect($restored->displayOrder)->toBe($original->displayOrder);
    });
});
