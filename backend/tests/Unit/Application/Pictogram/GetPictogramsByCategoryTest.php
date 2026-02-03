<?php

declare(strict_types=1);

use App\Application\DTO\PictogramDTO;
use App\Application\Exception\CategoryNotFoundException;
use App\Application\Pictogram\GetPictogramsByCategory;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryCategoryRepository;
use Tests\Shared\InMemoryPictogramRepository;

beforeEach(function (): void {
    $this->categoryRepository = new InMemoryCategoryRepository();
    $this->pictogramRepository = new InMemoryPictogramRepository();
    $this->useCase = new GetPictogramsByCategory(
        $this->pictogramRepository,
        $this->categoryRepository
    );
    $this->uuidGenerator = new FakeUuidGenerator();
});

describe('GetPictogramsByCategory', function (): void {

    it('throws exception when category does not exist', function (): void {
        $nonExistentId = $this->uuidGenerator->generate();

        ($this->useCase)($nonExistentId);
    })->throws(CategoryNotFoundException::class);

    it('returns empty array when category has no pictograms', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $category = new Category(
            CategoryId::fromString($categoryId),
            'Acciones',
            null
        );
        $this->categoryRepository->save($category);

        // Act
        $result = ($this->useCase)($categoryId);

        // Assert
        expect($result)->toBe([]);
    });

    it('returns single pictogram for category', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $category = new Category(
            CategoryId::fromString($categoryId),
            'Acciones',
            null
        );
        $this->categoryRepository->save($category);

        $pictogram = new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            new ArasaacId(12345),
            CategoryId::fromString($categoryId),
            'comer',
            '/pictograms/comer.png'
        );
        $this->pictogramRepository->save($pictogram);

        // Act
        $result = ($this->useCase)($categoryId);

        // Assert
        expect($result)->toHaveCount(1);
        expect($result[0])->toBeInstanceOf(PictogramDTO::class);
        expect($result[0]->label)->toBe('comer');
    });

    it('returns only pictograms from requested category', function (): void {
        // Arrange
        $categoryId1 = $this->uuidGenerator->generate();
        $categoryId2 = $this->uuidGenerator->generate();

        $category1 = new Category(
            CategoryId::fromString($categoryId1),
            'Acciones',
            null
        );
        $category2 = new Category(
            CategoryId::fromString($categoryId2),
            'Emociones',
            null
        );
        $this->categoryRepository->save($category1);
        $this->categoryRepository->save($category2);

        // Pictogramas en categoría 1
        $pictogram1 = new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            new ArasaacId(111),
            CategoryId::fromString($categoryId1),
            'correr',
            '/pictograms/correr.png'
        );
        $pictogram2 = new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            new ArasaacId(222),
            CategoryId::fromString($categoryId1),
            'saltar',
            '/pictograms/saltar.png'
        );

        // Pictograma en categoría 2
        $pictogram3 = new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            new ArasaacId(333),
            CategoryId::fromString($categoryId2),
            'feliz',
            '/pictograms/feliz.png'
        );

        $this->pictogramRepository->save($pictogram1);
        $this->pictogramRepository->save($pictogram2);
        $this->pictogramRepository->save($pictogram3);

        // Act
        $result = ($this->useCase)($categoryId1);

        // Assert
        expect($result)->toHaveCount(2);
        expect($result[0]->label)->toBe('correr');
        expect($result[1]->label)->toBe('saltar');
    });

    it('maps all DTO fields correctly', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $pictogramId = $this->uuidGenerator->generate();
        $arasaacId = 99999;
        $label = 'pictograma-test';
        $imagePath = '/images/test/pictograma.png';

        $category = new Category(
            CategoryId::fromString($categoryId),
            'Test',
            null
        );
        $this->categoryRepository->save($category);

        $pictogram = new Pictogram(
            PictogramId::fromString($pictogramId),
            new ArasaacId($arasaacId),
            CategoryId::fromString($categoryId),
            $label,
            $imagePath
        );
        $this->pictogramRepository->save($pictogram);

        // Act
        $result = ($this->useCase)($categoryId);

        // Assert
        expect($result[0]->id)->toBe($pictogramId);
        expect($result[0]->arasaacId)->toBe($arasaacId);
        expect($result[0]->categoryId)->toBe($categoryId);
        expect($result[0]->label)->toBe($label);
        expect($result[0]->imagePath)->toBe($imagePath);
    });

    it('maintains insertion order within category', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $category = new Category(
            CategoryId::fromString($categoryId),
            'Acciones',
            null
        );
        $this->categoryRepository->save($category);

        $labels = ['primero', 'segundo', 'tercero', 'cuarto'];
        foreach ($labels as $index => $label) {
            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId($index + 1),
                CategoryId::fromString($categoryId),
                $label,
                "/pictograms/{$label}.png"
            );
            $this->pictogramRepository->save($pictogram);
        }

        // Act
        $result = ($this->useCase)($categoryId);

        // Assert
        expect($result)->toHaveCount(4);
        expect($result[0]->label)->toBe('primero');
        expect($result[1]->label)->toBe('segundo');
        expect($result[2]->label)->toBe('tercero');
        expect($result[3]->label)->toBe('cuarto');
    });

    it('handles multiple pictograms in category', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $category = new Category(
            CategoryId::fromString($categoryId),
            'Test',
            null
        );
        $this->categoryRepository->save($category);

        $totalPictograms = 10;
        for ($i = 1; $i <= $totalPictograms; $i++) {
            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId($i * 100),
                CategoryId::fromString($categoryId),
                "pictograma-{$i}",
                "/pictograms/picto-{$i}.png"
            );
            $this->pictogramRepository->save($pictogram);
        }

        // Act
        $result = ($this->useCase)($categoryId);

        // Assert
        expect($result)->toHaveCount($totalPictograms);
        foreach ($result as $index => $dto) {
            expect($dto)->toBeInstanceOf(PictogramDTO::class);
            expect($dto->label)->toBe('pictograma-' . ($index + 1));
        }
    });

    it('throws exception with invalid UUID format', function (): void {
        ($this->useCase)('invalid-uuid');
    })->throws(InvalidArgumentException::class);

});
