<?php

declare(strict_types=1);

namespace App\Domain\Phrase\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidPhraseVariationsException extends DomainException
{
    private function __construct(
        string $message,
        private readonly ?int $actualCount = null,
        private readonly ?int $maxCount = null,
        private readonly ?int $index = null,
        private readonly ?int $actualLength = null,
        private readonly ?int $maxLength = null
    ) {
        parent::__construct($message);
    }

    public static function tooMany(int $actualCount, int $maxCount): self
    {
        return new self(
            sprintf('Too many phrase variations: %d (max: %d)', $actualCount, $maxCount),
            $actualCount,
            $maxCount
        );
    }

    public static function empty(): self
    {
        return new self('Phrase must have at least one variation');
    }

    public static function variationTooLong(int $index, int $actualLength, int $maxLength): self
    {
        return new self(
            sprintf(
                'Phrase variation at index %d is too long: %d characters (max: %d)',
                $index,
                $actualLength,
                $maxLength
            ),
            null,
            null,
            $index,
            $actualLength,
            $maxLength
        );
    }

    public function getActualCount(): ?int
    {
        return $this->actualCount;
    }

    public function getMaxCount(): ?int
    {
        return $this->maxCount;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function getActualLength(): ?int
    {
        return $this->actualLength;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }
}
