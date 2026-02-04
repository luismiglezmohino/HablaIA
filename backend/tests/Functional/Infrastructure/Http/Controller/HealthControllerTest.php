<?php

declare(strict_types=1);

use App\Infrastructure\Health\DatabaseHealthCheckerInterface;

beforeEach(function (): void {
    $this->healthChecker = $this->createMock(DatabaseHealthCheckerInterface::class);
});

describe('HealthController', function (): void {
    describe('GET /api/health', function (): void {
        it('returns healthy when database is up', function (): void {
            $client = static::createClient();

            $this->healthChecker->method('check')->willReturn([
                'status' => 'up',
                'latency_ms' => 1.23,
            ]);

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            expect($client->getResponse()->headers->get('Content-Type'))->toBe('application/json');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['status'])->toBe('healthy');
            expect($data)->toHaveKey('timestamp');
            expect($data)->toHaveKey('components');
            expect($data['components']['database']['status'])->toBe('up');
            expect($data['components']['database'])->toHaveKey('latency_ms');
        });

        it('returns unhealthy when database is down', function (): void {
            $client = static::createClient();

            $this->healthChecker->method('check')->willReturn([
                'status' => 'down',
                'error' => 'Connection refused',
            ]);

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health');

            expect($client->getResponse()->getStatusCode())->toBe(503);

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['status'])->toBe('unhealthy');
            expect($data['components']['database']['status'])->toBe('down');
            expect($data['components']['database'])->toHaveKey('error');
        });

        it('returns valid timestamp', function (): void {
            $client = static::createClient();

            $this->healthChecker->method('check')->willReturn([
                'status' => 'up',
                'latency_ms' => 1.0,
            ]);

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health');

            $data = json_decode($client->getResponse()->getContent(), true);
            $timestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $data['timestamp']);
            expect($timestamp)->not->toBeFalse();
        });
    });

    describe('GET /api/health/live', function (): void {
        it('returns alive', function (): void {
            $client = static::createClient();

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health/live');

            expect($client->getResponse()->getStatusCode())->toBe(200);
            expect($client->getResponse()->headers->get('Content-Type'))->toBe('application/json');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['status'])->toBe('alive');
        });

        it('does not check database', function (): void {
            $client = static::createClient();

            $this->healthChecker->expects($this->never())->method('check');

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health/live');

            expect($client->getResponse()->getStatusCode())->toBe(200);
        });
    });

    describe('GET /api/health/ready', function (): void {
        it('returns ready when database is up', function (): void {
            $client = static::createClient();

            $this->healthChecker->method('check')->willReturn([
                'status' => 'up',
                'latency_ms' => 0.5,
            ]);

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health/ready');

            expect($client->getResponse()->getStatusCode())->toBe(200);

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['status'])->toBe('ready');
        });

        it('returns not ready when database is down', function (): void {
            $client = static::createClient();

            $this->healthChecker->method('check')->willReturn([
                'status' => 'down',
                'error' => 'Connection refused',
            ]);

            self::getContainer()->set(DatabaseHealthCheckerInterface::class, $this->healthChecker);

            $client->request('GET', '/api/health/ready');

            expect($client->getResponse()->getStatusCode())->toBe(503);

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['status'])->toBe('not_ready');
            expect($data['reason'])->toBe('Database connection failed');
        });
    });

    describe('HTTP methods', function (): void {
        it('rejects POST on /api/health', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/health');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('rejects POST on /api/health/live', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/health/live');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('rejects POST on /api/health/ready', function (): void {
            $client = static::createClient();

            $client->request('POST', '/api/health/ready');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });
    });
});
