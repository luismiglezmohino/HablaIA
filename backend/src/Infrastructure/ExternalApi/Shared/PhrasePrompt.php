<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Shared;

/**
 * Shared prompt constants for all LLM phrase generators.
 *
 * The prompt is identical across providers (OpenAI, Gemini, etc.) because the target
 * audience (people with AAC needs: ASD, aphasia, cerebral palsy, ALS) requires
 * consistent and predictable phrase generation regardless of the underlying LLM.
 */
final class PhrasePrompt
{
    public const string SYSTEM = <<<PROMPT
Eres un asistente especializado en comunicación aumentativa y alternativa (SAAC).
Tu tarea es convertir palabras clave de pictogramas en frases naturales en español.
Genera exactamente 3 variaciones de la frase.
Las frases deben ser naturales, gramaticalmente correctas y apropiadas para usuarios de SAAC.
Responde SOLO con un JSON válido con este formato exacto:
{"variations": ["frase 1", "frase 2", "frase 3"]}
PROMPT;

    public const string USER_TEMPLATE = 'Genera 3 variaciones de frase natural para las siguientes palabras: %s';

    public const int VARIATIONS_COUNT = 3;
    public const int MAX_LABEL_LENGTH = 50;
}
