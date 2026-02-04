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

beforeEach(function (): void {
    $this->pictogramRepository = $this->createMock(PictogramRepository::class);
    $this->phraseRepository = $this->createMock(PhraseRepository::class);
    $this->phraseGenerator = $this->createMock(PhraseGeneratorInterface::class);
    $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
});

function setupMocks(object $test): void
{
    self::getContainer()->set(PictogramRepository::class, $test->pictogramRepository);
    self::getContainer()->set(PhraseRepository::class, $test->phraseRepository);
    self::getContainer()->set(PhraseGeneratorInterface::class, $test->phraseGenerator);
    self::getContainer()->set(UuidGeneratorInterface::class, $test->uuidGenerator);
}

describe('PhraseController', function (): void {
    describe('POST /api/phrases/generate', function (): void {
        it('returns phrase variations', function (): void {
            $client = static::createClient();

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $this->pictogramRepository->method('findById')->willReturn($pictogram);
            $this->phraseRepository->method('findBySequenceHash')->willReturn(null);
            $this->phraseGenerator->method('generate')->willReturn([
                'Quiero comer',
                'Me gustaría comer',
                'Necesito comer',
            ]);
            $this->uuidGenerator->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            setupMocks($this);

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

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $this->pictogramRepository->method('findById')->willReturn($pictogram);
            $this->phraseRepository->method('findBySequenceHash')->willReturn(null);
            $this->phraseGenerator->method('generate')->willReturn(['Quiero comer']);
            $this->uuidGenerator->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            setupMocks($this);

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
            setupMocks($this);

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
            setupMocks($this);

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
            setupMocks($this);

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
            setupMocks($this);

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
            expect($data)->toHaveKey('error');
        });

        it('returns 400 with invalid UUID format', function (): void {
            $client = static::createClient();
            setupMocks($this);

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
            expect($data['error'])->toBe('Invalid UUID format');
        });

        it('returns 404 when pictogram not found', function (): void {
            $client = static::createClient();

            $this->pictogramRepository->method('findById')->willReturn(null);
            setupMocks($this);

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

            $pictogramId = '550e8400-e29b-41d4-a716-446655440010';
            $pictogram = createTestPictogram($pictogramId, 'comer');

            $this->pictogramRepository->method('findById')->willReturn($pictogram);
            $this->phraseRepository->method('findBySequenceHash')->willReturn(null);
            $this->phraseGenerator->method('generate')->willReturn(['Quiero comer']);
            $this->uuidGenerator->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            setupMocks($this);

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

            $pictogramId1 = '550e8400-e29b-41d4-a716-446655440010';
            $pictogramId2 = '550e8400-e29b-41d4-a716-446655440011';
            $pictogram1 = createTestPictogram($pictogramId1, 'yo');
            $pictogram2 = createTestPictogram($pictogramId2, 'comer');

            $this->pictogramRepository->method('findById')
                ->willReturnCallback(function (PictogramId $id) use ($pictogram1, $pictogram2) {
                    return match ($id->value()) {
                        '550e8400-e29b-41d4-a716-446655440010' => $pictogram1,
                        '550e8400-e29b-41d4-a716-446655440011' => $pictogram2,
                        default => null,
                    };
                });

            $this->phraseRepository->method('findBySequenceHash')->willReturn(null);
            $this->phraseGenerator->method('generate')->willReturn([
                'Yo quiero comer',
                'Yo necesito comer',
                'Yo deseo comer',
            ]);
            $this->uuidGenerator->method('generate')->willReturn('550e8400-e29b-41d4-a716-446655440099');

            setupMocks($this);

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

        it('has correct rate limit response constant', function (): void {
            expect(Response::HTTP_TOO_MANY_REQUESTS)->toBe(429);
        });
    });
});
