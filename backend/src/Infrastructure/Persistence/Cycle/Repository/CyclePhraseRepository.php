<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Repository\PhraseRepository;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Infrastructure\Persistence\Cycle\Entity\PhraseEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\PhraseMapper;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

final class CyclePhraseRepository implements PhraseRepository
{
    public function __construct(
        private readonly Repository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(PhraseId $id): ?Phrase
    {
        $entity = $this->repository->findByPK($id->value());

        if ($entity === null) {
            return null;
        }

        return PhraseMapper::toDomain($entity);
    }

    public function findBySequenceHash(string $hash): ?Phrase
    {
        $entity = $this->repository->findOne(['sequenceHash' => $hash]);

        if ($entity === null) {
            return null;
        }

        return PhraseMapper::toDomain($entity);
    }

    public function save(Phrase $phrase): void
    {
        $entity = PhraseMapper::toEntity($phrase);
        $this->entityManager->persist($entity);
        $this->entityManager->run();
    }
}
