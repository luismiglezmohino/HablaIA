<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\OpenAI;

use App\Infrastructure\ExternalApi\OpenAI\Exception\OpenAIException;
use RuntimeException;

describe('OpenAIException', function (): void {
    it('extends RuntimeException', function (): void {
        $exception = new OpenAIException('Error message');

        expect($exception)->toBeInstanceOf(RuntimeException::class);
    });

    it('can be created with API error', function (): void {
        $exception = OpenAIException::apiError('Invalid API key', 401);

        expect($exception)->toBeInstanceOf(OpenAIException::class);
        expect($exception->getMessage())->toContain('Invalid API key');
        expect($exception->getMessage())->toContain('401');
    });

    it('can be created for rate limit', function (): void {
        $exception = OpenAIException::rateLimitExceeded();

        expect($exception)->toBeInstanceOf(OpenAIException::class);
        expect($exception->getMessage())->toContain('rate limit');
    });

    it('can be created for timeout', function (): void {
        $exception = OpenAIException::timeout(7);

        expect($exception)->toBeInstanceOf(OpenAIException::class);
        expect($exception->getMessage())->toContain('timeout');
        expect($exception->getMessage())->toContain('7');
    });

    it('can be created for invalid response', function (): void {
        $exception = OpenAIException::invalidResponse('Missing choices');

        expect($exception)->toBeInstanceOf(OpenAIException::class);
        expect($exception->getMessage())->toContain('Invalid response');
        expect($exception->getMessage())->toContain('Missing choices');
    });
});
