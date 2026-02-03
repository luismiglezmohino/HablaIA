<?php

declare(strict_types=1);

use App\Application\DTO\PictogramDTO;
use App\Application\Pictogram\GetAllPictograms;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryPictogramRepository;

beforeEach(function (): void {
    $this->repository = new InMemoryPictogramRepository();
    $this->useCase = new GetAllPictograms($this->repository);
    $this->uuidGenerator = new FakeUuidGenerator();
});

describe('GetAllPictograms', function (): void {

    it('returns empty array when no pictograms exist', function (): void {
        $result = ($this->useCase)();

        expect($result)->toBe([]);
    });

    it('returns single pictogram as DTO', function (): void {
        // Arrange
        $id = $this->uuidGenerator->generate();
        $categoryId = $this->uuidGenerator->generate();

        $pictogram = new Pictogram(
            PictogramId::fromString($id),
            new ArasaacId(12345),
            CategoryId::fromString($categoryId),
            'comer',
            '/pictograms/comer.png'
        );
        $this->repository->save($pictogram);

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount(1);
        expect($result[0])->toBeInstanceOf(PictogramDTO::class);
    });

    it('returns all pictograms as DTOs with correct data', function (): void {
        // Arrange
        $id1 = $this->uuidGenerator->generate();
        $id2 = $this->uuidGenerator->generate();
        $categoryId = $this->uuidGenerator->generate();

        $pictogram1 = new Pictogram(
            PictogramId::fromString($id1),
            new ArasaacId(12345),
            CategoryId::fromString($categoryId),
            'comer',
            '/pictograms/comer.png'
        );
        $pictogram2 = new Pictogram(
            PictogramId::fromString($id2),
            new ArasaacId(67890),
            CategoryId::fromString($categoryId),
            'beber',
            '/pictograms/beber.png'
        );
        $this->repository->save($pictogram1);
        $this->repository->save($pictogram2);

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount(2);
        expect($result[0])->toBeInstanceOf(PictogramDTO::class);
        expect($result[0]->id)->toBe($id1);
        expect($result[0]->arasaacId)->toBe(12345);
        expect($result[0]->categoryId)->toBe($categoryId);
        expect($result[0]->label)->toBe('comer');
        expect($result[0]->imagePath)->toBe('/pictograms/comer.png');
        expect($result[1]->id)->toBe($id2);
        expect($result[1]->label)->toBe('beber');
    });

    it('returns pictograms from different categories', function (): void {
        // Arrange
        $categoryId1 = $this->uuidGenerator->generate();
        $categoryId2 = $this->uuidGenerator->generate();

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
            CategoryId::fromString($categoryId2),
            'feliz',
            '/pictograms/feliz.png'
        );
        $this->repository->save($pictogram1);
        $this->repository->save($pictogram2);

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount(2);
        expect($result[0]->categoryId)->toBe($categoryId1);
        expect($result[1]->categoryId)->toBe($categoryId2);
    });

    it('maps all DTO fields correctly', function (): void {
        // Arrange
        $id = $this->uuidGenerator->generate();
        $categoryId = $this->uuidGenerator->generate();
        $arasaacId = 99999;
        $label = 'pictograma-test';
        $imagePath = '/images/test/pictograma.png';

        $pictogram = new Pictogram(
            PictogramId::fromString($id),
            new ArasaacId($arasaacId),
            CategoryId::fromString($categoryId),
            $label,
            $imagePath
        );
        $this->repository->save($pictogram);

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result[0]->id)->toBe($id);
        expect($result[0]->arasaacId)->toBe($arasaacId);
        expect($result[0]->categoryId)->toBe($categoryId);
        expect($result[0]->label)->toBe($label);
        expect($result[0]->imagePath)->toBe($imagePath);
    });

    it('maintains insertion order', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $labels = ['primero', 'segundo', 'tercero', 'cuarto'];
        $ids = [];

        foreach ($labels as $index => $label) {
            $id = $this->uuidGenerator->generate();
            $ids[] = $id;

            $pictogram = new Pictogram(
                PictogramId::fromString($id),
                new ArasaacId($index + 1),
                CategoryId::fromString($categoryId),
                $label,
                "/pictograms/{$label}.png"
            );
            $this->repository->save($pictogram);
        }

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount(4);
        expect($result[0]->label)->toBe('primero');
        expect($result[1]->label)->toBe('segundo');
        expect($result[2]->label)->toBe('tercero');
        expect($result[3]->label)->toBe('cuarto');
        expect($result[0]->id)->toBe($ids[0]);
        expect($result[3]->id)->toBe($ids[3]);
    });

    it('handles multiple pictograms correctly', function (): void {
        // Arrange
        $categoryId = $this->uuidGenerator->generate();
        $totalPictograms = 10;

        for ($i = 1; $i <= $totalPictograms; $i++) {
            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId($i * 100),
                CategoryId::fromString($categoryId),
                "pictograma-{$i}",
                "/pictograms/picto-{$i}.png"
            );
            $this->repository->save($pictogram);
        }

        // Act
        $result = ($this->useCase)();

        // Assert
        expect($result)->toHaveCount($totalPictograms);
        foreach ($result as $index => $dto) {
            expect($dto)->toBeInstanceOf(PictogramDTO::class);
            expect($dto->label)->toBe('pictograma-' . ($index + 1));
            expect($dto->arasaacId)->toBe(($index + 1) * 100);
        }
    });

});
