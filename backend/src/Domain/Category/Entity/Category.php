<?php

declare(strict_types=1);

namespace App\Domain\Category\Entity;

use App\Domain\Category\Exception\InvalidCategoryNameException;
use App\Domain\Category\ValueObject\CategoryId;

final readonly class Category
{
    private const int MAX_NAME_LENGTH = 50;

    public function __construct(
        private CategoryId $id,
        private string $name,
        private ?string $icon
    ) {
        $this->validateName($name);
    }

    public function id(): CategoryId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    private function validateName(string $name): void
    {
        if ($name === '') {
            throw InvalidCategoryNameException::empty();
        }

        $length = strlen($name);
        if ($length > self::MAX_NAME_LENGTH) {
            throw InvalidCategoryNameException::tooLong($length, self::MAX_NAME_LENGTH);
        }
    }
}
