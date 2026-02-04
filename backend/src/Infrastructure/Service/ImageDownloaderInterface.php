<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

interface ImageDownloaderInterface
{
    /**
     * Downloads an image from a URL to a target path.
     */
    public function download(string $url, string $targetPath): bool;

    /**
     * Checks if an image already exists at the given path.
     */
    public function exists(string $path): bool;
}
