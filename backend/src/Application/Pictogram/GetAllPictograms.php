<?php

declare(strict_types=1);

namespace App\Application\Pictogram;

use App\Application\DTO\PictogramDTO;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;

final readonly class GetAllPictograms
{
    public function __construct(
        private PictogramRepository $repository
    ) {}

    /** @return array<PictogramDTO> */
    public function __invoke(): array
    {
        $pictograms = $this->repository->findAll();

        return array_map(
            function (Pictogram $pictogram): PictogramDTO {
                return PictogramDTO::fromEntity($pictogram);
            },
            $pictograms
        );
    }
}
