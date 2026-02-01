<?php

declare(strict_types=1);

use App\Domain\Shared\ValueObject\Uuid;

describe('Uuid Value Object', function (): void {
    it('validates correct UUID v4 format', function (): void {
        expect(Uuid::isValid('550e8400-e29b-41d4-a716-446655440000'))->toBeTrue();
        expect(Uuid::isValid('6ba7b810-9dad-41d4-80b4-00c04fd430c8'))->toBeTrue();
    });

    it('rejects invalid UUID formats', function (): void {
        expect(Uuid::isValid('invalid-uuid'))->toBeFalse();
        expect(Uuid::isValid(''))->toBeFalse();
        expect(Uuid::isValid('550e8400-e29b-41d4-a716'))->toBeFalse();
        expect(Uuid::isValid('550e8400-e29b-41d4-a716-446655440000-extra'))->toBeFalse();
    });

    it('rejects UUID v1 format (wrong version)', function (): void {
        // UUID v1 has version 1 in position 14 (should be 4 for v4)
        expect(Uuid::isValid('550e8400-e29b-11d4-a716-446655440000'))->toBeFalse();
    });

    it('rejects UUID with wrong variant', function (): void {
        // Variant must be 8, 9, a, or b in position 19
        expect(Uuid::isValid('550e8400-e29b-41d4-0716-446655440000'))->toBeFalse();
        expect(Uuid::isValid('550e8400-e29b-41d4-c716-446655440000'))->toBeFalse();
    });

    it('normalizes uppercase to lowercase', function (): void {
        $normalized = Uuid::normalize('550E8400-E29B-41D4-A716-446655440000');

        expect($normalized)->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    it('accepts uppercase UUIDs as valid', function (): void {
        expect(Uuid::isValid('550E8400-E29B-41D4-A716-446655440000'))->toBeTrue();
    });
});
