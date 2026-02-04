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

        $domain = CategoryMapper::toDomain($entity);

        expect($domain)->toBeInstanceOf(Category::class);
        expect($domain->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($domain->name())->toBe('Acciones');
        expect($domain->icon())->toBe('running');
    });

    it('converts Category domain entity to CategoryEntity', function (): void {
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $domain = new Category($categoryId, 'Acciones', 'running');

        $entity = CategoryMapper::toEntity($domain);

        expect($entity)->toBeInstanceOf(CategoryEntity::class);
        expect($entity->id)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($entity->name)->toBe('Acciones');
        expect($entity->icon)->toBe('running');
    });

    it('preserves data through domain to entity to domain conversion', function (): void {
        $originalId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $original = new Category($originalId, 'Acciones', 'running');

        $entity = CategoryMapper::toEntity($original);
        $restored = CategoryMapper::toDomain($entity);

        expect($restored->id()->value())->toBe($original->id()->value());
        expect($restored->name())->toBe($original->name());
        expect($restored->icon())->toBe($original->icon());
    });

    it('handles null icon correctly in both directions', function (): void {
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $domain = new Category($categoryId, 'Otros', null);

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

        $domain = CategoryMapper::toDomain($original);
        $restored = CategoryMapper::toEntity($domain);

        expect($restored->id)->toBe($original->id);
        expect($restored->name)->toBe($original->name);
        expect($restored->icon)->toBe($original->icon);
    });
});
