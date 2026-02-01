<?php

declare(strict_types=1);

namespace App\Domain\Phrase\Service;

use App\Domain\Phrase\ValueObject\PictogramSequence;

/**
 * Contract for phrase generation services.
 *
 * Implementations may use different LLM providers:
 * - OpenAI (GPT-4o-mini)
 * - Anthropic (Claude)
 * - Google (Gemini)
 * - Local models (LLaMA, Mistral)
 */
interface PhraseGeneratorInterface
{
    /**
     * Generate humanized phrase variations from a pictogram sequence.
     *
     * @return array<string> List of phrase variations (typically 3)
     */
    public function generate(PictogramSequence $sequence): array;
}
