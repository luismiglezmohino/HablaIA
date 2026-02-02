<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidPictogramLabelException extends DomainException
{
    private function __construct(
        string $message,
        private readonly ?int $actualLength = null,
        private readonly ?int $maxLength = null
    ) {
        parent::__construct($message);
    }

    public static function tooLong(int $actualLength, int $maxLength): self
    {
        return new self(
            sprintf('Pictogram label is too long: %d characters (max: %d)', $actualLength, $maxLength),
            $actualLength,
            $maxLength
        );
    }

    public static function empty(): self
    {
        return new self('Pictogram label cannot be empty');
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
