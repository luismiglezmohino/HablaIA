<?php

declare(strict_types=1);

use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Domain\Shared\ValueObject\Uuid;
use Tests\Shared\FakeUuidGenerator;

describe('UuidGeneratorInterface', function (): void {
    it('FakeUuidGenerator implements the interface', function (): void {
        $generator = new FakeUuidGenerator();

        expect($generator)->toBeInstanceOf(UuidGeneratorInterface::class);
    });

    it('generates valid UUID v4 format', function (): void {
        $generator = new FakeUuidGenerator();
        $uuid = $generator->generate();

        expect($uuid)->toBeString();
        expect(strlen($uuid))->toBe(36);
        expect(Uuid::isValid($uuid))->toBeTrue();
    });

    it('generates unique UUIDs', function (): void {
        $generator = new FakeUuidGenerator();
        $uuid1 = $generator->generate();
        $uuid2 = $generator->generate();

        expect($uuid1)->not()->toBe($uuid2);
    });
});
