<?php

declare(strict_types=1);

namespace App\Application\Exception;

/**
 * Exception thrown when a category is not found.
 */
final class CategoryNotFoundException extends ApplicationException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(string $id): self
    {
        return new self(sprintf('Category with ID "%s" was not found', $id));
    }
}
