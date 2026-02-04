<?php

declare(strict_types=1);

use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Shared\SymfonyUuidGenerator;

describe('SymfonyUuidGenerator', function (): void {

    it('implements UuidGeneratorInterface', function (): void {
        $generator = new SymfonyUuidGenerator();

        expect($generator)->toBeInstanceOf(UuidGeneratorInterface::class);
    });

    it('generates valid UUID v4 format', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        // UUID v4 format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        expect($uuid)->toMatch($pattern);
    });

    it('generates unique UUIDs', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid1 = $generator->generate();
        $uuid2 = $generator->generate();
        $uuid3 = $generator->generate();

        expect($uuid1)->not->toBe($uuid2);
        expect($uuid2)->not->toBe($uuid3);
        expect($uuid1)->not->toBe($uuid3);
    });

    it('generates lowercase UUIDs', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        expect($uuid)->toBe(strtolower($uuid));
    });

    it('generates UUID with exact length of 36 characters', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        expect(strlen($uuid))->toBe(36);
    });

    it('never returns empty string', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        expect($uuid)->not->toBeEmpty();
        expect($uuid)->not->toBe('');
    });

    it('contains only valid characters (hexadecimal and hyphens)', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        // Should only contain 0-9, a-f, and hyphens
        expect($uuid)->toMatch('/^[0-9a-f-]+$/');
    });

    it('has hyphens in correct positions', function (): void {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        // Hyphens at positions 8, 13, 18, 23 (0-indexed)
        expect($uuid[8])->toBe('-');
        expect($uuid[13])->toBe('-');
        expect($uuid[18])->toBe('-');
        expect($uuid[23])->toBe('-');
    });

    it('generates 100 unique and valid UUIDs', function (): void {
        $generator = new SymfonyUuidGenerator();
        $uuids = [];
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

        for ($i = 0; $i < 100; $i++) {
            $uuid = $generator->generate();
            $uuids[] = $uuid;

            // Each UUID should be valid
            expect($uuid)->toMatch($pattern);
        }

        // All UUIDs should be unique
        $uniqueUuids = array_unique($uuids);
        expect(count($uniqueUuids))->toBe(100);
    });

});
