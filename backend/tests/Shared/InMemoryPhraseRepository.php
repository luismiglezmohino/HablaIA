<?php

declare(strict_types=1);

namespace Tests\Shared;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\Repository\PhraseRepository;
use App\Domain\Phrase\ValueObject\PhraseId;

/**
 * In-memory implementation of PhraseRepository for testing.
 */
final class InMemoryPhraseRepository implements PhraseRepository
{
    /** @var array<string, Phrase> */
    private array $phrases = [];

    public function findById(PhraseId $id): ?Phrase
    {
        return $this->phrases[$id->value()] ?? null;
    }

    public function findBySequenceHash(string $hash): ?Phrase
    {
        foreach ($this->phrases as $phrase) {
            if ($phrase->sequenceHash() === $hash) {
                return $phrase;
            }
        }

        return null;
    }

    public function save(Phrase $phrase): void
    {
        $this->phrases[$phrase->id()->value()] = $phrase;
    }
}
