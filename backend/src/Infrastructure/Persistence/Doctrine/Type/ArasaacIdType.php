<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Pictogram\ValueObject\ArasaacId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * Doctrine type: ArasaacId (PHP) ↔ INTEGER (PostgreSQL).
 */
final class ArasaacIdType extends Type
{
    private const string NAME = 'arasaac_id';

    /**
     * SQL type declaration.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'INTEGER';
    }

    /**
     * DB → PHP: Convierte integer a ArasaacId.
     *
     * @note Idempotente: si $value ya es ArasaacId, se retorna sin modificar.
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ArasaacId
    {
        if ($value === null) {
            return null;
        }

        // Idempotencia: si ya es ArasaacId, retornarlo
        if ($value instanceof ArasaacId) {
            return $value;
        }

        // PostgreSQL puede devolver string numérico
        if (is_string($value)) {
            if ($value === '' || !is_numeric($value)) {
                throw new InvalidArgumentException('Expected numeric value for ArasaacId');
            }
            $value = (int) $value;
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException('Expected integer value for ArasaacId');
        }

        // ArasaacId constructor valida que sea positivo
        return new ArasaacId($value);
    }

    /**
     * PHP → DB: Convierte ArasaacId a integer.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ArasaacId) {
            throw new InvalidArgumentException('Expected ArasaacId instance');
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
