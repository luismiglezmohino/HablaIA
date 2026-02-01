<?php

declare(strict_types=1);

namespace App\Domain\Phrase\ValueObject;

use App\Domain\Pictogram\ValueObject\PictogramId;
use InvalidArgumentException;

final readonly class PictogramSequence
{
    private const int MIN_PICTOGRAMS = 1;
    private const int MAX_PICTOGRAMS = 10;

    /** @var array<PictogramId> */
    private array $pictogramIds;

    /**
     * @param array<PictogramId> $pictogramIds
     */
    public function __construct(array $pictogramIds)
    {
        $this->validate($pictogramIds);
        $this->pictogramIds = $pictogramIds;
    }

    /**
     * @return array<PictogramId>
     */
    public function pictogramIds(): array
    {
        return $this->pictogramIds;
    }

    public function count(): int
    {
        return count($this->pictogramIds);
    }

    public function hash(): string
    {
        $ids = array_map(
            fn (PictogramId $id) => $id->value(),
            $this->pictogramIds
        );

        return hash('sha256', implode('|', $ids));
    }

    /**
     * @param array<PictogramId> $pictogramIds
     */
    private function validate(array $pictogramIds): void
    {
        if (count($pictogramIds) < self::MIN_PICTOGRAMS) {
            throw new InvalidArgumentException('At least one pictogram is required');
        }

        if (count($pictogramIds) > self::MAX_PICTOGRAMS) {
            throw new InvalidArgumentException('Maximum 10 pictograms allowed');
        }
    }
}
