<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

/**
 * UUID v4 validator and normalizer.
 *
 * Only validates and normalizes UUIDs. Generation is handled by
 * UuidGeneratorInterface implementations in Infrastructure layer.
 *
 * @see https://www.rfc-editor.org/rfc/rfc4122
 */
final class Uuid
{
    /**
     * UUID v4 pattern: 8-4-4-4-12 hex chars
     * - Position 14: must be '4' (version)
     * - Position 19: must be '8', '9', 'a', or 'b' (variant)
     */
    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private function __construct()
    {
    }

    /**
     * Validate UUID v4 format.
     *
     * Accepts both uppercase and lowercase.
     */
    public static function isValid(string $value): bool
    {
        return preg_match(self::UUID_V4_PATTERN, $value) === 1;
    }

    /**
     * Normalize UUID to lowercase.
     */
    public static function normalize(string $value): string
    {
        return strtolower($value);
    }
}
