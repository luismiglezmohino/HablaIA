<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(table: 'categories')]
class CategoryEntity
{
    #[Column(type: 'uuid', primary: true)]
    public string $id = '';

    #[Column(type: 'string(50)')]
    public string $name = '';

    #[Column(type: 'string(255)', nullable: true)]
    public ?string $icon = null;
}
