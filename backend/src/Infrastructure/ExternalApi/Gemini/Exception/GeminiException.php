<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Gemini\Exception;

use RuntimeException;

final class GeminiException extends RuntimeException
{
    public static function apiError(string $message, int $statusCode): self
    {
        return new self(sprintf('Gemini API error (%d): %s', $statusCode, $message));
    }

    public static function rateLimitExceeded(): self
    {
        return new self('Gemini API rate limit exceeded');
    }

    public static function timeout(): self
    {
        return new self('Gemini API request timed out');
    }

    public static function invalidResponse(string $reason): self
    {
        return new self(sprintf('Invalid Gemini API response: %s', $reason));
    }
}
