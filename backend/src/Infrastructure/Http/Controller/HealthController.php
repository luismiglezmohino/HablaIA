<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Infrastructure\Health\DatabaseHealthCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Health check endpoints for container orchestration (Kubernetes, Docker).
 *
 * - /api/health       → General health status with details
 * - /api/health/live  → Liveness probe (is the app running?)
 * - /api/health/ready → Readiness probe (can the app serve traffic?)
 */
#[Route('/api/health', name: 'api_health_')]
final class HealthController
{
    public function __construct(
        private readonly DatabaseHealthCheckerInterface $databaseHealthChecker
    ) {
    }

    /**
     * General health check with component status.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $dbStatus = $this->databaseHealthChecker->check();

        $isHealthy = $dbStatus['status'] === 'up';

        return new JsonResponse([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'components' => [
                'database' => $dbStatus,
            ],
        ], $isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * Liveness probe - Is the application running?
     *
     * Returns 200 if the PHP process is alive.
     * Used by Kubernetes to know when to restart the container.
     */
    #[Route('/live', name: 'live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'alive',
        ], Response::HTTP_OK);
    }

    /**
     * Readiness probe - Can the application serve traffic?
     *
     * Returns 200 only if all dependencies (DB, etc.) are available.
     * Used by Kubernetes to know when to send traffic to the pod.
     */
    #[Route('/ready', name: 'ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        $dbStatus = $this->databaseHealthChecker->check();

        if ($dbStatus['status'] !== 'up') {
            return new JsonResponse([
                'status' => 'not_ready',
                'reason' => 'Database connection failed',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse([
            'status' => 'ready',
        ], Response::HTTP_OK);
    }
}
