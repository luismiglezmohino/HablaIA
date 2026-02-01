<?php

declare(strict_types=1);

namespace App\Domain\Pictogram\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidImagePathException extends DomainException
{
    private function __construct(
        string $message,
        private readonly ?string $path = null
    ) {
        parent::__construct($message);
    }

    public static function pathTraversalDetected(string $path): self
    {
        return new self(
            sprintf('Path traversal detected in image path: %s', $path),
            $path
        );
    }

    public static function empty(): self
    {
        return new self('Image path cannot be empty');
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
