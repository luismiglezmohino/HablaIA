<?php

declare(strict_types=1);

use App\Domain\Pictogram\Exception\InvalidImagePathException;
use App\Domain\Shared\Exception\DomainException;

test('InvalidImagePathException extends DomainException', function (): void {
    $exception = InvalidImagePathException::pathTraversalDetected('../etc/passwd');

    expect($exception)->toBeInstanceOf(DomainException::class);
});

test('InvalidImagePathException::pathTraversalDetected creates exception with correct message', function (): void {
    $exception = InvalidImagePathException::pathTraversalDetected('../etc/passwd');

    expect($exception->getMessage())->toBe('Path traversal detected in image path: ../etc/passwd');
    expect($exception->getPath())->toBe('../etc/passwd');
});

test('InvalidImagePathException::empty creates exception with correct message', function (): void {
    $exception = InvalidImagePathException::empty();

    expect($exception->getMessage())->toBe('Image path cannot be empty');
});
