<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Phrase\ValueObject\PhraseId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * Doctrine type: PhraseId (PHP) ↔ UUID (PostgreSQL).
 */
final class PhraseIdType extends Type
{
    private const string NAME = 'phrase_id';

    /**
     * SQL type declaration.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'UUID';
    }

    /**
     * DB → PHP: Convierte string UUID a PhraseId.
     *
     * @note Idempotente: si $value ya es PhraseId, se retorna sin modificar.
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PhraseId
    {
        if ($value === null) {
            return null;
        }

        // Idempotencia: si ya es PhraseId, retornarlo
        if ($value instanceof PhraseId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string value for PhraseId');
        }

        return PhraseId::fromString($value);
    }

    /**
     * PHP → DB: Convierte PhraseId a string UUID.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof PhraseId) {
            throw new InvalidArgumentException('Expected PhraseId instance');
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
