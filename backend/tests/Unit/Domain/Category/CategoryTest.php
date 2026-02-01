<?php

declare(strict_types=1);

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Exception\InvalidCategoryNameException;
use App\Domain\Category\ValueObject\CategoryId;

describe('Category Entity', function (): void {
    it('can be created with valid data', function (): void {
        $id = CategoryId::generate();
        $name = 'Acciones';
        $icon = '🏃';

        $category = new Category(
            id: $id,
            name: $name,
            icon: $icon
        );

        expect($category->id())->toBe($id);
        expect($category->name())->toBe('Acciones');
        expect($category->icon())->toBe('🏃');
    });

    it('can be created without icon', function (): void {
        $id = CategoryId::generate();

        $category = new Category(
            id: $id,
            name: 'Emociones',
            icon: null
        );

        expect($category->icon())->toBeNull();
    });

    it('requires a non-empty name', function (): void {
        $id = CategoryId::generate();

        new Category(
            id: $id,
            name: '',
            icon: null
        );
    })->throws(InvalidCategoryNameException::class, 'Category name cannot be empty');

    it('requires name under 50 characters', function (): void {
        $id = CategoryId::generate();

        new Category(
            id: $id,
            name: str_repeat('a', 51),
            icon: null
        );
    })->throws(InvalidCategoryNameException::class, 'Category name is too long: 51 characters (max: 50)');
});

describe('CategoryId Value Object', function (): void {
    it('can be generated', function (): void {
        $id = CategoryId::generate();

        expect($id)->toBeInstanceOf(CategoryId::class);
        expect($id->value())->toBeString();
        expect(strlen($id->value()))->toBe(36);
    });

    it('can be created from string', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = CategoryId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    it('rejects invalid UUID format', function (): void {
        CategoryId::fromString('invalid-uuid');
    })->throws(InvalidArgumentException::class);

    it('can be compared for equality', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = CategoryId::fromString($uuid);
        $id2 = CategoryId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });
});
