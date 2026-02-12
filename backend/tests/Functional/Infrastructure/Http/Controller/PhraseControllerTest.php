<?php

declare(strict_types=1);

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Phrase\Repository\PhraseRepository;
use App\Domain\Phrase\Service\PhraseGeneratorInterface;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\ValueObject\ArasaacId;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

function createTestPictogram(string $id, string $label): Pictogram
{
    return new Pictogram(
        PictogramId::fromString($id),
        new ArasaacId(12345),
        CategoryId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        $label,
        'https://static.arasaac.org/pictograms/12345/12345_500.png'
    );
}

describe('PhraseController', function (): void {
    describe('POST /api/phrases/generate', function (): void {
        it('returns phrase variations', function (): void {
            $client = static::createClient();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $phraseRepo = $this->createMock(PhraseRepository::class);
            $phraseGen = $this->createMock(PhraseGeneratorInterface::class);
            $uuidGen = $this->createMock(UuidGeneratorInterface::class);

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $pictogramRepo->method('findByIds')->willReturn([$pictogram]);
            $phraseRepo->method('findBySequenceHash')->willReturn(null);
            $phraseGen->method('generate')->willReturn([
                'Quiero comer',
                'Me gustaría comer',
                'Necesito comer',
            ]);
            $uuidGen->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $phraseRepo);
            self::getContainer()->set(PhraseGeneratorInterface::class, $phraseGen);
            self::getContainer()->set(UuidGeneratorInterface::class, $uuidGen);

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => [$pictogramId]])
            );

            expect($client->getResponse()->getStatusCode())->toBe(200);
            expect($client->getResponse()->headers->get('Content-Type'))->toBe('application/json');

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveKey('variations');
            expect($data['variations'])->toHaveCount(3);
        });

        it('returns correct JSON structure', function (): void {
            $client = static::createClient();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $phraseRepo = $this->createMock(PhraseRepository::class);
            $phraseGen = $this->createMock(PhraseGeneratorInterface::class);
            $uuidGen = $this->createMock(UuidGeneratorInterface::class);

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $pictogramRepo->method('findByIds')->willReturn([$pictogram]);
            $phraseRepo->method('findBySequenceHash')->willReturn(null);
            $phraseGen->method('generate')->willReturn(['Quiero comer']);
            $uuidGen->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $phraseRepo);
            self::getContainer()->set(PhraseGeneratorInterface::class, $phraseGen);
            self::getContainer()->set(UuidGeneratorInterface::class, $uuidGen);

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => [$pictogramId]])
            );

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveKeys(['variations', 'source', 'sequenceHash', 'pictogramIds']);
        });

        it('returns 400 with invalid JSON', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                'invalid json {'
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Invalid JSON body');
        });

        it('returns 400 when missing pictogramIds', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['other' => 'field'])
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Missing required field: pictogramIds');
        });

        it('returns 400 when pictogramIds not array', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => 'not-an-array'])
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('pictogramIds must be an array');
        });

        it('returns 400 when pictogramIds empty', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => []])
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('pictogramIds must contain between 1 and 10 elements');
        });

        it('returns 400 when pictogramIds exceeds max 10', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $ids = array_map(
                fn (int $i) => sprintf('550e8400-e29b-41d4-a716-4466554400%02d', $i),
                range(1, 11)
            );

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => $ids])
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('pictogramIds must contain between 1 and 10 elements');
        });

        it('returns 400 with invalid UUID format', function (): void {
            $client = static::createClient();

            self::getContainer()->set(PictogramRepository::class, $this->createMock(PictogramRepository::class));
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => ['invalid-uuid']])
            );

            expect($client->getResponse()->getStatusCode())->toBe(400);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toBe('Each pictogramId must be a valid UUID v4');
        });

        it('returns 404 when pictogram not found', function (): void {
            $client = static::createClient();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $pictogramRepo->method('findByIds')->willReturn([]);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $this->createMock(PhraseRepository::class));
            self::getContainer()->set(PhraseGeneratorInterface::class, $this->createMock(PhraseGeneratorInterface::class));
            self::getContainer()->set(UuidGeneratorInterface::class, $this->createMock(UuidGeneratorInterface::class));

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => ['550e8400-e29b-41d4-a716-446655440010']])
            );

            expect($client->getResponse()->getStatusCode())->toBe(404);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data)->toHaveKey('error');
        });

        it('returns valid JSON', function (): void {
            $client = static::createClient();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $phraseRepo = $this->createMock(PhraseRepository::class);
            $phraseGen = $this->createMock(PhraseGeneratorInterface::class);
            $uuidGen = $this->createMock(UuidGeneratorInterface::class);

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $pictogramRepo->method('findByIds')->willReturn([$pictogram]);
            $phraseRepo->method('findBySequenceHash')->willReturn(null);
            $phraseGen->method('generate')->willReturn(['Quiero comer']);
            $uuidGen->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $phraseRepo);
            self::getContainer()->set(PhraseGeneratorInterface::class, $phraseGen);
            self::getContainer()->set(UuidGeneratorInterface::class, $uuidGen);

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => [$pictogramId]])
            );

            json_decode($client->getResponse()->getContent(), true);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });

        it('rejects GET method', function (): void {
            $client = static::createClient();

            $client->request('GET', '/api/phrases/generate');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('rejects PUT method', function (): void {
            $client = static::createClient();

            $client->request('PUT', '/api/phrases/generate');

            expect($client->getResponse()->getStatusCode())->toBe(405);
        });

        it('handles multiple pictograms', function (): void {
            $client = static::createClient();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $phraseRepo = $this->createMock(PhraseRepository::class);
            $phraseGen = $this->createMock(PhraseGeneratorInterface::class);
            $uuidGen = $this->createMock(UuidGeneratorInterface::class);

            $pictogramId1 = '550e8400-e29b-41d4-a716-446655440010';
            $pictogramId2 = '550e8400-e29b-41d4-a716-446655440011';
            $pictogram1 = createTestPictogram($pictogramId1, 'yo');
            $pictogram2 = createTestPictogram($pictogramId2, 'comer');

            $pictogramRepo->method('findByIds')->willReturn([$pictogram1, $pictogram2]);

            $phraseRepo->method('findBySequenceHash')->willReturn(null);
            $phraseGen->method('generate')->willReturn([
                'Yo quiero comer',
                'Yo necesito comer',
                'Yo deseo comer',
            ]);
            $uuidGen->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $phraseRepo);
            self::getContainer()->set(PhraseGeneratorInterface::class, $phraseGen);
            self::getContainer()->set(UuidGeneratorInterface::class, $uuidGen);

            $client->request(
                'POST',
                '/api/phrases/generate',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pictogramIds' => [$pictogramId1, $pictogramId2]])
            );

            expect($client->getResponse()->getStatusCode())->toBe(200);
            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['pictogramIds'])->toHaveCount(2);
        });

        it('returns 429 when daily rate limit is exceeded', function (): void {
            $client = static::createClient();
            $client->disableReboot();

            $pictogramRepo = $this->createMock(PictogramRepository::class);
            $phraseRepo = $this->createMock(PhraseRepository::class);
            $phraseGen = $this->createMock(PhraseGeneratorInterface::class);
            $uuidGen = $this->createMock(UuidGeneratorInterface::class);

            self::getContainer()->set(PictogramRepository::class, $pictogramRepo);
            self::getContainer()->set(PhraseRepository::class, $phraseRepo);
            self::getContainer()->set(PhraseGeneratorInterface::class, $phraseGen);
            self::getContainer()->set(UuidGeneratorInterface::class, $uuidGen);

            // Pre-exhaust the daily limiter (PHRASE_DAILY_LIMIT=3 in .env.test)
            $limiterFactory = self::getContainer()->get('limiter.phrase_daily');
            $limiter = $limiterFactory->create('127.0.0.1');
            $limiter->consume(3);

            $body = json_encode(['pictogramIds' => ['550e8400-e29b-41d4-a716-446655440010']]);
            $headers = ['CONTENT_TYPE' => 'application/json'];

            $client->request('POST', '/api/phrases/generate', [], [], $headers, $body);
            expect($client->getResponse()->getStatusCode())->toBe(429);

            $data = json_decode($client->getResponse()->getContent(), true);
            expect($data['error'])->toContain('Daily request limit');
        });

        it('has correct rate limit response constant', function (): void {
            expect(Response::HTTP_TOO_MANY_REQUESTS)->toBe(429);
        });
    });
});
