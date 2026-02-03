<?php

declare(strict_types=1);

namespace App\Application\Exception;

/**
 * Exception thrown when one or more pictograms are not found.
 */
final class PictogramNotFoundException extends ApplicationException
{
    /** @var array<string> */
    private readonly array $missingIds;

    /**
     * @param array<string> $missingIds
     */
    private function __construct(string $message, array $missingIds)
    {
        parent::__construct($message);
        $this->missingIds = $missingIds;
    }

    /**
     * @param array<string> $ids
     */
    public static function withIds(array $ids): self
    {
        $idsString = implode(', ', $ids);

        return new self(
            sprintf('Pictograms not found: %s', $idsString),
            $ids
        );
    }

    /**
     * @return array<string>
     */
    public function getMissingIds(): array
    {
        return $this->missingIds;
    }
}
