<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Exception\PictogramNotFoundException;
use App\Application\Phrase\GenerateHumanizedPhrase;
use App\Domain\Phrase\Exception\InvalidPictogramSequenceException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for phrase generation endpoints.
 * Rate limited to protect OpenAI API costs.
 */
#[Route('/api/phrases', name: 'api_phrases_')]
final class PhraseController
{
    public function __construct(
        private readonly GenerateHumanizedPhrase $generateHumanizedPhrase,
        private readonly RateLimiterFactory $phraseGeneratorLimiter
    ) {
    }

    /**
     * Generate humanized phrase variations from pictogram sequence.
     *
     * Request body: {"pictogramIds": ["uuid1", "uuid2", ...]}
     * Response: {"variations": [...], "source": "generated|cache|fallback", ...}
     */
    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        // 1. Rate limiting by client IP
        $limiter = $this->phraseGeneratorLimiter->create($request->getClientIp() ?? 'anonymous');
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            return new JsonResponse(
                ['error' => 'Too many requests', 'retryAfter' => $limit->getRetryAfter()->getTimestamp()],
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Retry-After' => $limit->getRetryAfter()->getTimestamp()]
            );
        }

        // 2. Parse and validate JSON body
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['pictogramIds'])) {
            return new JsonResponse(['error' => 'Missing required field: pictogramIds'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($data['pictogramIds'])) {
            return new JsonResponse(['error' => 'pictogramIds must be an array'], Response::HTTP_BAD_REQUEST);
        }

        // 3. Execute use case
        try {
            $response = ($this->generateHumanizedPhrase)($data['pictogramIds']);
        } catch (InvalidPictogramSequenceException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (PictogramNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException) {
            return new JsonResponse(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        // 4. Return successful response
        return new JsonResponse([
            'variations' => $response->variations,
            'source' => $response->source,
            'sequenceHash' => $response->sequenceHash,
            'pictogramIds' => $response->pictogramIds,
        ], Response::HTTP_OK);
    }
}
