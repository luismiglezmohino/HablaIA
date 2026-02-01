<?php

declare(strict_types=1);

use App\Domain\Pictogram\Exception\InvalidPictogramLabelException;
use App\Domain\Shared\Exception\DomainException;

test('InvalidPictogramLabelException extends DomainException', function (): void {
    $exception = InvalidPictogramLabelException::tooLong(150, 100);

    expect($exception)->toBeInstanceOf(DomainException::class);
});

test('InvalidPictogramLabelException::tooLong creates exception with correct message', function (): void {
    $exception = InvalidPictogramLabelException::tooLong(150, 100);

    expect($exception->getMessage())->toBe('Pictogram label is too long: 150 characters (max: 100)');
    expect($exception->getActualLength())->toBe(150);
    expect($exception->getMaxLength())->toBe(100);
});

test('InvalidPictogramLabelException::empty creates exception with correct message', function (): void {
    $exception = InvalidPictogramLabelException::empty();

    expect($exception->getMessage())->toBe('Pictogram label cannot be empty');
});
