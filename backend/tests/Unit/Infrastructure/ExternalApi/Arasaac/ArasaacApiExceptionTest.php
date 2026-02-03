<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\Arasaac;

use App\Infrastructure\ExternalApi\Arasaac\Exception\ArasaacApiException;
use RuntimeException;

describe('ArasaacApiException', function (): void {
    it('extends RuntimeException', function (): void {
        $exception = new ArasaacApiException('Error message');

        expect($exception)->toBeInstanceOf(RuntimeException::class);
    });

    it('can be created with API error', function (): void {
        $exception = ArasaacApiException::apiError('Server error', 500);

        expect($exception)->toBeInstanceOf(ArasaacApiException::class);
        expect($exception->getMessage())->toContain('Server error');
        expect($exception->getMessage())->toContain('500');
    });

    it('can be created for connection error', function (): void {
        $exception = ArasaacApiException::connectionError('Could not connect');

        expect($exception)->toBeInstanceOf(ArasaacApiException::class);
        expect($exception->getMessage())->toContain('Connection error');
    });

    it('can be created for invalid response', function (): void {
        $exception = ArasaacApiException::invalidResponse('Missing _id field');

        expect($exception)->toBeInstanceOf(ArasaacApiException::class);
        expect($exception->getMessage())->toContain('Invalid response');
    });
});
