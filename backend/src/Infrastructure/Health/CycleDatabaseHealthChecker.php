<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

use Cycle\Database\DatabaseManager;
use Throwable;

/**
 * Database health checker implementation using Cycle ORM.
 */
final class CycleDatabaseHealthChecker implements DatabaseHealthCheckerInterface
{
    public function __construct(
        private readonly DatabaseManager $databaseManager
    ) {
    }

    public function check(): array
    {
        try {
            $start = microtime(true);

            $this->databaseManager->database()->query('SELECT 1')->fetch();

            $latency = (microtime(true) - $start) * 1000;

            return [
                'status' => 'up',
                'latency_ms' => round($latency, 2),
            ];
        } catch (Throwable) {
            return [
                'status' => 'down',
                'error' => 'Database unavailable',
            ];
        }
    }
}
