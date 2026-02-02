<?php

declare(strict_types=1);

namespace Tests\Shared;

use App\Domain\Shared\Service\UuidGeneratorInterface;

/**
 * Fake UUID generator for testing.
 *
 * Uses random_bytes() for realistic UUID generation in tests.
 * In production, use Infrastructure implementation.
 */
final class FakeUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
