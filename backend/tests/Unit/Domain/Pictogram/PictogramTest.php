<?php

declare(strict_types=1);

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Exception\InvalidImagePathException;
use App\Domain\Pictogram\Exception\InvalidPictogramLabelException;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;

describe('Pictogram Entity', function (): void {
    it('can be created with valid data', function (): void {
        $id = PictogramId::generate();
        $arasaacId = new ArasaacId(4887);
        $categoryId = CategoryId::generate();
        $label = 'comer';
        $imagePath = '/pictograms/4887.png';

        $pictogram = new Pictogram(
            id: $id,
            arasaacId: $arasaacId,
            categoryId: $categoryId,
            label: $label,
            imagePath: $imagePath
        );

        expect($pictogram->id())->toBe($id);
        expect($pictogram->arasaacId())->toBe($arasaacId);
        expect($pictogram->categoryId())->toBe($categoryId);
        expect($pictogram->label())->toBe('comer');
        expect($pictogram->imagePath())->toBe('/pictograms/4887.png');
    });

    it('requires a non-empty label', function (): void {
        $id = PictogramId::generate();
        $arasaacId = new ArasaacId(4887);
        $categoryId = CategoryId::generate();

        new Pictogram(
            id: $id,
            arasaacId: $arasaacId,
            categoryId: $categoryId,
            label: '',
            imagePath: '/pictograms/4887.png'
        );
    })->throws(InvalidPictogramLabelException::class, 'Pictogram label cannot be empty');

    it('requires label under 100 characters', function (): void {
        $id = PictogramId::generate();
        $arasaacId = new ArasaacId(4887);
        $categoryId = CategoryId::generate();

        new Pictogram(
            id: $id,
            arasaacId: $arasaacId,
            categoryId: $categoryId,
            label: str_repeat('a', 101),
            imagePath: '/pictograms/4887.png'
        );
    })->throws(InvalidPictogramLabelException::class, 'Pictogram label is too long: 101 characters (max: 100)');

    it('requires a non-empty image path', function (): void {
        $id = PictogramId::generate();
        $arasaacId = new ArasaacId(4887);
        $categoryId = CategoryId::generate();

        new Pictogram(
            id: $id,
            arasaacId: $arasaacId,
            categoryId: $categoryId,
            label: 'comer',
            imagePath: ''
        );
    })->throws(InvalidImagePathException::class, 'Image path cannot be empty');

    it('rejects path traversal in image path', function (): void {
        $id = PictogramId::generate();
        $arasaacId = new ArasaacId(4887);
        $categoryId = CategoryId::generate();

        new Pictogram(
            id: $id,
            arasaacId: $arasaacId,
            categoryId: $categoryId,
            label: 'comer',
            imagePath: '/pictograms/../etc/passwd'
        );
    })->throws(InvalidImagePathException::class, 'Path traversal detected in image path: /pictograms/../etc/passwd');
});

describe('PictogramId Value Object', function (): void {
    it('can be generated', function (): void {
        $id = PictogramId::generate();

        expect($id)->toBeInstanceOf(PictogramId::class);
        expect($id->value())->toBeString();
        expect(strlen($id->value()))->toBe(36); // UUID format
    });

    it('can be created from string', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = PictogramId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    it('rejects invalid UUID format', function (): void {
        PictogramId::fromString('invalid-uuid');
    })->throws(InvalidArgumentException::class);

    it('can be compared for equality', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = PictogramId::fromString($uuid);
        $id2 = PictogramId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });
});

describe('ArasaacId Value Object', function (): void {
    it('can be created with valid positive integer', function (): void {
        $arasaacId = new ArasaacId(4887);

        expect($arasaacId->value())->toBe(4887);
    });

    it('rejects zero', function (): void {
        new ArasaacId(0);
    })->throws(InvalidArgumentException::class, 'ARASAAC ID must be positive');

    it('rejects negative numbers', function (): void {
        new ArasaacId(-1);
    })->throws(InvalidArgumentException::class, 'ARASAAC ID must be positive');

    it('can be compared for equality', function (): void {
        $id1 = new ArasaacId(4887);
        $id2 = new ArasaacId(4887);

        expect($id1->equals($id2))->toBeTrue();
    });
});
