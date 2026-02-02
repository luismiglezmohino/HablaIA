<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO for phrase generation response.
 */
final readonly class PhraseResponseDTO
{
    public const string SOURCE_CACHE = 'cache';
    public const string SOURCE_GENERATED = 'generated';
    public const string SOURCE_FALLBACK = 'fallback';

    /**
     * @param array<string> $variations
     * @param array<string> $pictogramIds
     */
    public function __construct(
        public array $variations,
        public string $source,
        public string $sequenceHash,
        public array $pictogramIds
    ) {}
}
