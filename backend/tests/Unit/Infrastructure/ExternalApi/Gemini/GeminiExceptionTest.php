<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\Gemini;

use App\Infrastructure\ExternalApi\Gemini\Exception\GeminiException;
use RuntimeException;

describe('GeminiException', function (): void {
    it('extends RuntimeException', function (): void {
        $exception = GeminiException::apiError('Test error', 500);
        expect($exception)->toBeInstanceOf(RuntimeException::class);
    });

    it('can be created with API error', function (): void {
        $exception = GeminiException::apiError('Invalid request', 400);
        expect($exception->getMessage())->toBe('Gemini API error (400): Invalid request');
    });

    it('can be created for rate limit', function (): void {
        $exception = GeminiException::rateLimitExceeded();
        expect($exception->getMessage())->toBe('Gemini API rate limit exceeded');
    });

    it('can be created for timeout', function (): void {
        $exception = GeminiException::timeout();
        expect($exception->getMessage())->toBe('Gemini API request timed out');
    });

    it('can be created for invalid response', function (): void {
        $exception = GeminiException::invalidResponse('Missing candidates');
        expect($exception->getMessage())->toBe('Invalid Gemini API response: Missing candidates');
    });
});
