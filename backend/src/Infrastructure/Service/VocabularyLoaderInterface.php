<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

interface VocabularyLoaderInterface
{
    /**
     * Loads vocabulary from configuration source.
     *
     * @return array<string, array<string>> Category name => keywords mapping
     */
    public function load(): array;
}
