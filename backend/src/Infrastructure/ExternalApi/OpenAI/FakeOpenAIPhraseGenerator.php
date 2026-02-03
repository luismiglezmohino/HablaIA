<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\OpenAI;

use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PictogramSequence;

final class FakeOpenAIPhraseGenerator implements PhraseGeneratorInterface
{
    private const array TEMPLATES = [
        // Deseos y necesidades
        'Quiero %s',
        'Me gustaría %s',
        'Necesito %s',
        // Peticiones corteses
        'Por favor, %s',
        '¿Puedo %s?',
        '¿Me ayudas a %s?',
        // Afirmaciones
        'Voy a %s',
        'Estoy %s',
        'Tengo %s',
        // Preguntas
        '¿Dónde está %s?',
        '¿Cuándo %s?',
        '¿Quién tiene %s?',
    ];

    /**
     * @param array<string> $labels
     */
    public function __construct(
        private readonly array $labels = []
    ) {
    }

    /**
     * @return array<string>
     */
    public function generate(PictogramSequence $sequence): array
    {
        $labelsText = $this->buildLabelsText();

        // Seleccionar 3 templates diferentes
        $selectedTemplates = $this->selectTemplates(3);

        return array_map(
            fn (string $template) => sprintf($template, $labelsText),
            $selectedTemplates
        );
    }

    private function buildLabelsText(): string
    {
        if (empty($this->labels)) {
            return 'esto';
        }

        return implode(' ', $this->labels);
    }

    /**
     * @return array<string>
     */
    private function selectTemplates(int $count): array
    {
        // Siempre retornar los primeros 3 para consistencia en tests
        return array_slice(self::TEMPLATES, 0, $count);
    }
}
