<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Shared\Service\UuidGeneratorInterface;

final readonly class CategoryFixtures implements CategoryFixturesInterface
{
    private const array SAAC_CATEGORIES = [
        ['name' => 'Personas', 'icon' => 'users', 'colorHex' => '#FBBF24', 'displayOrder' => 1],
        ['name' => 'Acciones', 'icon' => 'play', 'colorHex' => '#22C55E', 'displayOrder' => 2],
        ['name' => 'Emociones', 'icon' => 'heart', 'colorHex' => '#3B82F6', 'displayOrder' => 3],
        ['name' => 'Lugares', 'icon' => 'map-pin', 'colorHex' => '#F97316', 'displayOrder' => 4],
        ['name' => 'Objetos', 'icon' => 'box', 'colorHex' => '#FB923C', 'displayOrder' => 5],
        ['name' => 'Comida', 'icon' => 'utensils', 'colorHex' => '#EA580C', 'displayOrder' => 6],
        ['name' => 'Transporte', 'icon' => 'car', 'colorHex' => '#F59E0B', 'displayOrder' => 7],
        ['name' => 'Social', 'icon' => 'message-circle', 'colorHex' => '#EC4899', 'displayOrder' => 8],
        ['name' => 'Tiempo', 'icon' => 'clock', 'colorHex' => '#8B5CF6', 'displayOrder' => 9],
        ['name' => 'Descriptivos', 'icon' => 'sliders', 'colorHex' => '#14B8A6', 'displayOrder' => 10],
    ];

    public function __construct(
        private UuidGeneratorInterface $uuidGenerator,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array<array{name: string, icon: string, colorHex: string, displayOrder: int}>
     */
    public static function getCategories(): array
    {
        return self::SAAC_CATEGORIES;
    }

    /**
     * Load SAAC categories into the repository.
     *
     * @return int Number of categories loaded
     */
    public function load(): int
    {
        $loaded = 0;

        foreach (self::SAAC_CATEGORIES as $categoryData) {
            $existing = $this->categoryRepository->findByName($categoryData['name']);

            if ($existing !== null) {
                continue;
            }

            $category = new Category(
                CategoryId::fromString($this->uuidGenerator->generate()),
                $categoryData['name'],
                $categoryData['icon'],
                $categoryData['colorHex'],
                $categoryData['displayOrder']
            );

            $this->categoryRepository->save($category);
            $loaded++;
        }

        return $loaded;
    }
}
