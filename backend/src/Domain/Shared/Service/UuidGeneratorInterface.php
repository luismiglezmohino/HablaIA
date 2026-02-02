<?php

declare(strict_types=1);

namespace App\Domain\Shared\Service;

/**
 * Contract for UUID generation services.
 *
 * Implementations must use cryptographically secure random number generators.
 */
interface UuidGeneratorInterface
{
    /**
     * Generate a new UUID v4.
     *
     * @return string UUID in format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     */
    public function generate(): string;
}
