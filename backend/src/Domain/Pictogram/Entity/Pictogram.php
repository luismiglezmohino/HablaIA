<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Entity;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use InvalidArgumentException;

final readonly class Pictogram
{
    private const int MAX_LABEL_LENGTH = 100;

    public function __construct(
        private PictogramId $id,
        private ArasaacId $arasaacId,
        private CategoryId $categoryId,
        private string $label,
        private string $imagePath
    ) {
        $this->validateLabel($label);
        $this->validateImagePath($imagePath);
    }

    public function id(): PictogramId
    {
        return $this->id;
    }

    public function arasaacId(): ArasaacId
    {
        return $this->arasaacId;
    }

    public function categoryId(): CategoryId
    {
        return $this->categoryId;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function imagePath(): string
    {
        return $this->imagePath;
    }

    private function validateLabel(string $label): void
    {
        if ($label === '') {
            throw new InvalidArgumentException('Label cannot be empty');
        }

        if (strlen($label) > self::MAX_LABEL_LENGTH) {
            throw new InvalidArgumentException('Label cannot exceed 100 characters');
        }
    }

    private function validateImagePath(string $imagePath): void
    {
        if ($imagePath === '') {
            throw new InvalidArgumentException('Image path cannot be empty');
        }

        if (!str_starts_with($imagePath, '/')) {
            throw new InvalidArgumentException('Invalid image path');
        }

        if (str_contains($imagePath, '..')) {
            throw new InvalidArgumentException('Invalid image path');
        }
    }
}
