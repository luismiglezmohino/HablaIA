<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\YamlVocabularyLoader;

describe('YamlVocabularyLoader', function (): void {
    it('loads vocabulary from YAML file', function (): void {
        $yamlContent = <<<'YAML'
Personas:
  - yo
  - tu
  - mama
Acciones:
  - comer
  - beber
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'vocab_');
        file_put_contents($tempFile, $yamlContent);

        try {
            $loader = new YamlVocabularyLoader($tempFile);
            $vocabulary = $loader->load();

            expect($vocabulary)->toBeArray();
            expect($vocabulary)->toHaveKey('Personas');
            expect($vocabulary)->toHaveKey('Acciones');
            expect($vocabulary['Personas'])->toBe(['yo', 'tu', 'mama']);
            expect($vocabulary['Acciones'])->toBe(['comer', 'beber']);
        } finally {
            unlink($tempFile);
        }
    });

    it('throws exception when file does not exist', function (): void {
        $loader = new YamlVocabularyLoader('/nonexistent/file.yaml');

        expect(fn () => $loader->load())->toThrow(\RuntimeException::class);
    });

    it('throws exception when YAML is invalid', function (): void {
        $tempFile = tempnam(sys_get_temp_dir(), 'vocab_');
        file_put_contents($tempFile, "invalid: yaml: content:\n  [broken");

        try {
            $loader = new YamlVocabularyLoader($tempFile);

            expect(fn () => $loader->load())->toThrow(\RuntimeException::class);
        } finally {
            unlink($tempFile);
        }
    });

    it('returns empty array when YAML file is empty', function (): void {
        $tempFile = tempnam(sys_get_temp_dir(), 'vocab_');
        file_put_contents($tempFile, '');

        try {
            $loader = new YamlVocabularyLoader($tempFile);
            $vocabulary = $loader->load();

            expect($vocabulary)->toBe([]);
        } finally {
            unlink($tempFile);
        }
    });

    it('filters out non-array values', function (): void {
        $yamlContent = <<<'YAML'
Personas:
  - yo
  - tu
InvalidCategory: "not an array"
Acciones:
  - comer
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'vocab_');
        file_put_contents($tempFile, $yamlContent);

        try {
            $loader = new YamlVocabularyLoader($tempFile);
            $vocabulary = $loader->load();

            expect($vocabulary)->toHaveKey('Personas');
            expect($vocabulary)->toHaveKey('Acciones');
            expect($vocabulary)->not->toHaveKey('InvalidCategory');
        } finally {
            unlink($tempFile);
        }
    });
});
