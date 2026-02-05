<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Category\GetAllCategories;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories_')]
final class CategoryController
{
    public function __construct(
        private readonly GetAllCategories $getAllCategories,
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $categories = ($this->getAllCategories)();

        return new JsonResponse(
            array_map(fn ($dto) => [
                'id' => $dto->id,
                'name' => $dto->name,
                'icon' => $dto->icon,
                'colorHex' => $dto->colorHex,
                'displayOrder' => $dto->displayOrder,
            ], $categories),
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $categoryId = CategoryId::fromString($id);
        } catch (InvalidArgumentException) {
            return new JsonResponse(
                ['error' => 'Invalid UUID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $category = $this->categoryRepository->findById($categoryId);

        if ($category === null) {
            return new JsonResponse(
                ['error' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse([
            'id' => $category->id()->value(),
            'name' => $category->name(),
            'icon' => $category->icon(),
            'colorHex' => $category->colorHex(),
            'displayOrder' => $category->displayOrder(),
        ], Response::HTTP_OK);
    }
}
