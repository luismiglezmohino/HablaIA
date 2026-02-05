<?php

declare(strict_types=1);

namespace App\Domain\Category\Entity;

use App\Domain\Category\Exception\InvalidCategoryColorException;
use App\Domain\Category\Exception\InvalidCategoryDisplayOrderException;
use App\Domain\Category\Exception\InvalidCategoryNameException;
use App\Domain\Category\ValueObject\CategoryId;

final readonly class Category
{
    private const int MAX_NAME_LENGTH = 50;

    public function __construct(
        private CategoryId $id,
        private string $name,
        private ?string $icon,
        private string $colorHex = '#6B7280',
        private int $displayOrder = 0
    ) {
        $this->validateName($name);
        $this->validateColorHex($colorHex);
        $this->validateDisplayOrder($displayOrder);
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

    public function colorHex(): string
    {
        return $this->colorHex;
    }

    public function displayOrder(): int
    {
        return $this->displayOrder;
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

    private function validateColorHex(string $colorHex): void
    {
        if ($colorHex === '') {
            throw InvalidCategoryColorException::empty();
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorHex)) {
            throw InvalidCategoryColorException::invalidFormat($colorHex);
        }
    }

    private function validateDisplayOrder(int $displayOrder): void
    {
        if ($displayOrder < 0) {
            throw InvalidCategoryDisplayOrderException::negative($displayOrder);
        }
    }
}
