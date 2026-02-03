<?php

declare(strict_types=1);

namespace Tests\Shared;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use RuntimeException;

/**
 * Fake implementation of PhraseGeneratorInterface for testing.
 */
final class FakePhraseGenerator implements PhraseGeneratorInterface
{
    private bool $shouldFail = false;

    /** @var array<string> */
    private array $variations = [
        'Quiero comer pan',
        'Me gustaría comer pan',
        'Deseo comer pan',
    ];

    public function generate(PictogramSequence $sequence): array
    {
        if ($this->shouldFail) {
            throw new RuntimeException('Generator failure');
        }

        return $this->variations;
    }

    public function willFail(): void
    {
        $this->shouldFail = true;
    }

    /**
     * @param array<string> $variations
     */
    public function setVariations(array $variations): void
    {
        $this->variations = $variations;
    }
}
