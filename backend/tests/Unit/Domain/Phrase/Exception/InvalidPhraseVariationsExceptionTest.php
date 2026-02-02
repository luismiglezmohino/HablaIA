<?php

declare(strict_types=1);

use App\Domain\Phrase\Exception\InvalidPhraseVariationsException;
use App\Domain\Shared\Exception\DomainException;

test('InvalidPhraseVariationsException extends DomainException', function (): void {
    $exception = InvalidPhraseVariationsException::tooMany(5, 3);

    expect($exception)->toBeInstanceOf(DomainException::class);
});

test('InvalidPhraseVariationsException::tooMany creates exception with correct message', function (): void {
    $exception = InvalidPhraseVariationsException::tooMany(5, 3);

    expect($exception->getMessage())->toBe('Too many phrase variations: 5 (max: 3)');
    expect($exception->getActualCount())->toBe(5);
    expect($exception->getMaxCount())->toBe(3);
});

test('InvalidPhraseVariationsException::empty creates exception with correct message', function (): void {
    $exception = InvalidPhraseVariationsException::empty();

    expect($exception->getMessage())->toBe('Phrase must have at least one variation');
});

test('InvalidPhraseVariationsException::variationTooLong creates exception with correct message', function (): void {
    $exception = InvalidPhraseVariationsException::variationTooLong(0, 600, 500);

    expect($exception->getMessage())->toBe('Phrase variation at index 0 is too long: 600 characters (max: 500)');
    expect($exception->getIndex())->toBe(0);
    expect($exception->getActualLength())->toBe(600);
    expect($exception->getMaxLength())->toBe(500);
});
