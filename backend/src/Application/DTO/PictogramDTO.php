<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Pictogram\Entity\Pictogram;

final readonly class PictogramDTO
{
    public function __construct(
        public string $id,
        public int $arasaacId,
        public string $categoryId,
        public string $label,
        public string $imagePath
    ) {}

    public static function fromEntity(Pictogram $pictogram): self
    {
        return new self(
            id: $pictogram->id()->value(),
            arasaacId: $pictogram->arasaacId()->value(),
            categoryId: $pictogram->categoryId()->value(),
            label: $pictogram->label(),
            imagePath: $pictogram->imagePath()
        );
    }
}
