<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity;

final class PictogramMapper
{
    public static function toDomain(PictogramEntity $entity): Pictogram
    {
        return new Pictogram(
            PictogramId::fromString($entity->id),
            new ArasaacId($entity->arasaacId),
            CategoryId::fromString($entity->categoryId),
            $entity->label,
            $entity->imagePath
        );
    }

    public static function toEntity(Pictogram $domain): PictogramEntity
    {
        $entity = new PictogramEntity();
        $entity->id = $domain->id()->value();
        $entity->arasaacId = $domain->arasaacId()->value();
        $entity->categoryId = $domain->categoryId()->value();
        $entity->label = $domain->label();
        $entity->imagePath = $domain->imagePath();

        return $entity;
    }
}
