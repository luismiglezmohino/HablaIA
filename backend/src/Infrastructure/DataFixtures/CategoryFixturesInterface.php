<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

interface CategoryFixturesInterface
{
    /**
     * Load SAAC categories into the repository.
     *
     * @return int Number of categories loaded
     */
    public function load(): int;
}
