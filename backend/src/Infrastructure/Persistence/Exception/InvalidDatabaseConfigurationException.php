<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Exception;

final class InvalidDatabaseConfigurationException extends InfrastructureException
{
    public static function emptyUrl(): self
    {
        return new self('DATABASE_URL environment variable is required');
    }

    public static function malformedUrl(): self
    {
        return new self('DATABASE_URL is malformed and cannot be parsed');
    }

    public static function missingHost(): self
    {
        return new self('DATABASE_URL must include host');
    }

    public static function missingUser(): self
    {
        return new self('DATABASE_URL must include user');
    }

    public static function missingPassword(): self
    {
        return new self('DATABASE_URL must include password');
    }

    public static function missingDatabase(): self
    {
        return new self('DATABASE_URL must include database name');
    }

    public static function invalidDatabaseName(): self
    {
        return new self('DATABASE_URL contains invalid database name');
    }
}
