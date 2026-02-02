<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Entity;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Exception\InvalidImagePathException;
use App\Domain\Pictogram\Exception\InvalidPictogramLabelException;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;

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
            throw InvalidPictogramLabelException::empty();
        }

        $length = strlen($label);
        if ($length > self::MAX_LABEL_LENGTH) {
            throw InvalidPictogramLabelException::tooLong($length, self::MAX_LABEL_LENGTH);
        }
    }

    private function validateImagePath(string $imagePath): void
    {
        if ($imagePath === '') {
            throw InvalidImagePathException::empty();
        }

        if (str_contains($imagePath, '..')) {
            throw InvalidImagePathException::pathTraversalDetected($imagePath);
        }
    }
}
