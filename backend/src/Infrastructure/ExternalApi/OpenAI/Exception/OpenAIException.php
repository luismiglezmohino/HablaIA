<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\OpenAI\Exception;

use RuntimeException;

final class OpenAIException extends RuntimeException
{
    public static function apiError(string $message, int $statusCode): self
    {
        return new self(sprintf('OpenAI API error (%d): %s', $statusCode, $message));
    }

    public static function rateLimitExceeded(): self
    {
        return new self('OpenAI API rate limit exceeded. Please try again later.');
    }

    public static function timeout(int $seconds): self
    {
        return new self(sprintf('OpenAI API request timeout after %d seconds', $seconds));
    }

    public static function invalidResponse(string $reason): self
    {
        return new self(sprintf('Invalid response from OpenAI API: %s', $reason));
    }
}
