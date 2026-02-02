<?php

declare(strict_types=1);

namespace App\Application\Phrase;

use App\Application\DTO\PhraseResponseDTO;
use App\Application\Exception\PictogramNotFoundException;
use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Repository\PhraseRepository;
use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use DateTimeImmutable;
use Throwable;

/**
 * Use Case: Genera una frase humanizada a partir de una secuencia de pictogramas.
 *
 * Este es el caso de uso principal del MVP. Transforma una secuencia de
 * pictogramas seleccionados por el usuario en frases naturales usando IA.
 *
 * Algoritmo:
 * 1. Validar que todos los pictogramas existen en BD
 * 2. Construir la secuencia de pictogramas
 * 3. Buscar en caché (por hash de la secuencia)
 * 4. Si no está en caché, llamar al generador (LLM)
 * 5. Si el LLM falla, usar fallback (concatenar labels)
 * 6. Guardar resultado en caché para futuras consultas
 */
final readonly class GenerateHumanizedPhrase
{
    public function __construct(
        private PictogramRepository $pictogramRepository,
        private PhraseRepository $phraseRepository,
        private PhraseGeneratorInterface $phraseGenerator,
        private UuidGeneratorInterface $uuidGenerator
    ) {}

    /**
     * Ejecuta el caso de uso.
     *
     * @param array<string> $pictogramIdStrings Lista de IDs de pictogramas (UUIDs como strings)
     * @return PhraseResponseDTO Respuesta con las variaciones de frase generadas
     *
     * @throws PictogramNotFoundException Si algún pictograma no existe
     * @throws \InvalidArgumentException Si algún ID no es un UUID válido
     * @throws \App\Domain\Phrase\Exception\InvalidPictogramSequenceException Si la lista está vacía o excede 10
     */
    public function __invoke(array $pictogramIdStrings): PhraseResponseDTO
    {
        // Paso 1: Validar que todos los pictogramas existen en la base de datos.
        // Si alguno no existe, lanza PictogramNotFoundException con los IDs faltantes.
        $pictograms = $this->validateAndGetPictograms($pictogramIdStrings);

        // Paso 2: Construir la secuencia de pictogramas.
        // PictogramSequence valida que haya entre 1 y 10 pictogramas.
        $pictogramIds = array_map(
            function (Pictogram $pictogram): PictogramId {
                return $pictogram->id();
            },
            $pictograms
        );
        $sequence = new PictogramSequence($pictogramIds);

        // Paso 3: Buscar en caché usando el hash de la secuencia.
        // El hash es un SHA256 de los IDs concatenados, garantiza unicidad.
        $cachedPhrase = $this->phraseRepository->findBySequenceHash($sequence->hash());
        if ($cachedPhrase !== null) {
            // Cache hit: retornar la frase cacheada sin llamar al LLM.
            return new PhraseResponseDTO(
                $cachedPhrase->variations(),
                PhraseResponseDTO::SOURCE_CACHE,
                $sequence->hash(),
                $pictogramIdStrings
            );
        }

        // Paso 4: Cache miss - intentar generar con el LLM.
        try {
            // Llamar al generador de frases (implementación real usa OpenAI, etc.)
            $variations = $this->phraseGenerator->generate($sequence);
            $source = PhraseResponseDTO::SOURCE_GENERATED;
        } catch (Throwable) {
            // Paso 5: Fallback si el LLM falla (timeout, error de API, etc.)
            // Concatenar los labels de los pictogramas como frase básica.
            // Ejemplo: ["comer", "pan"] → "comer pan"
            $variations = [$this->buildFallbackPhrase($pictograms)];
            $source = PhraseResponseDTO::SOURCE_FALLBACK;
        }

        // Paso 6: Guardar en caché para futuras consultas con la misma secuencia.
        $phrase = new Phrase(
            PhraseId::fromString($this->uuidGenerator->generate()),
            $sequence,
            $variations,
            new DateTimeImmutable()
        );
        $this->phraseRepository->save($phrase);

        // Retornar el DTO con las variaciones, source, hash y los IDs originales.
        return new PhraseResponseDTO(
            $variations,
            $source,
            $sequence->hash(),
            $pictogramIdStrings
        );
    }

    /**
     * Valida que todos los pictogramas existen y los retorna.
     *
     * @param array<string> $pictogramIdStrings Lista de IDs como strings
     * @return array<Pictogram> Lista de entidades Pictogram
     *
     * @throws PictogramNotFoundException Si algún pictograma no existe
     * @throws \InvalidArgumentException Si algún ID no es un UUID válido
     */
    private function validateAndGetPictograms(array $pictogramIdStrings): array
    {
        $pictograms = [];
        $missingIds = [];

        foreach ($pictogramIdStrings as $idString) {
            // PictogramId::fromString valida el formato UUID.
            // Lanza InvalidArgumentException si no es válido.
            $pictogramId = PictogramId::fromString($idString);

            // Buscar el pictograma en el repositorio.
            $pictogram = $this->pictogramRepository->findById($pictogramId);

            if ($pictogram === null) {
                // Acumular IDs no encontrados para reportarlos todos juntos.
                $missingIds[] = $idString;
            } else {
                $pictograms[] = $pictogram;
            }
        }

        // Si hay IDs faltantes, lanzar excepción con todos ellos.
        if (count($missingIds) > 0) {
            throw PictogramNotFoundException::withIds($missingIds);
        }

        return $pictograms;
    }

    /**
     * Construye una frase básica concatenando los labels de los pictogramas.
     *
     * Este es el fallback cuando el LLM no está disponible.
     * Ejemplo: pictogramas ["yo", "querer", "comer"] → "yo querer comer"
     *
     * @param array<Pictogram> $pictograms Lista de pictogramas
     * @return string Frase concatenada
     */
    private function buildFallbackPhrase(array $pictograms): string
    {
        $labels = array_map(
            function (Pictogram $pictogram): string {
                return $pictogram->label();
            },
            $pictograms
        );

        return implode(' ', $labels);
    }
}
