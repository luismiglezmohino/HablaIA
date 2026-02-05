<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeImmutable;

#[Entity(table: 'phrases')]
class PhraseEntity
{
    #[Column(type: 'uuid', primary: true)]
    public string $id = '';

    #[Column(type: 'string(64)')]
    public string $sequenceHash = '';

    /** @var array<string> */
    #[Column(type: 'json', typecast: 'json')]
    public array $pictogramIds = [];

    /** @var array<string> */
    #[Column(type: 'json', typecast: 'json')]
    public array $variations = [];

    #[Column(type: 'datetime')]
    public ?DateTimeImmutable $createdAt = null;
}
