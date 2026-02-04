<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class YamlVocabularyLoader implements VocabularyLoaderInterface
{
    public function __construct(
        private string $vocabularyFilePath
    ) {
    }

    /**
     * @return array<string, array<string>>
     */
    public function load(): array
    {
        if (!file_exists($this->vocabularyFilePath)) {
            throw new \RuntimeException(sprintf(
                'Vocabulary file not found: %s',
                $this->vocabularyFilePath
            ));
        }

        $content = file_get_contents($this->vocabularyFilePath);

        if ($content === false || $content === '') {
            return [];
        }

        try {
            $parsed = Yaml::parse($content);
        } catch (ParseException $e) {
            throw new \RuntimeException(sprintf(
                'Failed to parse YAML vocabulary file: %s',
                $e->getMessage()
            ), 0, $e);
        }

        if (!is_array($parsed)) {
            return [];
        }

        // Filter to only include valid category => keywords mappings
        return array_filter($parsed, static fn (mixed $value): bool => is_array($value));
    }
}
