<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle;

use App\Infrastructure\Persistence\Exception\InvalidDatabaseConfigurationException;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\Postgres\DsnConnectionConfig;
use Cycle\Database\Config\PostgresDriverConfig;
use Cycle\Database\DatabaseManager;

/**
 * Factory for creating Cycle ORM DatabaseManager.
 *
 * Parses DATABASE_URL environment variable and creates a configured
 * DatabaseManager instance for PostgreSQL connections.
 *
 * @see https://cycle-orm.dev/docs/database-configuration
 */
final class DatabaseFactory
{
    /**
     * Creates a DatabaseManager from a DATABASE_URL string.
     *
     * URL format: postgresql://user:password@host:port/database?params
     *
     * Example:
     *   postgresql://hablaia_user:secret@127.0.0.1:5432/hablaia?serverVersion=16
     *
     * @param string $databaseUrl Full database connection URL
     *
     * @return DatabaseManager Configured database manager ready for use
     */
    public static function create(string $databaseUrl): DatabaseManager
    {
        if (empty($databaseUrl)) {
            throw InvalidDatabaseConfigurationException::emptyUrl();
        }

        $parsed = parse_url($databaseUrl);

        if ($parsed === false) {
            throw InvalidDatabaseConfigurationException::malformedUrl();
        }

        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? 5432;
        $user = $parsed['user'] ?? null;
        $pass = $parsed['pass'] ?? null;
        $dbname = ltrim($parsed['path'] ?? '', '/');

        if (empty($host)) {
            throw InvalidDatabaseConfigurationException::missingHost();
        }

        if (empty($user)) {
            throw InvalidDatabaseConfigurationException::missingUser();
        }

        if ($pass === null) {
            throw InvalidDatabaseConfigurationException::missingPassword();
        }

        if (empty($dbname)) {
            throw InvalidDatabaseConfigurationException::missingDatabase();
        }

        if (str_contains($dbname, '..') || str_contains($dbname, '/')) {
            throw InvalidDatabaseConfigurationException::invalidDatabaseName();
        }

        // Build PDO DSN string for PostgreSQL
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $dbname);

        // Configure Cycle Database with named connection
        $config = new DatabaseConfig([
            'default' => 'default',  // Default database alias
            'databases' => [
                'default' => ['connection' => 'postgres'],  // Map alias to connection
            ],
            'connections' => [
                'postgres' => new PostgresDriverConfig(
                    connection: new DsnConnectionConfig(
                        dsn: $dsn,
                        user: $user,
                        password: $pass
                    ),
                ),
            ],
        ]);

        return new DatabaseManager($config);
    }
}
