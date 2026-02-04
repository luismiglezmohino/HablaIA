<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(table: 'pictograms')]
class PictogramEntity
{
    #[Column(type: 'uuid', primary: true)]
    public string $id = '';

    #[Column(type: 'integer')]
    public int $arasaacId = 0;

    #[Column(type: 'uuid')]
    public string $categoryId = '';

    #[Column(type: 'string(100)')]
    public string $label = '';

    #[Column(type: 'string(255)')]
    public string $imagePath = '';

    #[BelongsTo(target: CategoryEntity::class, innerKey: 'categoryId')]
    public ?CategoryEntity $category = null;
}
