<?php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidCategoryColorException extends DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function empty(): self
    {
        return new self('Category color cannot be empty');
    }

    public static function invalidFormat(string $color): self
    {
        return new self(sprintf('Invalid hex color format: "%s" (expected #RRGGBB)', $color));
    }
}
