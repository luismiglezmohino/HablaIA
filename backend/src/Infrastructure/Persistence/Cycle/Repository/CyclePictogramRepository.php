<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Repository;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Infrastructure\Persistence\Cycle\Entity\PictogramEntity;
use App\Infrastructure\Persistence\Cycle\Mapper\PictogramMapper;
use Cycle\Database\Injection\Fragment;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select\Repository;

final class CyclePictogramRepository implements PictogramRepository
{
    /**
     * @param Repository<PictogramEntity> $repository
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(PictogramId $id): ?Pictogram
    {
        $entity = $this->repository->findByPK($id->value());

        if (!$entity instanceof PictogramEntity) {
            return null;
        }

        return PictogramMapper::toDomain($entity);
    }

    /**
     * @param array<PictogramId> $ids
     * @return array<Pictogram>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $uuids = array_map(function (PictogramId $id): string {
            return $id->value();
        }, $ids);

        /** @var array<PictogramEntity> $entities */
        $entities = $this->repository
            ->select()
            ->where('id', 'IN', $uuids)
            ->fetchAll();

        return array_map(
            function (PictogramEntity $entity): Pictogram {
                return PictogramMapper::toDomain($entity);
            },
            $entities
        );
    }

    /**
     * @return array<Pictogram>
     */
    public function findByCategoryId(CategoryId $categoryId): array
    {
        /** @var array<PictogramEntity> $entities */
        $entities = iterator_to_array($this->repository->findAll(['categoryId' => $categoryId->value()]));

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
        /** @var array<PictogramEntity> $entities */
        $entities = iterator_to_array($this->repository->findAll());

        return array_map(
            fn (PictogramEntity $entity) => PictogramMapper::toDomain($entity),
            $entities
        );
    }

    /**
     * @return array<Pictogram>
     */
    public function findByLabelLike(string $query, int $limit = 10): array
    {
        $normalizedQuery = mb_strtolower(trim($query));

        /** @var array<PictogramEntity> $entities */
        $entities = $this->repository
            ->select()
            ->where(new Fragment('unaccent(LOWER("label")) LIKE unaccent(?)', "%{$normalizedQuery}%"))
            ->limit($limit)
            ->fetchAll();

        return array_map(
            fn (PictogramEntity $entity) => PictogramMapper::toDomain($entity),
            $entities
        );
    }

    public function findByArasaacId(int $arasaacId): ?Pictogram
    {
        /** @var PictogramEntity|null $entity */
        $entity = $this->repository->findOne(['arasaacId' => $arasaacId]);

        if ($entity === null) {
            return null;
        }

        return PictogramMapper::toDomain($entity);
    }

    public function save(Pictogram $pictogram): void
    {
        $entity = PictogramMapper::toEntity($pictogram);
        $this->entityManager->persist($entity);
        $this->entityManager->run();
    }
}
