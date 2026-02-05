<?php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidCategoryDisplayOrderException extends DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function negative(int $order): self
    {
        return new self(sprintf('Display order must be non-negative, got: %d', $order));
    }
}
