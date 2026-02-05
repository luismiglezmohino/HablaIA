<?php

declare(strict_types=1);

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Exception\InvalidCategoryColorException;
use App\Domain\Category\Exception\InvalidCategoryDisplayOrderException;
use App\Domain\Category\Exception\InvalidCategoryNameException;
use App\Domain\Category\ValueObject\CategoryId;
use Tests\Shared\FakeUuidGenerator;

beforeEach(function (): void {
    $this->uuidGenerator = new FakeUuidGenerator();
});

describe('Category Entity', function (): void {
    it('can be created with valid data', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());
        $name = 'Acciones';
        $icon = '🏃';

        $category = new Category(
            id: $id,
            name: $name,
            icon: $icon,
            colorHex: '#10B981',
            displayOrder: 2
        );

        expect($category->id())->toBe($id);
        expect($category->name())->toBe('Acciones');
        expect($category->icon())->toBe('🏃');
        expect($category->colorHex())->toBe('#10B981');
        expect($category->displayOrder())->toBe(2);
    });

    it('can be created without icon', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        $category = new Category(
            id: $id,
            name: 'Emociones',
            icon: null,
            colorHex: '#F59E0B',
            displayOrder: 3
        );

        expect($category->icon())->toBeNull();
    });

    it('uses default colorHex and displayOrder', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        $category = new Category(
            id: $id,
            name: 'Personas',
            icon: 'users'
        );

        expect($category->colorHex())->toBe('#6B7280');
        expect($category->displayOrder())->toBe(0);
    });

    it('requires a non-empty name', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: '',
            icon: null
        );
    })->throws(InvalidCategoryNameException::class, 'Category name cannot be empty');

    it('requires name under 50 characters', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: str_repeat('a', 51),
            icon: null
        );
    })->throws(InvalidCategoryNameException::class, 'Category name is too long: 51 characters (max: 50)');

    it('requires a valid hex color format', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        $category = new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '#3B82F6',
            displayOrder: 1
        );

        expect($category->colorHex())->toBe('#3B82F6');
    });

    it('rejects empty color', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: ''
        );
    })->throws(InvalidCategoryColorException::class, 'Category color cannot be empty');

    it('rejects invalid color format without hash', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '3B82F6'
        );
    })->throws(InvalidCategoryColorException::class, 'Invalid hex color format');

    it('rejects color with wrong length', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '#FFF'
        );
    })->throws(InvalidCategoryColorException::class, 'Invalid hex color format');

    it('rejects color with invalid characters', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '#GGGGGG'
        );
    })->throws(InvalidCategoryColorException::class, 'Invalid hex color format');

    it('rejects negative display order', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '#3B82F6',
            displayOrder: -1
        );
    })->throws(InvalidCategoryDisplayOrderException::class, 'Display order must be non-negative, got: -1');

    it('accepts zero display order', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

        $category = new Category(
            id: $id,
            name: 'Acciones',
            icon: null,
            colorHex: '#3B82F6',
            displayOrder: 0
        );

        expect($category->displayOrder())->toBe(0);
    });
});

describe('CategoryId Value Object', function (): void {
    it('can be created from generator', function (): void {
        $id = CategoryId::fromString($this->uuidGenerator->generate());

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
