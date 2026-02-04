<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\HttpImageDownloader;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

describe('HttpImageDownloader', function (): void {
    beforeEach(function (): void {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->tempDir = sys_get_temp_dir() . '/test_pictograms_' . uniqid();
        $this->baseDirectory = $this->tempDir;
        mkdir($this->tempDir, 0755, true);
    });

    afterEach(function (): void {
        // Cleanup temp directory
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }
    });

    describe('download', function (): void {
        it('downloads image successfully from allowed domain', function (): void {
            // Valid PNG file header (8 bytes) + minimal IHDR chunk
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;
            $targetPath = $this->baseDirectory . '/12345.png';

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient
                ->method('request')
                ->with('GET', 'https://static.arasaac.org/pictograms/12345/12345_300.png')
                ->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/pictograms/12345/12345_300.png', $targetPath);

            expect($result)->toBeTrue();
            expect(file_exists($targetPath))->toBeTrue();
        });

        it('creates directory if it does not exist', function (): void {
            // Valid PNG file header
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;
            $nestedPath = $this->baseDirectory . '/nested/dir/12345.png';

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient
                ->method('request')
                ->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.png', $nestedPath);

            expect($result)->toBeTrue();
            expect(file_exists($nestedPath))->toBeTrue();

            // Cleanup nested directory
            unlink($nestedPath);
            rmdir($this->baseDirectory . '/nested/dir');
            rmdir($this->baseDirectory . '/nested');
        });

        it('returns false on HTTP error', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(404);

            $this->httpClient
                ->method('request')
                ->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('returns false on exception', function (): void {
            $this->httpClient
                ->method('request')
                ->willThrowException(new \Exception('Network error'));

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });
    });

    describe('SSRF protection - URL whitelist', function (): void {
        it('returns false when URL host is not in whitelist', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            // Malicious URL attempting SSRF attack
            $result = $downloader->download('https://evil.com/malicious.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('returns false for localhost URL (SSRF attempt)', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            $result = $downloader->download('http://localhost/admin', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('returns false for internal IP URL (SSRF attempt)', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            $result = $downloader->download('http://192.168.1.1/internal', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('returns false for URL without host', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            $result = $downloader->download('/etc/passwd', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('allows static.arasaac.org domain', function (): void {
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/pictograms/1/1_300.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeTrue();
        });

        it('allows api.arasaac.org domain', function (): void {
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://api.arasaac.org/api/pictograms/1', $this->baseDirectory . '/test.png');

            expect($result)->toBeTrue();
        });
    });

    describe('Path traversal protection', function (): void {
        it('returns false when path traversal is attempted with ../', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            // Attempt to write outside base directory
            $maliciousPath = $this->baseDirectory . '/../../../etc/passwd';
            $result = $downloader->download('https://static.arasaac.org/image.png', $maliciousPath);

            expect($result)->toBeFalse();
        });

        it('returns false when target path is outside base directory', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            // Attempt to write to /tmp directly (outside base directory)
            $result = $downloader->download('https://static.arasaac.org/image.png', '/tmp/malicious.png');

            expect($result)->toBeFalse();
        });

        it('returns false for absolute path outside base directory', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            $result = $downloader->download('https://static.arasaac.org/image.png', '/var/www/evil.png');

            expect($result)->toBeFalse();
        });
    });

    describe('MIME type validation', function (): void {
        it('returns false when content is not a valid image', function (): void {
            $maliciousContent = '<?php echo "hacked"; ?>';

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($maliciousContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/evil.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('returns false for HTML content disguised as image', function (): void {
            $htmlContent = '<html><body>Phishing page</body></html>';

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($htmlContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/page.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeFalse();
        });

        it('accepts valid PNG image', function (): void {
            // Valid PNG file header
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.png', $this->baseDirectory . '/test.png');

            expect($result)->toBeTrue();
        });

        it('accepts valid JPEG image', function (): void {
            // Valid JPEG file header (SOI + APP0 marker)
            $jpegContent = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00";

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($jpegContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.jpg', $this->baseDirectory . '/test.jpg');

            expect($result)->toBeTrue();
        });

        it('accepts valid GIF image', function (): void {
            // Valid GIF file header
            $gifContent = "GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xFF\xFF\xFF!\xF9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x01D\x00;";

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($gifContent);

            $this->httpClient->method('request')->willReturn($response);

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $result = $downloader->download('https://static.arasaac.org/image.gif', $this->baseDirectory . '/test.gif');

            expect($result)->toBeTrue();
        });
    });

    describe('Directory permissions', function (): void {
        it('creates directory with 0755 permissions', function (): void {
            // Valid PNG file header
            $pngHeader = "\x89PNG\r\n\x1a\n";
            $ihdrChunk = "\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde";
            $imageContent = $pngHeader . $ihdrChunk;

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('getContent')->willReturn($imageContent);

            $this->httpClient->method('request')->willReturn($response);

            $newDir = $this->baseDirectory . '/secure_dir';
            $targetPath = $newDir . '/image.png';

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);
            $downloader->download('https://static.arasaac.org/image.png', $targetPath);

            // Check directory permissions (0755 = 16877 in decimal, but we check octal)
            $perms = fileperms($newDir) & 0777;
            expect($perms)->toBe(0755);

            // Cleanup
            unlink($targetPath);
            rmdir($newDir);
        });
    });

    describe('exists', function (): void {
        it('returns true when file exists', function (): void {
            $filePath = $this->baseDirectory . '/existing.png';
            file_put_contents($filePath, 'content');

            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            expect($downloader->exists($filePath))->toBeTrue();
        });

        it('returns false when file does not exist', function (): void {
            $downloader = new HttpImageDownloader($this->httpClient, $this->baseDirectory);

            expect($downloader->exists($this->baseDirectory . '/nonexistent.png'))->toBeFalse();
        });
    });
});
