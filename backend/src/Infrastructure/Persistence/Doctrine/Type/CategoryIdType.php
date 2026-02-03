<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Category\ValueObject\CategoryId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * Doctrine type: CategoryId (PHP) ↔ UUID (PostgreSQL).
 */
final class CategoryIdType extends Type
{
    private const string NAME = 'category_id';

    /**
     * SQL type declaration.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'UUID';
    }

    /**
     * DB → PHP: Convierte string UUID a CategoryId.
     *
     * @note Idempotente: si $value ya es CategoryId, se retorna sin modificar.
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CategoryId
    {
        if ($value === null) {
            return null;
        }

        // Idempotencia: si ya es CategoryId, retornarlo
        if ($value instanceof CategoryId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string value for CategoryId');
        }

        return CategoryId::fromString($value);
    }

    /**
     * PHP → DB: Convierte CategoryId a string UUID.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof CategoryId) {
            throw new InvalidArgumentException('Expected CategoryId instance');
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
