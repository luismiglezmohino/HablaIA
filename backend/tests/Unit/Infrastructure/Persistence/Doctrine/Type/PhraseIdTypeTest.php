<?php

declare(strict_types=1);

use App\Domain\Phrase\ValueObject\PhraseId;
use App\Infrastructure\Persistence\Doctrine\Type\PhraseIdType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

describe('PhraseIdType', function (): void {

    beforeEach(function (): void {
        $this->type = new PhraseIdType();
        $this->platform = new PostgreSQLPlatform();
    });

    it('has correct type name', function (): void {
        expect($this->type->getName())->toBe('phrase_id');
    });

    it('declares SQL as UUID type', function (): void {
        $sql = $this->type->getSQLDeclaration([], $this->platform);

        expect($sql)->toBe('UUID');
    });

    it('converts PhraseId to database value', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $phraseId = PhraseId::fromString($uuid);

        $result = $this->type->convertToDatabaseValue($phraseId, $this->platform);

        expect($result)->toBe($uuid);
    });

    it('converts null to null for database', function (): void {
        $result = $this->type->convertToDatabaseValue(null, $this->platform);

        expect($result)->toBeNull();
    });

    it('converts database value to PhraseId', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $result = $this->type->convertToPHPValue($uuid, $this->platform);

        expect($result)->toBeInstanceOf(PhraseId::class);
        expect($result->value())->toBe($uuid);
    });

    it('converts null from database to null', function (): void {
        $result = $this->type->convertToPHPValue(null, $this->platform);

        expect($result)->toBeNull();
    });

    it('requires SQL comment hint', function (): void {
        expect($this->type->requiresSQLCommentHint($this->platform))->toBeTrue();
    });

    it('throws exception when converting invalid type to database', function (): void {
        $this->type->convertToDatabaseValue('not-a-phrase-id', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when converting integer to database', function (): void {
        $this->type->convertToDatabaseValue(12345, $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when converting array to database', function (): void {
        $this->type->convertToDatabaseValue(['invalid'], $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is invalid UUID', function (): void {
        $this->type->convertToPHPValue('not-a-valid-uuid', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is empty string', function (): void {
        $this->type->convertToPHPValue('', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('returns same PhraseId if already PhraseId from database (idempotent)', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $phraseId = PhraseId::fromString($uuid);

        $result = $this->type->convertToPHPValue($phraseId, $this->platform);

        expect($result)->toBeInstanceOf(PhraseId::class);
        expect($result->value())->toBe($uuid);
    });

    it('normalizes uppercase UUID to lowercase', function (): void {
        $uppercaseUuid = '550E8400-E29B-41D4-A716-446655440000';

        $result = $this->type->convertToPHPValue($uppercaseUuid, $this->platform);

        expect($result->value())->toBe(strtolower($uppercaseUuid));
    });

    it('preserves exact value without transformation', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $phraseId = PhraseId::fromString($uuid);

        $dbValue = $this->type->convertToDatabaseValue($phraseId, $this->platform);
        $phpValue = $this->type->convertToPHPValue($dbValue, $this->platform);

        expect($phpValue->value())->toBe($uuid);
        expect($phpValue->equals($phraseId))->toBeTrue();
    });

});
