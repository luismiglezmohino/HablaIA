<?php

declare(strict_types=1);

use App\Domain\Phrase\Exception\InvalidPictogramSequenceException;
use App\Domain\Shared\Exception\DomainException;

test('InvalidPictogramSequenceException extends DomainException', function (): void {
    $exception = InvalidPictogramSequenceException::tooMany(15, 10);

    expect($exception)->toBeInstanceOf(DomainException::class);
});

test('InvalidPictogramSequenceException::tooMany creates exception with correct message', function (): void {
    $exception = InvalidPictogramSequenceException::tooMany(15, 10);

    expect($exception->getMessage())->toBe('Too many pictograms in sequence: 15 (max: 10)');
    expect($exception->getActualCount())->toBe(15);
    expect($exception->getMaxCount())->toBe(10);
});

test('InvalidPictogramSequenceException::empty creates exception with correct message', function (): void {
    $exception = InvalidPictogramSequenceException::empty();

    expect($exception->getMessage())->toBe('Pictogram sequence cannot be empty');
});
