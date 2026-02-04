<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared;

use App\Domain\Shared\Service\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Symfony implementation of UUID generator.
 *
 * Uses Symfony's Uid component for cryptographically secure UUID v4 generation.
 */
final readonly class SymfonyUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
