<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Secure image downloader with SSRF protection, path traversal prevention,
 * and MIME type validation.
 *
 * Security features:
 * - SSRF Protection: Only allows downloads from ARASAAC domains
 * - Path Traversal Prevention: Validates target path is within base directory
 * - MIME Type Validation: Ensures downloaded content is a valid image
 * - Secure Directory Permissions: Creates directories with 0755 (not 0777)
 */
final readonly class HttpImageDownloader implements ImageDownloaderInterface
{
    private const array ALLOWED_HOSTS = [
        'static.arasaac.org',
        'api.arasaac.org',
    ];

    private const array ALLOWED_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/gif',
    ];

    private const int DIRECTORY_PERMISSIONS = 0755;
    private const int HTTP_OK = 200;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseDirectory
    ) {
    }

    public function download(string $url, string $targetPath): bool
    {
        try {
            if (!$this->isAllowedUrl($url)) {
                return false;
            }

            if (!$this->isPathWithinBaseDirectory($targetPath)) {
                return false;
            }

            $response = $this->httpClient->request('GET', $url);

            if ($response->getStatusCode() !== self::HTTP_OK) {
                return false;
            }

            $content = $response->getContent();

            if (!$this->isValidImage($content)) {
                return false;
            }

            $this->ensureDirectoryExists(dirname($targetPath));

            return file_put_contents($targetPath, $content) !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Validates that the URL host is in the allowed whitelist.
     * Prevents SSRF attacks by only allowing ARASAAC domains.
     */
    private function isAllowedUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host !== null && in_array($host, self::ALLOWED_HOSTS, true);
    }

    /**
     * Validates that the target path is within the allowed base directory.
     * Prevents path traversal attacks (e.g., ../../../etc/passwd).
     */
    private function isPathWithinBaseDirectory(string $targetPath): bool
    {
        $normalizedBase = $this->normalizePath($this->baseDirectory);
        $normalizedTarget = $this->normalizePath($targetPath);

        return str_starts_with($normalizedTarget, $normalizedBase);
    }

    /**
     * Normalizes a path by resolving symlinks and relative components.
     * Handles both existing and non-existing paths correctly.
     */
    private function normalizePath(string $path): string
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            return $realPath;
        }

        return $this->resolveNonExistentPath($path);
    }

    /**
     * Resolves a path that doesn't exist by finding the closest
     * existing ancestor and building from there.
     */
    private function resolveNonExistentPath(string $path): string
    {
        $pathParts = [];
        $currentPath = $path;

        while ($currentPath !== '/' && $currentPath !== '') {
            $realCurrent = realpath($currentPath);
            if ($realCurrent !== false) {
                return $realCurrent . '/' . implode('/', array_reverse($pathParts));
            }
            $pathParts[] = basename($currentPath);
            $currentPath = dirname($currentPath);
        }

        return $this->manuallyNormalizePath($path);
    }

    /**
     * Manually normalizes a path when no existing ancestor is found.
     * Resolves relative paths and removes . and .. components.
     */
    private function manuallyNormalizePath(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            $path = getcwd() . '/' . $path;
        }

        $segments = explode('/', $path);
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($normalized);
            } else {
                $normalized[] = $segment;
            }
        }

        return '/' . implode('/', $normalized);
    }

    /**
     * Validates that the content is a valid image by checking its MIME type.
     * Prevents uploading malicious files disguised as images.
     */
    private function isValidImage(string $content): bool
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }

    /**
     * Creates the directory with secure permissions if it doesn't exist.
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, self::DIRECTORY_PERMISSIONS, true);
        }
    }
}
