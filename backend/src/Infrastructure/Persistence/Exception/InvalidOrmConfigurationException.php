<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Exception;

final class InvalidOrmConfigurationException extends InfrastructureException
{
    public static function emptyEntityPath(): self
    {
        return new self('Entity path cannot be empty');
    }

    public static function pathTraversalDetected(): self
    {
        return new self('Entity path cannot contain path traversal sequences (..)');
    }

    public static function directoryNotFound(string $path): self
    {
        return new self(sprintf('Entity path does not exist or is not a directory: %s', $path));
    }

    public static function pathCannotBeResolved(string $path): self
    {
        return new self(sprintf('Entity path cannot be resolved: %s', $path));
    }
}
