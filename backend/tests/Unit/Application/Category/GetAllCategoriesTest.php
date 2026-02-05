<?php

declare(strict_types=1);

use App\Application\Category\GetAllCategories;
use App\Application\DTO\CategoryDTO;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryCategoryRepository;

beforeEach(function (): void {
    $this->repository = new InMemoryCategoryRepository();
    $this->useCase = new GetAllCategories($this->repository);
    $this->uuidGenerator = new FakeUuidGenerator();
});

describe('GetAllCategories', function (): void {

    it('returns empty array when no categories exist', function (): void {
        $result = ($this->useCase)();

        expect($result)->toBe([]);
    });

    it('returns all categories as DTOs with correct data', function (): void {
        // Arrange
        $id1 = $this->uuidGenerator->generate();
        $id2 = $this->uuidGenerator->generate();

        $category1 = new Category(
            CategoryId::fromString($id1),
            'Acciones',
            'actions-icon',
            '#22C55E',
            2
        );
        $category2 = new Category(
            CategoryId::fromString($id2),
            'Emociones',
            null,
            '#3B82F6',
            3
        );
        $this->repository->save($category1);
        $this->repository->save($category2);

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount(2);
        expect($result[0])->toBeInstanceOf(CategoryDTO::class);
        expect($result[0]->id)->toBe($id1);
        expect($result[0]->name)->toBe('Acciones');
        expect($result[0]->icon)->toBe('actions-icon');
        expect($result[0]->colorHex)->toBe('#22C55E');
        expect($result[0]->displayOrder)->toBe(2);
        expect($result[1]->id)->toBe($id2);
        expect($result[1]->name)->toBe('Emociones');
        expect($result[1]->icon)->toBeNull();
        expect($result[1]->colorHex)->toBe('#3B82F6');
        expect($result[1]->displayOrder)->toBe(3);
    });

});
