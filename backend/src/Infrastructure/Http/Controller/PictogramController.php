<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Exception\CategoryNotFoundException;
use App\Application\Pictogram\GetAllPictograms;
use App\Application\Pictogram\GetPictogramsByCategory;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\PictogramId;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pictograms', name: 'api_pictograms_')]
final class PictogramController
{
    public function __construct(
        private readonly GetAllPictograms $getAllPictograms,
        private readonly GetPictogramsByCategory $getPictogramsByCategory,
        private readonly PictogramRepository $pictogramRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('categoryId');

        try {
            if ($categoryId !== null) {
                $pictograms = ($this->getPictogramsByCategory)($categoryId);
            } else {
                $pictograms = ($this->getAllPictograms)();
            }
        } catch (CategoryNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (InvalidArgumentException) {
            return new JsonResponse(
                ['error' => 'Invalid UUID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(
            array_map(fn ($dto) => [
                'id' => $dto->id,
                'arasaacId' => $dto->arasaacId,
                'categoryId' => $dto->categoryId,
                'label' => $dto->label,
                'imagePath' => $dto->imagePath,
            ], $pictograms),
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $pictogramId = PictogramId::fromString($id);
        } catch (InvalidArgumentException) {
            return new JsonResponse(
                ['error' => 'Invalid UUID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $pictogram = $this->pictogramRepository->findById($pictogramId);

        if ($pictogram === null) {
            return new JsonResponse(
                ['error' => 'Pictogram not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse([
            'id' => $pictogram->id()->value(),
            'arasaacId' => $pictogram->arasaacId()->value(),
            'categoryId' => $pictogram->categoryId()->value(),
            'label' => $pictogram->label(),
            'imagePath' => $pictogram->imagePath(),
        ], Response::HTTP_OK);
    }
}
