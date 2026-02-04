<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

/**
 * Interface for checking database health.
 * Allows mocking in tests since Cycle's DatabaseManager is final.
 */
interface DatabaseHealthCheckerInterface
{
    /**
     * Check if the database is reachable and responsive.
     *
     * @return array{status: string, latency_ms?: float, error?: string}
     */
    public function check(): array;
}
