<?php

declare(strict_types=1);

describe('ApiExceptionSubscriber', function (): void {
    it('returns JSON for API 404 errors', function (): void {
        $client = static::createClient();
        $client->request('GET', '/api/this-route-does-not-exist');

        $response = $client->getResponse();
        expect($response->getStatusCode())->toBe(404);
        expect($response->headers->get('Content-Type'))->toContain('application/json');

        $data = json_decode($response->getContent(), true);
        expect($data)->toHaveKey('error');
        expect($data['error'])->toBe('Not Found');
        expect($data['status'])->toBe(404);
    });

    it('returns JSON for API 405 errors', function (): void {
        $client = static::createClient();
        $client->request('DELETE', '/api/categories');

        $response = $client->getResponse();
        expect($response->getStatusCode())->toBe(405);
        expect($response->headers->get('Content-Type'))->toContain('application/json');

        $data = json_decode($response->getContent(), true);
        expect($data['error'])->toBe('Method Not Allowed');
    });

    it('does not affect non-API routes', function (): void {
        $client = static::createClient();
        $client->request('GET', '/this-is-not-api');

        $response = $client->getResponse();
        expect($response->headers->get('Content-Type'))->not->toContain('application/json');
    });
});
