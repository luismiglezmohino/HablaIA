<?php

declare(strict_types=1);

use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Infrastructure\Persistence\Doctrine\Type\ArasaacIdType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

describe('ArasaacIdType', function (): void {

    beforeEach(function (): void {
        $this->type = new ArasaacIdType();
        $this->platform = new PostgreSQLPlatform();
    });

    it('has correct type name', function (): void {
        expect($this->type->getName())->toBe('arasaac_id');
    });

    it('declares SQL as INTEGER type', function (): void {
        $sql = $this->type->getSQLDeclaration([], $this->platform);

        expect($sql)->toBe('INTEGER');
    });

    it('converts ArasaacId to database value', function (): void {
        $arasaacId = new ArasaacId(12345);

        $result = $this->type->convertToDatabaseValue($arasaacId, $this->platform);

        expect($result)->toBe(12345);
    });

    it('converts null to null for database', function (): void {
        $result = $this->type->convertToDatabaseValue(null, $this->platform);

        expect($result)->toBeNull();
    });

    it('converts database value to ArasaacId', function (): void {
        $result = $this->type->convertToPHPValue(12345, $this->platform);

        expect($result)->toBeInstanceOf(ArasaacId::class);
        expect($result->value())->toBe(12345);
    });

    it('converts string number from database to ArasaacId', function (): void {
        $result = $this->type->convertToPHPValue('12345', $this->platform);

        expect($result)->toBeInstanceOf(ArasaacId::class);
        expect($result->value())->toBe(12345);
    });

    it('converts null from database to null', function (): void {
        $result = $this->type->convertToPHPValue(null, $this->platform);

        expect($result)->toBeNull();
    });

    it('requires SQL comment hint', function (): void {
        expect($this->type->requiresSQLCommentHint($this->platform))->toBeTrue();
    });

    it('throws exception when converting invalid type to database', function (): void {
        $this->type->convertToDatabaseValue('not-an-arasaac-id', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when converting array to database', function (): void {
        $this->type->convertToDatabaseValue(['invalid'], $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is zero', function (): void {
        $this->type->convertToPHPValue(0, $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is negative', function (): void {
        $this->type->convertToPHPValue(-1, $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is non-numeric string', function (): void {
        $this->type->convertToPHPValue('not-a-number', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('throws exception when database value is empty string', function (): void {
        $this->type->convertToPHPValue('', $this->platform);
    })->throws(InvalidArgumentException::class);

    it('returns same ArasaacId if already ArasaacId from database (idempotent)', function (): void {
        $arasaacId = new ArasaacId(12345);

        $result = $this->type->convertToPHPValue($arasaacId, $this->platform);

        expect($result)->toBeInstanceOf(ArasaacId::class);
        expect($result->value())->toBe(12345);
    });

    it('preserves exact value without transformation', function (): void {
        $arasaacId = new ArasaacId(99999);

        $dbValue = $this->type->convertToDatabaseValue($arasaacId, $this->platform);
        $phpValue = $this->type->convertToPHPValue($dbValue, $this->platform);

        expect($phpValue->value())->toBe(99999);
        expect($phpValue->equals($arasaacId))->toBeTrue();
    });

    it('handles large ARASAAC IDs correctly', function (): void {
        $largeId = 999999;
        $arasaacId = new ArasaacId($largeId);

        $dbValue = $this->type->convertToDatabaseValue($arasaacId, $this->platform);
        $phpValue = $this->type->convertToPHPValue($dbValue, $this->platform);

        expect($dbValue)->toBe($largeId);
        expect($phpValue->value())->toBe($largeId);
    });

    it('handles minimum valid ARASAAC ID (1)', function (): void {
        $arasaacId = new ArasaacId(1);

        $dbValue = $this->type->convertToDatabaseValue($arasaacId, $this->platform);
        $phpValue = $this->type->convertToPHPValue($dbValue, $this->platform);

        expect($dbValue)->toBe(1);
        expect($phpValue->value())->toBe(1);
    });

});
