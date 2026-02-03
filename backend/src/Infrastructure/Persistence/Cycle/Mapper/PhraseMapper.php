<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Mapper;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PhraseEntity;

final class PhraseMapper
{
    public static function toDomain(PhraseEntity $entity): Phrase
    {
        $pictogramIds = array_map(
            fn (string $id) => PictogramId::fromString($id),
            $entity->pictogramIds
        );

        return new Phrase(
            PhraseId::fromString($entity->id),
            new PictogramSequence($pictogramIds),
            $entity->variations,
            $entity->createdAt
        );
    }

    public static function toEntity(Phrase $domain): PhraseEntity
    {
        $entity = new PhraseEntity();
        $entity->id = $domain->id()->value();
        $entity->sequenceHash = $domain->pictogramSequence()->hash();
        $entity->pictogramIds = array_map(
            fn (PictogramId $id) => $id->value(),
            $domain->pictogramSequence()->pictogramIds()
        );
        $entity->variations = $domain->variations();
        $entity->createdAt = $domain->createdAt();

        return $entity;
    }
}
