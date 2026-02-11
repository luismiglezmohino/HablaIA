<?php

declare(strict_types=1);

namespace App\Infrastructure\Phrase;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;

final class FakePhraseGenerator implements PhraseGeneratorInterface
{
    private const array TEMPLATES = [
        'Quiero %s',
        'Me gustaría %s',
        'Necesito %s',
    ];

    /**
     * @return array<string>
     */
    public function generate(PictogramSequence $sequence, array $labels): array
    {
        $labelsText = $this->buildLabelsText($labels);

        return array_map(
            fn (string $template) => sprintf($template, $labelsText),
            self::TEMPLATES
        );
    }

    /**
     * @param array<string> $labels
     */
    private function buildLabelsText(array $labels): string
    {
        // Red de seguridad: GenerateHumanizedPhrase siempre extrae labels de pictogramas
        // validados, pero si llegase vacío evitamos frases incompletas ("Quiero ").
        if (empty($labels)) {
            return 'esto';
        }

        return implode(' ', $labels);
    }
}
