<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ExternalApi\Arasaac;

use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Infrastructure\ExternalApi\Arasaac\ArasaacApiClient;
use App\Infrastructure\ExternalApi\Arasaac\Exception\ArasaacApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

describe('ArasaacApiClient', function (): void {
    it('implements PictogramProviderInterface', function (): void {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = new ArasaacApiClient($httpClient);

        expect($client)->toBeInstanceOf(PictogramProviderInterface::class);
    });

    describe('searchByKeyword', function (): void {
        it('returns empty array when no results', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->searchByKeyword('palabrainexistente');

            expect($result)->toBe([]);
        });

        it('returns array of Pictograms when found', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                [
                    '_id' => 12345,
                    'aac' => true,
                    'keywords' => [
                        ['keyword' => 'comer'],
                    ],
                ],
                [
                    '_id' => 12346,
                    'aac' => true,
                    'keywords' => [
                        ['keyword' => 'comida'],
                    ],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->searchByKeyword('comer');

            expect($result)->toHaveCount(2);
            expect($result[0])->toBeInstanceOf(Pictogram::class);
            expect($result[1])->toBeInstanceOf(Pictogram::class);
        });

        it('calls API with correct URL and language', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'GET',
                    $this->stringContains('https://api.arasaac.org/v1/pictograms/es/search/comer')
                )
                ->willReturn($response);

            $client = new ArasaacApiClient($httpClient);
            $client->searchByKeyword('comer', 'es');
        });

        it('uses specified language', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'GET',
                    $this->stringContains('/en/search/')
                )
                ->willReturn($response);

            $client = new ArasaacApiClient($httpClient);
            $client->searchByKeyword('eat', 'en');
        });

        it('throws ArasaacApiException on API error', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(500);
            $response->method('getContent')->willReturn('Internal Server Error');

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            expect(fn () => $client->searchByKeyword('comer'))
                ->toThrow(ArasaacApiException::class);
        });

        it('encodes special characters in keyword', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'GET',
                    $this->callback(function (string $url): bool {
                        // URL should be properly encoded
                        return str_contains($url, 'ni%C3%B1o') || str_contains($url, 'niño');
                    })
                )
                ->willReturn($response);

            $client = new ArasaacApiClient($httpClient);
            $client->searchByKeyword('niño', 'es');
        });

        it('filters results to only return pictograms with aac true', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                [
                    '_id' => 6456,
                    'aac' => true,
                    'keywords' => [['keyword' => 'comer']],
                ],
                [
                    '_id' => 2349,
                    'aac' => false,
                    'keywords' => [['keyword' => 'comer']],
                ],
                [
                    '_id' => 9999,
                    'aac' => true,
                    'keywords' => [['keyword' => 'comer']],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->searchByKeyword('comer');

            expect($result)->toHaveCount(2);
            expect($result[0]->arasaacId()->value())->toBe(6456);
            expect($result[1]->arasaacId()->value())->toBe(9999);
        });

        it('returns all pictograms as fallback when none have aac true', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                [
                    '_id' => 1111,
                    'aac' => false,
                    'keywords' => [['keyword' => 'comer']],
                ],
                [
                    '_id' => 2222,
                    'aac' => false,
                    'keywords' => [['keyword' => 'comer']],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->searchByKeyword('comer');

            expect($result)->toHaveCount(2);
            expect($result[0]->arasaacId()->value())->toBe(1111);
            expect($result[1]->arasaacId()->value())->toBe(2222);
        });
    });

    describe('fetchById', function (): void {
        it('returns null when pictogram not found', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(404);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->fetchById(99999999);

            expect($result)->toBeNull();
        });

        it('returns Pictogram when found', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                '_id' => 12345,
                'keywords' => [
                    ['keyword' => 'comer', 'type' => 1],
                ],
                'categories' => ['core vocabulary'],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->fetchById(12345);

            expect($result)->toBeInstanceOf(Pictogram::class);
            expect($result->arasaacId()->value())->toBe(12345);
        });

        it('calls API with correct URL', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                '_id' => 12345,
                'keywords' => [['keyword' => 'comer']],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->expects($this->once())
                ->method('request')
                ->with(
                    'GET',
                    $this->stringContains('https://api.arasaac.org/v1/pictograms/es/12345')
                )
                ->willReturn($response);

            $client = new ArasaacApiClient($httpClient);
            $client->fetchById(12345);
        });

        it('throws ArasaacApiException on server error', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(500);
            $response->method('getContent')->willReturn('Internal Server Error');

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            expect(fn () => $client->fetchById(12345))
                ->toThrow(ArasaacApiException::class);
        });
    });

    describe('sync', function (): void {
        it('returns 0 when no keywords provided', function (): void {
            $httpClient = $this->createMock(HttpClientInterface::class);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->sync([]);

            expect($result)->toBe(0);
        });

        it('returns count of synced pictograms', function (): void {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn(200);
            $response->method('toArray')->willReturn([
                [
                    '_id' => 12345,
                    'aac' => true,
                    'keywords' => [['keyword' => 'comer']],
                ],
            ]);

            $httpClient = $this->createMock(HttpClientInterface::class);
            $httpClient->method('request')->willReturn($response);

            $client = new ArasaacApiClient($httpClient);

            $result = $client->sync(['comer', 'beber']);

            expect($result)->toBe(2);
        });
    });

    describe('getImageUrl', function (): void {
        it('returns correct ARASAAC CDN URL', function (): void {
            $httpClient = $this->createMock(HttpClientInterface::class);
            $client = new ArasaacApiClient($httpClient);

            $url = $client->getImageUrl(12345);

            expect($url)->toBe('https://static.arasaac.org/pictograms/12345/12345_500.png');
        });

        it('supports custom resolution', function (): void {
            $httpClient = $this->createMock(HttpClientInterface::class);
            $client = new ArasaacApiClient($httpClient);

            $url = $client->getImageUrl(12345, 300);

            expect($url)->toBe('https://static.arasaac.org/pictograms/12345/12345_300.png');
        });
    });
});
