<?php

declare(strict_types=1);

use App\Domain\Shared\Exception\DomainException;

test('DomainException is abstract and extends Exception', function (): void {
    $reflection = new ReflectionClass(DomainException::class);

    expect($reflection->isAbstract())->toBeTrue();
    expect($reflection->getParentClass()->getName())->toBe(Exception::class);
});
