<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\PictogramMapper;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

final class CyclePictogramRepository implements PictogramRepository
{
    public function __construct(
        private readonly Repository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(PictogramId $id): ?Pictogram
    {
        $entity = $this->repository->findByPK($id->value());

        if ($entity === null) {
            return null;
        }

        return PictogramMapper::toDomain($entity);
    }

    /**
     * @return array<Pictogram>
     */
    public function findByCategoryId(CategoryId $categoryId): array
    {
        /** @var array<PictogramEntity> $entities */
        $entities = $this->repository->findAll(['categoryId' => $categoryId->value()]);

        return array_map(
            fn (PictogramEntity $entity) => PictogramMapper::toDomain($entity),
            $entities
        );
    }

    /**
     * @return array<Pictogram>
     */
    public function findAll(): array
    {
        $entities = $this->repository->findAll();

        return array_map(
            fn (PictogramEntity $entity) => PictogramMapper::toDomain($entity),
            $entities
        );
    }

    public function save(Pictogram $pictogram): void
    {
        $entity = PictogramMapper::toEntity($pictogram);
        $this->entityManager->persist($entity);
        $this->entityManager->run();
    }
}
