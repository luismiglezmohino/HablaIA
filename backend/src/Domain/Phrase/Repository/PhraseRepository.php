<?php

declare(strict_types=1);

namespace App\Domain\Phrase\Repository;

use App\Domain\Phrase\Entity\Phrase;
use App\Domain\Phrase\ValueObject\PhraseId;

interface PhraseRepository
{
    public function findById(PhraseId $id): ?Phrase;

    /**
     * Find cached phrase by pictogram sequence hash.
     */
    public function findBySequenceHash(string $hash): ?Phrase;

    public function save(Phrase $phrase): void;
}
