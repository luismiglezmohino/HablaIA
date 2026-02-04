<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Arasaac\Exception;

use RuntimeException;

final class ArasaacApiException extends RuntimeException
{
    public static function apiError(string $message, int $statusCode): self
    {
        return new self(sprintf('ARASAAC API error (%d): %s', $statusCode, $message));
    }

    public static function connectionError(string $message): self
    {
        return new self(sprintf('Connection error to ARASAAC API: %s', $message));
    }

    public static function invalidResponse(string $reason): self
    {
        return new self(sprintf('Invalid response from ARASAAC API: %s', $reason));
    }
}
