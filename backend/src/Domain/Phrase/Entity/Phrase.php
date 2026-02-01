<?php

declare(strict_types=1);

namespace App\Domain\Phrase\Entity;

use App\Domain\Phrase\Exception\InvalidPhraseVariationsException;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use DateTimeImmutable;

final readonly class Phrase
{
    private const int MIN_VARIATIONS = 1;
    private const int MAX_VARIATIONS = 3;
    private const int MAX_VARIATION_LENGTH = 500;

    /**
     * @param array<string> $variations
     */
    public function __construct(
        private PhraseId $id,
        private PictogramSequence $pictogramSequence,
        private array $variations,
        private DateTimeImmutable $createdAt
    ) {
        $this->validateVariations($variations);
    }

    public function id(): PhraseId
    {
        return $this->id;
    }

    public function pictogramSequence(): PictogramSequence
    {
        return $this->pictogramSequence;
    }

    /**
     * @return array<string>
     */
    public function variations(): array
    {
        return $this->variations;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function sequenceHash(): string
    {
        return $this->pictogramSequence->hash();
    }

    /**
     * @param array<string> $variations
     */
    private function validateVariations(array $variations): void
    {
        $count = count($variations);

        if ($count < self::MIN_VARIATIONS) {
            throw InvalidPhraseVariationsException::empty();
        }

        if ($count > self::MAX_VARIATIONS) {
            throw InvalidPhraseVariationsException::tooMany($count, self::MAX_VARIATIONS);
        }

        foreach ($variations as $index => $variation) {
            if ($variation === '') {
                throw InvalidPhraseVariationsException::empty();
            }

            $length = strlen($variation);
            if ($length > self::MAX_VARIATION_LENGTH) {
                throw InvalidPhraseVariationsException::variationTooLong($index, $length, self::MAX_VARIATION_LENGTH);
            }
        }
    }
}
