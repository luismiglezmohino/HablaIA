<?php

declare(strict_types=1);

namespace App\Domain\Phrase\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidPictogramSequenceException extends DomainException
{
    private function __construct(
        string $message,
        private readonly ?int $actualCount = null,
        private readonly ?int $maxCount = null
    ) {
        parent::__construct($message);
    }

    public static function tooMany(int $actualCount, int $maxCount): self
    {
        return new self(
            sprintf('Too many pictograms in sequence: %d (max: %d)', $actualCount, $maxCount),
            $actualCount,
            $maxCount
        );
    }

    public static function empty(): self
    {
        return new self('Pictogram sequence cannot be empty');
    }

    public function getActualCount(): ?int
    {
        return $this->actualCount;
    }

    public function getMaxCount(): ?int
    {
        return $this->maxCount;
    }
}
