<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;
use InvalidArgumentException;

final readonly class CategoryId
{
    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID v4 format');
        }

        return new self(Uuid::normalize($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
