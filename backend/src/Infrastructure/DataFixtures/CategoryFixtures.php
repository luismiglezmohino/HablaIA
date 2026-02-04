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
        ['name' => 'Personas', 'icon' => 'users'],
        ['name' => 'Acciones', 'icon' => 'play'],
        ['name' => 'Emociones', 'icon' => 'heart'],
        ['name' => 'Lugares', 'icon' => 'map-pin'],
        ['name' => 'Objetos', 'icon' => 'box'],
        ['name' => 'Comida', 'icon' => 'utensils'],
        ['name' => 'Transporte', 'icon' => 'car'],
    ];

    public function __construct(
        private UuidGeneratorInterface $uuidGenerator,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array<array{name: string, icon: string}>
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
                $categoryData['icon']
            );

            $this->categoryRepository->save($category);
            $loaded++;
        }

        return $loaded;
    }
}
