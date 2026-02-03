<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Pictogram\ValueObject\PictogramId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * Doctrine type: PictogramId (PHP) ↔ UUID (PostgreSQL).
 */
final class PictogramIdType extends Type
{
    private const string NAME = 'pictogram_id';

    /**
     * SQL type declaration.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'UUID';
    }

    /**
     * DB → PHP: Convierte string UUID a PictogramId.
     *
     * @note Idempotente: si $value ya es PictogramId, se retorna sin modificar.
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PictogramId
    {
        if ($value === null) {
            return null;
        }

        // Idempotencia: si ya es PictogramId, retornarlo
        if ($value instanceof PictogramId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string value for PictogramId');
        }

        return PictogramId::fromString($value);
    }

    /**
     * PHP → DB: Convierte PictogramId a string UUID.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof PictogramId) {
            throw new InvalidArgumentException('Expected PictogramId instance');
        }

        return $value->value();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
