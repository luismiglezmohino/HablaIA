<?php

declare(strict_types=1);

use App\Application\DTO\PictogramDTO;
use App\Application\Pictogram\SearchPictogram;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Service\ImageDownloaderInterface;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryPictogramRepository;

beforeEach(function (): void {
    $this->repository = new InMemoryPictogramRepository();
    $this->pictogramProvider = $this->createMock(PictogramProviderInterface::class);
    $this->imageDownloader = $this->createMock(ImageDownloaderInterface::class);
    $this->uuidGenerator = new FakeUuidGenerator();
    $this->useCase = new SearchPictogram(
        $this->repository,
        $this->pictogramProvider,
        $this->imageDownloader,
        $this->uuidGenerator,
        '/var/www/public/pictograms'
    );
});

describe('SearchPictogram', function (): void {

    describe('Query validation', function (): void {

        it('throws exception when query is empty', function (): void {
            expect(fn () => ($this->useCase)(''))
                ->toThrow(InvalidArgumentException::class, 'Search query must be at least 2 characters');
        });

        it('throws exception when query is one character', function (): void {
            expect(fn () => ($this->useCase)('a'))
                ->toThrow(InvalidArgumentException::class, 'Search query must be at least 2 characters');
        });

        it('throws exception when query exceeds 100 characters', function (): void {
            $longQuery = str_repeat('a', 101);

            expect(fn () => ($this->useCase)($longQuery))
                ->toThrow(InvalidArgumentException::class, 'Search query must not exceed 100 characters');
        });

        it('accepts query with exactly 100 characters', function (): void {
            $query = str_repeat('a', 100);
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            $result = ($this->useCase)($query);

            expect($result)->toBe([]);
        });

        it('accepts query with exactly 2 characters', function (): void {
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            $result = ($this->useCase)('ab');

            expect($result)->toBe([]);
        });

    });

    describe('Local search', function (): void {

        it('returns empty array when no pictograms match locally or remotely', function (): void {
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            $result = ($this->useCase)('dinosaurio');

            expect($result)->toBe([]);
        });

        it('returns local pictograms when found in database', function (): void {
            // Arrange - add a pictogram to local repository
            $pictogramId = $this->uuidGenerator->generate();
            $categoryId = $this->uuidGenerator->generate();

            $pictogram = new Pictogram(
                PictogramId::fromString($pictogramId),
                new ArasaacId(12345),
                CategoryId::fromString($categoryId),
                'dinosaurio',
                '/pictograms/dinosaurio.png'
            );
            $this->repository->save($pictogram);

            // API should NOT be called when local results exist
            $this->pictogramProvider->expects($this->never())->method('searchByKeyword');

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert
            expect($result)->toHaveCount(1);
            expect($result[0])->toBeInstanceOf(PictogramDTO::class);
            expect($result[0]->label)->toBe('dinosaurio');
        });

        it('finds pictograms with partial match (LIKE query)', function (): void {
            // Arrange
            $categoryId = $this->uuidGenerator->generate();

            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(12345),
                CategoryId::fromString($categoryId),
                'dinosaurio rex',
                '/pictograms/dinosaurio-rex.png'
            );
            $this->repository->save($pictogram);

            $this->pictogramProvider->expects($this->never())->method('searchByKeyword');

            // Act
            $result = ($this->useCase)('dino');

            // Assert
            expect($result)->toHaveCount(1);
            expect($result[0]->label)->toBe('dinosaurio rex');
        });

        it('is case insensitive', function (): void {
            // Arrange
            $categoryId = $this->uuidGenerator->generate();

            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(12345),
                CategoryId::fromString($categoryId),
                'Dinosaurio',
                '/pictograms/dinosaurio.png'
            );
            $this->repository->save($pictogram);

            $this->pictogramProvider->expects($this->never())->method('searchByKeyword');

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert
            expect($result)->toHaveCount(1);
        });

        it('limits results to 10 pictograms', function (): void {
            // Arrange
            $categoryId = $this->uuidGenerator->generate();

            for ($i = 1; $i <= 15; $i++) {
                $pictogram = new Pictogram(
                    PictogramId::fromString($this->uuidGenerator->generate()),
                    new ArasaacId(12345 + $i),
                    CategoryId::fromString($categoryId),
                    "perro{$i}",
                    "/pictograms/perro{$i}.png"
                );
                $this->repository->save($pictogram);
            }

            $this->pictogramProvider->expects($this->never())->method('searchByKeyword');

            // Act
            $result = ($this->useCase)('perro');

            // Assert
            expect($result)->toHaveCount(10);
        });

    });

    describe('Remote search (ARASAAC API)', function (): void {

        it('calls ARASAAC API when no local results found', function (): void {
            // Arrange
            $this->pictogramProvider->expects($this->once())
                ->method('searchByKeyword')
                ->with('dinosaurio', 'es')
                ->willReturn([]);

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert
            expect($result)->toBe([]);
        });

        it('downloads image and saves pictogram when found in ARASAAC', function (): void {
            // Arrange
            $categoryId = '00000000-0000-4000-8000-000000000000';
            $arasaacPictogram = new Pictogram(
                PictogramId::fromString('11111111-1111-4111-8111-111111111111'),
                new ArasaacId(99999),
                CategoryId::fromString($categoryId),
                'dinosaurio',
                'https://static.arasaac.org/pictograms/99999/99999_500.png'
            );

            $this->pictogramProvider->method('searchByKeyword')
                ->willReturn([$arasaacPictogram]);

            $this->imageDownloader->expects($this->once())
                ->method('download')
                ->with(
                    'https://static.arasaac.org/pictograms/99999/99999_500.png',
                    $this->stringContains('/pictograms/99999.png')
                )
                ->willReturn(true);

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert
            expect($result)->toHaveCount(1);
            expect($result[0]->label)->toBe('dinosaurio');
            expect($result[0]->arasaacId)->toBe(99999);

            // Verify saved in repository
            $savedPictograms = $this->repository->findAll();
            expect($savedPictograms)->toHaveCount(1);
        });

        it('generates new UUID when saving ARASAAC pictogram', function (): void {
            // Arrange
            $categoryId = '00000000-0000-4000-8000-000000000000';
            $arasaacPictogram = new Pictogram(
                PictogramId::fromString('11111111-1111-4111-8111-111111111111'),
                new ArasaacId(99999),
                CategoryId::fromString($categoryId),
                'dinosaurio',
                'https://static.arasaac.org/pictograms/99999/99999_500.png'
            );

            $this->pictogramProvider->method('searchByKeyword')
                ->willReturn([$arasaacPictogram]);

            $this->imageDownloader->method('download')->willReturn(true);

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert - the ID should be different from the ARASAAC one (new UUID generated)
            expect($result[0]->id)->not->toBe('11111111-1111-4111-8111-111111111111');
        });

        it('updates imagePath to local path after downloading', function (): void {
            // Arrange
            $categoryId = '00000000-0000-4000-8000-000000000000';
            $arasaacPictogram = new Pictogram(
                PictogramId::fromString('11111111-1111-4111-8111-111111111111'),
                new ArasaacId(99999),
                CategoryId::fromString($categoryId),
                'dinosaurio',
                'https://static.arasaac.org/pictograms/99999/99999_500.png'
            );

            $this->pictogramProvider->method('searchByKeyword')
                ->willReturn([$arasaacPictogram]);

            $this->imageDownloader->method('download')->willReturn(true);

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert - imagePath should be local, not CDN URL
            expect($result[0]->imagePath)->toBe('/pictograms/99999.png');
        });

        it('limits ARASAAC results to 10 pictograms', function (): void {
            // Arrange
            $categoryId = '00000000-0000-4000-8000-000000000000';
            $arasaacPictograms = [];

            for ($i = 1; $i <= 15; $i++) {
                $arasaacPictograms[] = new Pictogram(
                    PictogramId::fromString($this->uuidGenerator->generate()),
                    new ArasaacId(90000 + $i),
                    CategoryId::fromString($categoryId),
                    "perro{$i}",
                    "https://static.arasaac.org/pictograms/" . (90000 + $i) . "/" . (90000 + $i) . "_500.png"
                );
            }

            $this->pictogramProvider->method('searchByKeyword')
                ->willReturn($arasaacPictograms);

            $this->imageDownloader->method('download')->willReturn(true);

            // Act
            $result = ($this->useCase)('perro');

            // Assert
            expect($result)->toHaveCount(10);
        });

        it('skips pictogram if image download fails', function (): void {
            // Arrange
            $categoryId = '00000000-0000-4000-8000-000000000000';
            $arasaacPictogram = new Pictogram(
                PictogramId::fromString('11111111-1111-4111-8111-111111111111'),
                new ArasaacId(99999),
                CategoryId::fromString($categoryId),
                'dinosaurio',
                'https://static.arasaac.org/pictograms/99999/99999_500.png'
            );

            $this->pictogramProvider->method('searchByKeyword')
                ->willReturn([$arasaacPictogram]);

            $this->imageDownloader->method('download')->willReturn(false);

            // Act
            $result = ($this->useCase)('dinosaurio');

            // Assert
            expect($result)->toBe([]);
            expect($this->repository->findAll())->toBe([]);
        });

        it('does not call ARASAAC again for already saved pictogram by arasaacId', function (): void {
            // Arrange - first, save a pictogram with the same arasaacId
            $categoryId = $this->uuidGenerator->generate();
            $existingPictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(99999),
                CategoryId::fromString($categoryId),
                'dinosaurio grande',
                '/pictograms/99999.png'
            );
            $this->repository->save($existingPictogram);

            // ARASAAC returns same pictogram
            $arasaacPictogram = new Pictogram(
                PictogramId::fromString('11111111-1111-4111-8111-111111111111'),
                new ArasaacId(99999),
                CategoryId::fromString('00000000-0000-4000-8000-000000000000'),
                'dinosaurio',
                'https://static.arasaac.org/pictograms/99999/99999_500.png'
            );

            $this->pictogramProvider->method('searchByKeyword')->willReturn([$arasaacPictogram]);

            // Image downloader should NOT be called for existing pictogram
            $this->imageDownloader->expects($this->never())->method('download');

            // Act - search with different term that doesn't match local label
            $result = ($this->useCase)('dino');

            // Assert - should return local pictogram, not duplicate
            expect($result)->toHaveCount(1);
            expect($result[0]->label)->toBe('dinosaurio grande');
        });

    });

    describe('Security', function (): void {

        it('sanitizes query to prevent SQL injection', function (): void {
            // This should not throw and should be safely handled
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            $result = ($this->useCase)("test'; DROP TABLE pictograms;--");

            expect($result)->toBe([]);
        });

        it('trims whitespace from query', function (): void {
            $categoryId = $this->uuidGenerator->generate();
            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(12345),
                CategoryId::fromString($categoryId),
                'perro',
                '/pictograms/perro.png'
            );
            $this->repository->save($pictogram);

            $this->pictogramProvider->expects($this->never())->method('searchByKeyword');

            $result = ($this->useCase)('  perro  ');

            expect($result)->toHaveCount(1);
        });

    });

});
