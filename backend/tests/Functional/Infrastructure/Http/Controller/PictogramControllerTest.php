<?php

declare(strict_types=1);

use App\Application\Pictogram\SearchPictogram;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Service\ImageDownloaderInterface;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryPictogramRepository;

beforeEach(function (): void {
    $this->pictogramRepository = $this->createMock(PictogramRepository::class);
    $this->categoryRepository = $this->createMock(CategoryRepository::class);
    $this->pictogramProvider = $this->createMock(PictogramProviderInterface::class);
    $this->imageDownloader = $this->createMock(ImageDownloaderInterface::class);
    $this->uuidGenerator = new FakeUuidGenerator();
});

describe('PictogramController', function (): void {
    describe('GET /api/pictograms', function (): void {
        it('returns all pictograms', function (): void {
            $client = static::createClient();

            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->pictogramRepository->method('findAll')->willReturn([$pictogram]);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            expect($client->getResponse()->headers->get('Content-Type'))->toBe('application/json');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(1);
            expect($data[0]['label'])->toBe('comer');
        });

        it('returns multiple pictograms', function (): void {
            $client = static::createClient();

            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $pictograms = [
                new Pictogram(
                    PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                    new ArasaacId(12345),
                    $categoryId,
                    'comer',
                    'https://static.arasaac.org/pictograms/12345/12345_500.png'
                ),
                new Pictogram(
                    PictogramId::fromString('550e8400-e29b-41d4-a716-446655440011'),
                    new ArasaacId(12346),
                    $categoryId,
                    'beber',
                    'https://static.arasaac.org/pictograms/12346/12346_500.png'
                ),
            ];

            $this->pictogramRepository->method('findAll')->willReturn($pictograms);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(2);
        });

        it('returns empty array when no pictograms', function (): void {
            $client = static::createClient();

            $this->pictogramRepository->method('findAll')->willReturn([]);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toBeEmpty();
        });

        it('returns correct JSON structure', function (): void {
            $client = static::createClient();

            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->pictogramRepository->method('findAll')->willReturn([$pictogram]);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data[0])->toHaveKeys(['id', 'arasaacId', 'categoryId', 'label', 'imagePath']);
        });

        it('filters by categoryId', function (): void {
            $client = static::createClient();

            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $category = new Category($categoryId, 'Acciones', 'running');
            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->categoryRepository->method('findById')->willReturn($category);
            $this->pictogramRepository->method('findByCategoryId')->willReturn([$pictogram]);

            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms?categoryId=550e8400-e29b-41d4-a716-446655440001');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(1);
            expect($data[0]['categoryId'])->toBe('550e8400-e29b-41d4-a716-446655440001');
        });

        it('returns 404 when category not found', function (): void {
            $client = static::createClient();

            $this->categoryRepository->method('findById')->willReturn(null);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms?categoryId=550e8400-e29b-41d4-a716-446655440099');

            expect($client->getResponse()->getStatusCode())->toBe(404);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveKey('error');
        });

        it('returns 400 with invalid UUID format', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms?categoryId=invalid-uuid');

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Invalid UUID format');
        });

        it('returns valid JSON', function (): void {
            $client = static::createClient();

            $this->pictogramRepository->method('findAll')->willReturn([]);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms');

            json_decode($client->getResponse()->getContent(), true);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });

        it('rejects POST method', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/pictograms');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('rejects PUT method', function (): void {
            $client = static::createClient();

            $client->request('PUT', '/api/pictograms');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });
    });

    describe('GET /api/pictograms/{id}', function (): void {
        it('returns pictogram by id', function (): void {
            $client = static::createClient();

            $categoryId = CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001');
            $pictogram = new Pictogram(
                PictogramId::fromString('550e8400-e29b-41d4-a716-446655440010'),
                new ArasaacId(12345),
                $categoryId,
                'comer',
                'https://static.arasaac.org/pictograms/12345/12345_500.png'
            );

            $this->pictogramRepository->method('findById')->willReturn($pictogram);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/550e8400-e29b-41d4-a716-446655440010');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['id'])->toBe('550e8400-e29b-41d4-a716-446655440010');
            expect($data['label'])->toBe('comer');
            expect($data['arasaacId'])->toBe(12345);
        });

        it('returns 404 when pictogram not found', function (): void {
            $client = static::createClient();

            $this->pictogramRepository->method('findById')->willReturn(null);

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/550e8400-e29b-41d4-a716-446655440099');

            expect($client->getResponse()->getStatusCode())->toBe(404);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Pictogram not found');
        });

        it('returns 400 with invalid UUID', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->pictogramRepository);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/invalid-uuid');

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Invalid UUID format');
        });
    });

    describe('GET /api/pictograms/search', function (): void {

        it('returns 400 when query parameter q is missing', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search');

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Query parameter q is required');
        });

        it('returns 400 when query is too short', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=a');

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Search query must be at least 2 characters');
        });

        it('returns empty array when no pictograms found', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=dinosaurio');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toBeEmpty();
        });

        it('returns pictograms found in local database', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();

            // Add pictogram to repository
            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(12345),
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'dinosaurio',
                '/pictograms/dinosaurio.png'
            );
            $pictogramRepo->save($pictogram);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=dino');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(1);
            expect($data[0]['label'])->toBe('dinosaurio');
        });

        it('finds accented labels when searching without accents', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();

            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(9853),
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'quién',
                '/pictograms/9853.png'
            );
            $pictogramRepo->save($pictogram);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=quien');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(1);
            expect($data[0]['label'])->toBe('quién');
        });

        it('returns correct JSON structure', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();

            $pictogram = new Pictogram(
                PictogramId::fromString($this->uuidGenerator->generate()),
                new ArasaacId(12345),
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'perro',
                '/pictograms/perro.png'
            );
            $pictogramRepo->save($pictogram);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=perro');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data[0])->toHaveKeys(['id', 'arasaacId', 'categoryId', 'label', 'imagePath']);
        });

        it('rejects POST method', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/pictograms/search?q=test');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('returns valid JSON', function (): void {
            $client = static::createClient();

            $pictogramRepo = new InMemoryPictogramRepository();
            $this->pictogramProvider->method('searchByKeyword')->willReturn([]);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(CategoryRepository::class, $this->categoryRepository);
            self::getContainer()->set(PictogramProviderInterface::class, $this->pictogramProvider);
            self::getContainer()->set(ImageDownloaderInterface::class, $this->imageDownloader);
            self::getContainer()->set(UuidGeneratorInterface::class, $this->uuidGenerator);

            $client->request('GET', '/api/pictograms/search?q=test');

            json_decode($client->getResponse()->getContent(), true);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });

    });
});
