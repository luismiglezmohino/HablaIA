<?php

declare(strict_types=1);

use App\Application\DTO\PhraseResponseDTO;
use App\Application\Exception\PictogramNotFoundException;
use App\Application\Phrase\GenerateHumanizedPhrase;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Exception\InvalidPictogramSequenceException;
use App\Domain\Phrase\ValueObject\PhraseId;
use App\Domain\Phrase\ValueObject\PictogramSequence;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use Tests\Shared\FakePhraseGenerator;
use Tests\Shared\FakeUuidGenerator;
use Tests\Shared\InMemoryPhraseRepository;
use Tests\Shared\InMemoryPictogramRepository;

beforeEach(function (): void {
    $this->pictogramRepository = new InMemoryPictogramRepository();
    $this->phraseRepository = new InMemoryPhraseRepository();
    $this->phraseGenerator = new FakePhraseGenerator();
    $this->uuidGenerator = new FakeUuidGenerator();

    $this->useCase = new GenerateHumanizedPhrase(
        $this->pictogramRepository,
        $this->phraseRepository,
        $this->phraseGenerator,
        $this->uuidGenerator
    );

    // Categoría común para los pictogramas de test
    $this->categoryId = $this->uuidGenerator->generate();
});

/**
 * Helper para crear un pictograma y guardarlo en el repositorio.
 */
function createPictogram(object $context, string $label): Pictogram
{
    $pictogram = new Pictogram(
        PictogramId::fromString($context->uuidGenerator->generate()),
        new ArasaacId(rand(1, 99999)),
        CategoryId::fromString($context->categoryId),
        $label,
        "/pictograms/{$label}.png"
    );
    $context->pictogramRepository->save($pictogram);

    return $pictogram;
}

describe('GenerateHumanizedPhrase', function (): void {

    it('returns cached phrase when cache hit', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'comer');
        $pictogram2 = createPictogram($this, 'pan');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
        ];

        // Crear frase cacheada
        $sequence = new PictogramSequence([
            $pictogram1->id(),
            $pictogram2->id(),
        ]);
        $cachedPhrase = new Phrase(
            PhraseId::fromString($this->uuidGenerator->generate()),
            $sequence,
            ['Quiero comer pan', 'Me gustaría comer pan'],
            new DateTimeImmutable()
        );
        $this->phraseRepository->save($cachedPhrase);

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result)->toBeInstanceOf(PhraseResponseDTO::class);
        expect($result->source)->toBe(PhraseResponseDTO::SOURCE_CACHE);
        expect($result->variations)->toBe(['Quiero comer pan', 'Me gustaría comer pan']);
    });

    it('generates new phrase on cache miss', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'beber');
        $pictogram2 = createPictogram($this, 'agua');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
        ];

        $this->phraseGenerator->setVariations([
            'Quiero beber agua',
            'Necesito beber agua',
        ]);

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result->source)->toBe(PhraseResponseDTO::SOURCE_GENERATED);
        expect($result->variations)->toBe(['Quiero beber agua', 'Necesito beber agua']);
    });

    it('saves generated phrase to cache', function (): void {
        // Arrange
        $pictogram = createPictogram($this, 'jugar');
        $pictogramIds = [$pictogram->id()->value()];

        // Act
        ($this->useCase)($pictogramIds);

        // Assert - verificar que se guardó en caché
        $sequence = new PictogramSequence([$pictogram->id()]);
        $cached = $this->phraseRepository->findBySequenceHash($sequence->hash());
        expect($cached)->not->toBeNull();
    });

    it('returns cached phrase on second call with same sequence', function (): void {
        // Arrange
        $pictogram = createPictogram($this, 'dormir');
        $pictogramIds = [$pictogram->id()->value()];

        // Primera llamada - genera
        $result1 = ($this->useCase)($pictogramIds);
        expect($result1->source)->toBe(PhraseResponseDTO::SOURCE_GENERATED);

        // Segunda llamada - debería usar caché
        $result2 = ($this->useCase)($pictogramIds);

        // Assert
        expect($result2->source)->toBe(PhraseResponseDTO::SOURCE_CACHE);
        expect($result2->sequenceHash)->toBe($result1->sequenceHash);
    });

    it('throws exception when pictogram does not exist', function (): void {
        // Arrange
        $nonExistentId = $this->uuidGenerator->generate();

        // Act & Assert
        ($this->useCase)([$nonExistentId]);
    })->throws(PictogramNotFoundException::class);

    it('throws exception with invalid UUID format', function (): void {
        ($this->useCase)(['invalid-uuid']);
    })->throws(InvalidArgumentException::class);

    it('throws exception when pictogram list is empty', function (): void {
        ($this->useCase)([]);
    })->throws(InvalidPictogramSequenceException::class);

    it('returns fallback when generator fails', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'comer');
        $pictogram2 = createPictogram($this, 'manzana');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
        ];

        $this->phraseGenerator->willFail();

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result->source)->toBe(PhraseResponseDTO::SOURCE_FALLBACK);
        expect($result->variations)->toContain('comer manzana');
    });

    it('handles single pictogram (minimum)', function (): void {
        // Arrange
        $pictogram = createPictogram($this, 'ayuda');
        $pictogramIds = [$pictogram->id()->value()];

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result)->toBeInstanceOf(PhraseResponseDTO::class);
        expect($result->pictogramIds)->toHaveCount(1);
    });

    it('handles ten pictograms (maximum)', function (): void {
        // Arrange
        $pictogramIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $pictogram = createPictogram($this, "pictograma-{$i}");
            $pictogramIds[] = $pictogram->id()->value();
        }

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result)->toBeInstanceOf(PhraseResponseDTO::class);
        expect($result->pictogramIds)->toHaveCount(10);
    });

    it('throws exception when exceeding ten pictograms', function (): void {
        // Arrange
        $pictogramIds = [];
        for ($i = 1; $i <= 11; $i++) {
            $pictogram = createPictogram($this, "pictograma-{$i}");
            $pictogramIds[] = $pictogram->id()->value();
        }

        // Act & Assert
        ($this->useCase)($pictogramIds);
    })->throws(InvalidPictogramSequenceException::class);

    it('maps DTO fields correctly', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'yo');
        $pictogram2 = createPictogram($this, 'querer');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
        ];

        $expectedVariations = ['Yo quiero', 'Quiero algo'];
        $this->phraseGenerator->setVariations($expectedVariations);

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result->variations)->toBe($expectedVariations);
        expect($result->source)->toBe(PhraseResponseDTO::SOURCE_GENERATED);
        expect($result->sequenceHash)->toBeString();
        expect(strlen($result->sequenceHash))->toBe(64); // SHA256
        expect($result->pictogramIds)->toBe($pictogramIds);
    });

    it('generates consistent hash for same sequence', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'hola');
        $pictogram2 = createPictogram($this, 'amigo');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
        ];

        // Act
        $result1 = ($this->useCase)($pictogramIds);
        $result2 = ($this->useCase)($pictogramIds);

        // Assert
        expect($result1->sequenceHash)->toBe($result2->sequenceHash);
    });

    it('concatenates labels in fallback', function (): void {
        // Arrange
        $pictogram1 = createPictogram($this, 'querer');
        $pictogram2 = createPictogram($this, 'jugar');
        $pictogram3 = createPictogram($this, 'pelota');
        $pictogramIds = [
            $pictogram1->id()->value(),
            $pictogram2->id()->value(),
            $pictogram3->id()->value(),
        ];

        $this->phraseGenerator->willFail();

        // Act
        $result = ($this->useCase)($pictogramIds);

        // Assert
        expect($result->source)->toBe(PhraseResponseDTO::SOURCE_FALLBACK);
        expect($result->variations[0])->toBe('querer jugar pelota');
    });

});
