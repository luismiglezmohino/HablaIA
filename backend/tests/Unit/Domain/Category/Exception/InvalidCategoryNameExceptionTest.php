<?php

declare(strict_types=1);

use App\Domain\Category\Exception\InvalidCategoryNameException;
use App\Domain\Shared\Exception\DomainException;

test('InvalidCategoryNameException extends DomainException', function (): void {
    $exception = InvalidCategoryNameException::tooLong(75, 50);

    expect($exception)->toBeInstanceOf(DomainException::class);
});

test('InvalidCategoryNameException::tooLong creates exception with correct message', function (): void {
    $exception = InvalidCategoryNameException::tooLong(75, 50);

    expect($exception->getMessage())->toBe('Category name is too long: 75 characters (max: 50)');
    expect($exception->getActualLength())->toBe(75);
    expect($exception->getMaxLength())->toBe(50);
});

test('InvalidCategoryNameException::empty creates exception with correct message', function (): void {
    $exception = InvalidCategoryNameException::empty();

    expect($exception->getMessage())->toBe('Category name cannot be empty');
});
