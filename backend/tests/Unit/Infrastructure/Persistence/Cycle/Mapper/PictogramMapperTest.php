<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\PictogramMapper;

describe('PictogramMapper', function (): void {
    it('converts PictogramEntity to Pictogram domain entity', function (): void {
        $entity = new PictogramEntity();
        $entity->id = '550e8400-e29b-41d4-a716-446655440001';
        $entity->arasaacId = 12345;
        $entity->categoryId = '550e8400-e29b-41d4-a716-446655440000';
        $entity->label = 'comer';
        $entity->imagePath = '/images/pictograms/12345.png';

        $domain = PictogramMapper::toDomain($entity);

        expect($domain)->toBeInstanceOf(Pictogram::class);
        expect($domain->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440001');
        expect($domain->arasaacId()->value())->toBe(12345);
        expect($domain->categoryId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($domain->label())->toBe('comer');
        expect($domain->imagePath())->toBe('/images/pictograms/12345.png');
    });

    it('converts Pictogram domain entity to PictogramEntity', function (): void {
        $pictogramId = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $arasaacId = new ArasaacId(12345);
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');

        $domain = new Pictogram($pictogramId, $arasaacId, $categoryId, 'comer', '/images/12345.png');

        $entity = PictogramMapper::toEntity($domain);

        expect($entity)->toBeInstanceOf(PictogramEntity::class);
        expect($entity->id)->toBe('550e8400-e29b-41d4-a716-446655440001');
        expect($entity->arasaacId)->toBe(12345);
        expect($entity->categoryId)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($entity->label)->toBe('comer');
        expect($entity->imagePath)->toBe('/images/12345.png');
    });

    it('preserves data through domain to entity to domain conversion', function (): void {
        $originalId = PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $arasaacId = new ArasaacId(12345);
        $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440000');

        $original = new Pictogram($originalId, $arasaacId, $categoryId, 'comer', '/images/12345.png');

        $entity = PictogramMapper::toEntity($original);
        $restored = PictogramMapper::toDomain($entity);

        expect($restored->id()->value())->toBe($original->id()->value());
        expect($restored->arasaacId()->value())->toBe($original->arasaacId()->value());
        expect($restored->categoryId()->value())->toBe($original->categoryId()->value());
        expect($restored->label())->toBe($original->label());
        expect($restored->imagePath())->toBe($original->imagePath());
    });

    it('preserves data through entity to domain to entity conversion', function (): void {
        $original = new PictogramEntity();
        $original->id = '550e8400-e29b-41d4-a716-446655440001';
        $original->arasaacId = 12345;
        $original->categoryId = '550e8400-e29b-41d4-a716-446655440000';
        $original->label = 'comer';
        $original->imagePath = '/images/pictograms/12345.png';

        $domain = PictogramMapper::toDomain($original);
        $restored = PictogramMapper::toEntity($domain);

        expect($restored->id)->toBe($original->id);
        expect($restored->arasaacId)->toBe($original->arasaacId);
        expect($restored->categoryId)->toBe($original->categoryId);
        expect($restored->label)->toBe($original->label);
        expect($restored->imagePath)->toBe($original->imagePath);
    });
});
