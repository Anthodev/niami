<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Client;

use App\Domain\Model\Game\ApiGame;
use App\Infrastructure\Client\IgdbClient;
use App\Infrastructure\Enum\IgdbGamePlatformEnum;
use App\Infrastructure\Exception\Game\IgdbAccessTokenRetrievalException;
use App\Shared\Dto\Game\IgdbSearchResponseDto;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->cache = $this->createMock(CacheInterface::class);
    $this->serializer = $this->createMock(Serializer::class);
    $this->httpClient = $this->createMock(HttpClientInterface::class);

    $this->igdbClient = new IgdbClient(
        clientId: 'test-client-id',
        clientSecret: 'test-client-secret',
        cache: $this->cache,
        serializer: $this->serializer,
        baseUrl: 'https://api.igdb.com/v4/'
    );

    $reflection = new \ReflectionClass($this->igdbClient);
    $httpClientProperty = $reflection->getProperty('httpClient');
    $httpClientProperty->setAccessible(true);
    $httpClientProperty->setValue($this->igdbClient, $this->httpClient);
});

it('searches games with default limit', function () {
    // Given
    $query = 'zelda';
    $limit = 10;

    $cacheKeyData = [
        'query' => trim(strtolower($query)),
        'limit' => $limit,
        'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
    ];

    $expectedGames = [createApiGame($this->faker)];
    $cacheSearchKey = 'igdb_api_search_' . md5(json_encode($cacheKeyData));
    $cacheGameKey = 'api_game_' . $expectedGames[0]->getSlug();

    $this->cache
        ->expects($this->exactly(2))
        ->method('get')
        ->with($this->callback(function ($cacheKey) use (
            $cacheSearchKey,
            $cacheGameKey,
        ) {
            return in_array($cacheKey, [$cacheSearchKey, $cacheGameKey]);
        }))
        ->willReturnCallback(function ($cacheKey, $callback) use (
            $expectedGames,
            $cacheSearchKey,
            $cacheGameKey,
        ) {
            return match($cacheKey) {
                $cacheSearchKey => $expectedGames,
                $cacheGameKey => $expectedGames[0],
                default => throw new \RuntimeException('Unexpected envelope')
            };
    });

    // When
    $result = $this->igdbClient->searchGames($query, $limit);

    // Then
    expect($result)
        ->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(ApiGame::class)
        ->and($result[0])->toBe($expectedGames[0]);
});

it('searches games with custom limit', function () {
    // Given
    $query = 'mario';
    $limit = 5;

    $cacheKeyData = [
        'query' => trim(strtolower($query)),
        'limit' => $limit,
        'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
    ];

    $expectedGames = [createApiGame($this->faker), createApiGame($this->faker)];
    $cacheSearchKey = 'igdb_api_search_' . md5(json_encode($cacheKeyData));
    $cacheFirstGameKey = 'api_game_' . $expectedGames[0]->getSlug();
    $cacheSecondGameKey = 'api_game_' . $expectedGames[1]->getSlug();

    $this->cache
        ->expects($this->exactly(3))
        ->method('get')
        ->with($this->callback(function ($cacheKey) use (
            $cacheSearchKey,
            $cacheFirstGameKey,
            $cacheSecondGameKey,
        ) {
            return in_array($cacheKey, [$cacheSearchKey, $cacheFirstGameKey, $cacheSecondGameKey]);;
        }))
        ->willReturnCallback(function ($cacheKey, $callback) use (
            $expectedGames,
            $cacheSearchKey,
            $cacheFirstGameKey,
            $cacheSecondGameKey,
        ) {
            return match($cacheKey) {
                $cacheSearchKey => $expectedGames,
                $cacheFirstGameKey => $expectedGames[0],
                $cacheSecondGameKey => $expectedGames[1],
                default => throw new \RuntimeException('Unexpected envelope')
            };
        });

    // When
    $result = $this->igdbClient->searchGames($query, $limit);

    // Then
    expect($result)
        ->toHaveCount(2)
        ->toBe($expectedGames)
    ;
});

it('returns cached results when available', function () {
    // Given
    $query = 'cached-game';
    $limit = 10;

    $cacheKeyData = [
        'query' => trim(strtolower($query)),
        'limit' => $limit,
        'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
    ];

    $expectedGames = [createApiGame($this->faker)];
    $cacheSearchKey = 'igdb_api_search_' . md5(json_encode($cacheKeyData));
    $cacheGameKey = 'api_game_' . $expectedGames[0]->getSlug();

    $this->cache
        ->expects($this->exactly(2))
        ->method('get')
        ->with($this->callback(function ($cacheKey) use (
            $cacheSearchKey,
            $cacheGameKey,
        ) {
            return in_array($cacheKey, [$cacheSearchKey, $cacheGameKey]);
        }))
        ->willReturnCallback(function ($cacheKey, $callback) use (
            $expectedGames,
            $cacheSearchKey,
            $cacheGameKey,
        ) {
            return match($cacheKey) {
                $cacheSearchKey => $expectedGames,
                $cacheGameKey => $expectedGames[0],
                default => throw new \RuntimeException('Unexpected envelope')
            };
        });

    // When
    $result = $this->igdbClient->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedGames);
});

it('gets game by slug successfully', function () {
    // Given
    $slug = 'test-game-slug';
    $expectedGame = createApiGame($this->faker);

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) use ($expectedGame) {
            return [$expectedGame];
        });

    // When
    $result = $this->igdbClient->getGameBySlug($slug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('returns null when game not found by slug', function () {
    // Given
    $slug = 'nonexistent-slug';

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) {
            return [];
        });

    // When
    $result = $this->igdbClient->getGameBySlug($slug);

    // Then
    expect($result)->toBeNull();
});

it('generates correct search cache key', function () {
    // Given
    $query = 'Test Game';
    $limit = 15;

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) use ($query, $limit) {
            // Verify cache key format
            expect($cacheKey)
                ->toStartWith('igdb_api_search_')
                ->and($cacheKey)->toContain(
                    md5(json_encode([
                        'query' => trim(strtolower($query)),
                        'limit' => $limit,
                        'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
                    ]))
                );
            return [];
        });

    // When
    $this->igdbClient->searchGames($query, $limit);
});

it('generates correct slug cache key', function () {
    // Given
    $slug = 'test-slug';
    $limit = 3;

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) use ($slug, $limit) {
            expect($cacheKey)
                ->toStartWith('igdb_api_slug_')
                ->and($cacheKey)->toContain(
                    md5(json_encode([
                        'query' => trim(strtolower($slug)),
                        'limit' => $limit,
                        'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
                    ]))
                );
            return [];
        });

    // When
    $this->igdbClient->getGameBySlug($slug);
});

it('handles empty query by returning empty array', function () {
    // Given
    $query = '';

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) {
            return $callback();
        });

    // When
    $result = $this->igdbClient->searchGames($query);

    // Then
    expect($result)->toBeEmpty();
});

it('retries request on 401 unauthorized error', function () {
    // Given
    $query = 'retry-test';
    $newToken = 'new-access-token';
    $initialToken = 'initial-access-token';

    $unauthorizedResponse = $this->createMock(ResponseInterface::class);
    $unauthorizedResponse->method('getStatusCode')->willReturn(401);

    $initialTokenResponse = $this->createMock(ResponseInterface::class);
    $initialTokenResponse->method('getStatusCode')->willReturn(200);
    $initialTokenResponse->method('toArray')->willReturn(['access_token' => $initialToken]);

    $unauthorizedException = new class($unauthorizedResponse) extends \Exception implements ClientExceptionInterface {
        private ResponseInterface $response;

        public function __construct(ResponseInterface $response) {
            parent::__construct('Unauthorized');
            $this->response = $response;
        }

        public function getResponse(): ResponseInterface {
            return $this->response;
        }
    };

    $newTokenResponse = $this->createMock(ResponseInterface::class);
    $newTokenResponse->method('getStatusCode')->willReturn(200);
    $newTokenResponse->method('toArray')->willReturn(['access_token' => $newToken]);

    $successResponse = $this->createMock(ResponseInterface::class);
    $successResponse->method('getStatusCode')->willReturn(200);
    $successResponse->method('toArray')->willReturn([]);

    $this->httpClient
        ->expects($this->exactly(4))
        ->method('request')
        ->willReturnOnConsecutiveCalls(
            $initialTokenResponse,
            $this->throwException($unauthorizedException),
            $newTokenResponse,
            $successResponse,
        );

    $this->serializer
        ->expects($this->once())
        ->method('denormalize')
        ->willReturn([]);

    $this->cache
        ->expects($this->exactly(3))
        ->method('get')
        ->willReturnCallback(function ($cacheKey, $callback) {
            return $callback();
        });

    $this->cache
        ->expects($this->once())
        ->method('delete')
        ->with('igdb_access_token');

    // When
    $result = $this->igdbClient->searchGames($query);

    // Then
    expect($result)->toBeEmpty();
});

it('throws exception when token retrieval fails', function () {
    // Given
    $this->cache
        ->expects($this->exactly(2))
        ->method('get')
        ->willReturnCallback(function ($key, $callback) {
            return $callback();
        });

    $transportException = new class('Network error') extends \Exception implements TransportExceptionInterface {};

    $this->httpClient
        ->expects($this->once())
        ->method('request')
        ->willThrowException($transportException);

    // When & Then
    expect(fn() => $this->igdbClient->searchGames('test'))
        ->toThrow(IgdbAccessTokenRetrievalException::class);
});

function createApiGame(Generator $faker): ApiGame
{
    return new ApiGame(
        name: $faker->words(3, true),
        slug: $faker->slug(),
        description: $faker->paragraph(),
        imageCover: 'https://images.igdb.com/igdb/image/upload/t_thumb/' . $faker->sha1() . '.jpg',
        publisher: $faker->company(),
        releaseDate: $faker->dateTime()->format(DATE_ATOM)
    );
}

function createIgdbSearchResponseDto(Generator $faker): IgdbSearchResponseDto
{
    return new IgdbSearchResponseDto(
        id: $faker->randomNumber(),
        name: $faker->words(3, true),
        slug: $faker->slug(),
        involved_companies: [
            [
                'company' => ['name' => $faker->company()],
                'publisher' => true
            ]
        ],
        cover: [
            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/' . $faker->sha1() . '.jpg'
        ],
        first_release_date: $faker->unixTime(),
        summary: $faker->paragraph(),
        websites: [
            ['url' => $faker->url()]
        ]
    );
}
