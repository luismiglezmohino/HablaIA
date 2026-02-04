<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Console;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Console\SyncArasaacCommand;
use App\Infrastructure\Service\ImageDownloaderInterface;
use App\Infrastructure\Service\VocabularyLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe('SyncArasaacCommand', function (): void {
    beforeEach(function (): void {
        $this->pictogramProvider = $this->createMock(PictogramProviderInterface::class);
        $this->pictogramRepository = $this->createMock(PictogramRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->vocabularyLoader = $this->createMock(VocabularyLoaderInterface::class);
        $this->imageDownloader = $this->createMock(ImageDownloaderInterface::class);

        $this->command = new SyncArasaacCommand(
            $this->pictogramProvider,
            $this->pictogramRepository,
            $this->categoryRepository,
            $this->uuidGenerator,
            $this->vocabularyLoader,
            $this->imageDownloader,
            '/tmp/test/pictograms'
        );

        $this->commandTester = new CommandTester($this->command);
    });

    it('has correct command name', function (): void {
        expect($this->command->getName())->toBe('app:arasaac:sync');
    });

    it('has correct description', function (): void {
        expect($this->command->getDescription())
            ->toBe('Synchronize pictograms from ARASAAC API');
    });

    describe('arguments and options', function (): void {
        it('has optional keywords argument', function (): void {
            $definition = $this->command->getDefinition();

            expect($definition->hasArgument('keywords'))->toBeTrue();
            expect($definition->getArgument('keywords')->isRequired())->toBeFalse();
            expect($definition->getArgument('keywords')->isArray())->toBeTrue();
        });

        it('has category option', function (): void {
            $definition = $this->command->getDefinition();

            expect($definition->hasOption('category'))->toBeTrue();
            expect($definition->getOption('category')->getShortcut())->toBe('c');
        });

        it('has all option', function (): void {
            $definition = $this->command->getDefinition();

            expect($definition->hasOption('all'))->toBeTrue();
            expect($definition->getOption('all')->getShortcut())->toBe('a');
        });

        it('has dry-run option', function (): void {
            $definition = $this->command->getDefinition();

            expect($definition->hasOption('dry-run'))->toBeTrue();
        });

        it('has limit option with default value of 1', function (): void {
            $definition = $this->command->getDefinition();

            expect($definition->hasOption('limit'))->toBeTrue();
            expect($definition->getOption('limit')->getShortcut())->toBe('l');
            expect($definition->getOption('limit')->getDefault())->toBe(1);
        });
    });

    describe('execution with specific keywords', function (): void {
        it('syncs pictograms for given keywords', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->with('Acciones')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->with('comer', 'es')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->pictogramRepository
                ->expects($this->once())
                ->method('save');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
            expect($this->commandTester->getDisplay())->toContain('Synced 1 pictogram(s)');
        });

        it('respects limit option', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictograms = [
                new Pictogram(
                    PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                    new ArasaacId(12345),
                    $categoryId,
                    'comer',
                    'https://static.arasaac.org/pictograms/12345/12345_500.png'
                ),
                new Pictogram(
                    PictogramId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                    new ArasaacId(12346),
                    $categoryId,
                    'comida',
                    'https://static.arasaac.org/pictograms/12346/12346_500.png'
                ),
            ];

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn($pictograms);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->pictogramRepository
                ->expects($this->exactly(2))
                ->method('save');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
                '--limit' => 2,
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
        });

        it('shows warning when category not found', function (): void {
            $this->categoryRepository
                ->method('findByName')
                ->willReturn(null);

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'NonExistent',
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE);
            expect($this->commandTester->getDisplay())->toContain('Category "NonExistent" not found');
        });
    });

    describe('execution with --all option', function (): void {
        it('loads vocabulary from YAML file via VocabularyLoader', function (): void {
            $vocabulary = [
                'Personas' => ['yo', 'tu'],
                'Acciones' => ['comer', 'beber'],
            ];

            $personasId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $accionesId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440002');

            $personas = new Category($personasId, 'Personas', null);
            $acciones = new Category($accionesId, 'Acciones', null);

            $this->vocabularyLoader
                ->expects($this->once())
                ->method('load')
                ->willReturn($vocabulary);

            $this->categoryRepository
                ->method('findByName')
                ->willReturnCallback(function (string $name) use ($personas, $acciones): ?Category {
                    return match ($name) {
                        'Personas' => $personas,
                        'Acciones' => $acciones,
                        default => null,
                    };
                });

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $personasId,
                'yo',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->commandTester->execute([
                '--all' => true,
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
            expect($this->commandTester->getDisplay())->toContain('Syncing vocabulary from YAML');
        });

        it('skips categories that do not exist in database', function (): void {
            $vocabulary = [
                'Personas' => ['yo'],
                'Acciones' => ['comer'],
            ];

            $personasId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $personas = new Category($personasId, 'Personas', null);

            $this->vocabularyLoader
                ->method('load')
                ->willReturn($vocabulary);

            $this->categoryRepository
                ->method('findByName')
                ->willReturnCallback(function (string $name) use ($personas): ?Category {
                    return $name === 'Personas' ? $personas : null;
                });

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $personasId,
                'yo',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->commandTester->execute([
                '--all' => true,
            ]);

            expect($this->commandTester->getDisplay())->toContain('Category "Acciones" not found, skipping');
        });
    });

    describe('dry-run mode', function (): void {
        it('does not save pictograms in dry-run mode', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->pictogramRepository
                ->expects($this->never())
                ->method('save');

            $this->imageDownloader
                ->expects($this->never())
                ->method('download');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
                '--dry-run' => true,
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
            expect($this->commandTester->getDisplay())->toContain('[DRY-RUN]');
        });

        it('shows what would be synced in dry-run mode', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
                '--dry-run' => true,
            ]);

            expect($this->commandTester->getDisplay())->toContain('Would sync: comer');
        });
    });

    describe('image downloading', function (): void {
        it('downloads images to local storage', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->expects($this->once())
                ->method('download')
                ->with(
                    'https://static.arasaac.org/pictograms/12345/12345_500.png',
                    '/tmp/test/pictograms/12345.png'
                )
                ->willReturn(true);

            $this->pictogramRepository
                ->expects($this->once())
                ->method('save')
                ->with($this->callback(function (Pictogram $p): bool {
                    return $p->imagePath() === '/pictograms/12345.png';
                }));

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
        });

        it('skips download when image already exists locally', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->expects($this->once())
                ->method('exists')
                ->with('/tmp/test/pictograms/12345.png')
                ->willReturn(true);

            $this->imageDownloader
                ->expects($this->never())
                ->method('download');

            $this->pictogramRepository
                ->expects($this->once())
                ->method('save');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getDisplay())->toContain('Image already exists');
        });

        it('handles image download failure gracefully', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->imageDownloader
                ->method('exists')
                ->willReturn(false);

            $this->imageDownloader
                ->method('download')
                ->willReturn(false);

            $this->pictogramRepository
                ->expects($this->never())
                ->method('save');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getDisplay())->toContain('Failed to download image');
        });

        it('does not download images in dry-run mode', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->imageDownloader
                ->expects($this->never())
                ->method('download');

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
                '--dry-run' => true,
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
        });
    });

    describe('error handling', function (): void {
        it('handles API errors gracefully', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willThrowException(new \RuntimeException('API Error'));

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);
            expect($this->commandTester->getDisplay())->toContain('Warning: Failed to fetch "comer"');
        });

        it('continues processing after API error', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'beber',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $callCount = 0;
            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturnCallback(function (string $keyword) use (&$callCount, $pictogram): array {
                    $callCount++;
                    if ($keyword === 'comer') {
                        throw new \RuntimeException('API Error');
                    }

                    return [$pictogram];
                });

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('exists')
                ->willReturn(false);

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->commandTester->execute([
                'keywords' => ['comer', 'beber'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getDisplay())->toContain('Warning: Failed to fetch "comer"');
            expect($this->commandTester->getDisplay())->toContain('Synced 1 pictogram(s)');
        });

        it('requires keywords or --all option', function (): void {
            $this->commandTester->execute([]);

            expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE);
            expect($this->commandTester->getDisplay())
                ->toContain('Please provide keywords or use --all option');
        });

        it('requires category when using specific keywords', function (): void {
            $this->commandTester->execute([
                'keywords' => ['comer'],
            ]);

            expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE);
            expect($this->commandTester->getDisplay())
                ->toContain('Please specify a category with --category option');
        });
    });

    describe('output formatting', function (): void {
        it('shows progress during sync', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('exists')
                ->willReturn(false);

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getDisplay())->toContain('Searching for "comer"');
        });

        it('shows summary at the end', function (): void {
            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', null);

            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository
                ->method('findByName')
                ->willReturn($category);

            $this->pictogramProvider
                ->method('searchByKeyword')
                ->willReturn([$pictogram]);

            $this->uuidGenerator
                ->method('generate')
                ->willReturn('550e8400-e29b-41d4-a716-446655440099');

            $this->imageDownloader
                ->method('exists')
                ->willReturn(false);

            $this->imageDownloader
                ->method('download')
                ->willReturn(true);

            $this->commandTester->execute([
                'keywords' => ['comer'],
                '--category' => 'Acciones',
            ]);

            expect($this->commandTester->getDisplay())->toContain('Sync completed');
        });
    });
});
