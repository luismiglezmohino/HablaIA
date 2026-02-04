<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle;

use App\Infrastructure\Persistence\Cycle\DatabaseFactory;
use App\Infrastructure\Persistence\Exception\InvalidDatabaseConfigurationException;
use Cycle\Database\DatabaseManager;

describe('DatabaseFactory', function (): void {
    describe('validation errors', function (): void {
        it('throws exception for empty URL', function (): void {
            expect(fn () => DatabaseFactory::create(''))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'DATABASE_URL environment variable is required');
        });

        it('throws exception for malformed URL', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql:///database'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'malformed');
        });

        it('throws exception for missing host', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql:database'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'host');
        });

        it('throws exception for missing user', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql://localhost/database'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'user');
        });

        it('throws exception for missing password', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql://user@localhost/database'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'password');
        });

        it('throws exception for missing database name', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql://user:pass@localhost/'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'database name');
        });

        it('throws exception for invalid database name with path traversal', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql://user:pass@localhost/../etc/passwd'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'invalid database name');
        });

        it('throws exception for invalid database name with slash', function (): void {
            expect(fn () => DatabaseFactory::create('postgresql://user:pass@localhost/db/name'))
                ->toThrow(InvalidDatabaseConfigurationException::class, 'invalid database name');
        });
    });

    describe('successful creation', function (): void {
        it('creates DatabaseManager with valid URL', function (): void {
            $url = 'postgresql://user:password@localhost:5432/testdb';

            $manager = DatabaseFactory::create($url);

            expect($manager)->toBeInstanceOf(DatabaseManager::class);
        });
    });
});
