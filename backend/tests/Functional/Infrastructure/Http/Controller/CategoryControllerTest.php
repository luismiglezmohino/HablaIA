<?php

declare(strict_types=1);

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;

describe('CategoryController', function (): void {
    describe('GET /api/categories', function (): void {
        it('returns categories', function (): void {
            $client = static::createClient();

            $category = new Category(
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'Acciones',
                'running',
                '#22C55E',
                2
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn([$category]);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            expect($client->getResponse()->headers->get('Content-Type'))->toBe('application/json');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(1);
            expect($data[0]['name'])->toBe('Acciones');
            expect($data[0]['colorHex'])->toBe('#22C55E');
            expect($data[0]['displayOrder'])->toBe(2);
        });

        it('returns multiple categories', function (): void {
            $client = static::createClient();

            $categories = [
                new Category(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'), 'Acciones', 'running', '#22C55E', 2),
                new Category(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440002'), 'Emociones', 'smile', '#3B82F6', 3),
                new Category(CategoryId::fromString('550e8400-e29b-41d4-a716-446655440003'), 'Personas', 'users', '#FBBF24', 1),
            ];

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn($categories);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveCount(3);
        });

        it('returns empty array when no categories', function (): void {
            $client = static::createClient();

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn([]);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toBeEmpty();
        });

        it('returns correct JSON structure', function (): void {
            $client = static::createClient();

            $category = new Category(
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'Emociones',
                'smile',
                '#3B82F6',
                3
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn([$category]);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data[0])->toHaveKeys(['id', 'name', 'icon', 'colorHex', 'displayOrder']);
        });

        it('handles category with null icon', function (): void {
            $client = static::createClient();

            $category = new Category(
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'Otros',
                null,
                '#F97316',
                4
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn([$category]);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data[0]['icon'])->toBeNull();
        });

        it('returns valid JSON', function (): void {
            $client = static::createClient();

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findAll')->willReturn([]);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories');

            json_decode($client->getResponse()->getContent(), true);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });

        it('rejects POST method', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/categories');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('rejects PUT method', function (): void {
            $client = static::createClient();

            $client->request('PUT', '/api/categories');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });
    });

    describe('GET /api/categories/{id}', function (): void {
        it('returns category by id', function (): void {
            $client = static::createClient();

            $category = new Category(
                CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                'Acciones',
                'running',
                '#22C55E',
                2
            );

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findById')->willReturn($category);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories/550e8400-e29b-41d4-a716-446655440001');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['id'])->toBe('550e8400-e29b-41d4-a716-446655440001');
            expect($data['name'])->toBe('Acciones');
            expect($data['colorHex'])->toBe('#22C55E');
            expect($data['displayOrder'])->toBe(2);
        });

        it('returns 404 when category not found', function (): void {
            $client = static::createClient();

            $repository = $this->createMock(CategoryRepository::class);
            $repository->method('findById')->willReturn(null);

            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories/550e8400-e29b-41d4-a716-446655440099');

            expect($client->getResponse()->getStatusCode())->toBe(404);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Category not found');
        });

        it('returns 400 with invalid UUID', function (): void {
            $client = static::createClient();

            $repository = $this->createMock(CategoryRepository::class);
            self::getContainer()->set(CategoryRepository::class, $repository);

            $client->request('GET', '/api/categories/invalid-uuid');

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Invalid UUID format');
        });
    });
});
