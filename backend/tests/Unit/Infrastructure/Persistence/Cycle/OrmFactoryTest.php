<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Cycle;

use App\Infrastructure\Persistence\Cycle\DatabaseFactory;
use App\Infrastructure\Persistence\Cycle\OrmFactory;
use App\Infrastructure\Persistence\Exception\InvalidOrmConfigurationException;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\ORM;

describe('OrmFactory', function (): void {
    describe('validation errors', function (): void {
        it('throws exception for empty entity path', function (): void {
            $dbal = createMockDatabaseManager();

            expect(fn () => OrmFactory::create($dbal, ''))
                ->toThrow(InvalidOrmConfigurationException::class, 'Entity path cannot be empty');
        });

        it('throws exception for path traversal attempt', function (): void {
            $dbal = createMockDatabaseManager();

            expect(fn () => OrmFactory::create($dbal, '/var/www/../etc/passwd'))
                ->toThrow(InvalidOrmConfigurationException::class, 'path traversal');
        });

        it('throws exception for non-existent directory', function (): void {
            $dbal = createMockDatabaseManager();

            expect(fn () => OrmFactory::create($dbal, '/non/existent/path'))
                ->toThrow(InvalidOrmConfigurationException::class, 'does not exist');
        });

        it('throws exception when path is a file not a directory', function (): void {
            $dbal = createMockDatabaseManager();
            $tempFile = tempnam(sys_get_temp_dir(), 'test');

            expect(fn () => OrmFactory::create($dbal, $tempFile))
                ->toThrow(InvalidOrmConfigurationException::class, 'does not exist');

            unlink($tempFile);
        });
    });

    describe('successful creation', function (): void {
        it('creates ORM with valid configuration', function (): void {
            $dbal = DatabaseFactory::create('postgresql://user:password@localhost:5432/testdb');
            $entityPath = realpath(__DIR__ . '/../../../../src/Infrastructure/Persistence/Cycle/Entity');

            if ($entityPath === false) {
                $entityPath = dirname(__DIR__, 4) . '/src/Infrastructure/Persistence/Cycle/Entity';
                if (!is_dir($entityPath)) {
                    mkdir($entityPath, 0755, true);
                }
            }

            $orm = OrmFactory::create($dbal, $entityPath);

            expect($orm)->toBeInstanceOf(ORM::class);
        });
    });
});

function createMockDatabaseManager(): DatabaseManager
{
    return DatabaseFactory::create('postgresql://user:password@localhost:5432/testdb');
}
