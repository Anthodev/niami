<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Application\Service\Game\ApiGameSearchService;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->apiGameSearchService = new ApiGameSearchService($this->messageBus);
});

it('can search games with default limit', function () {
    // Given
    $query = 'zelda';
    $apiGames = [
        createApiGameArray($this->faker),
        createApiGameArray($this->faker),
    ];

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('can search games with custom limit', function () {
    // Given
    $query = 'mario';
    $limit = 5;
    $apiGames = [
        createApiGameArray($this->faker),
    ];

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query, $limit) {
            return $searchQuery->query === $query && $searchQuery->limit === $limit;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($formattedResult);
});

it('returns empty array when no games found', function () {
    // Given
    $query = 'nonexistent-game';

    $expectedResult = [
        'local' => [],
        'api' => [],
        'total' => 0
    ];

    $formattedResult = [
        'games' => [],
        'total' => 0
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
    expect($result['games'])->toBeEmpty();
});

it('can search games with large limit', function () {
    // Given
    $query = 'adventure';
    $limit = 100;
    $apiGames = array_map(fn() => createApiGameArray($this->faker), range(1, 50));

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query, $limit) {
            return $searchQuery->query === $query && $searchQuery->limit === $limit;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($formattedResult);
    expect($result['games'])->toHaveCount(50);
});

it('can search games with minimum limit', function () {
    // Given
    $query = 'rpg';
    $limit = 1;
    $apiGames = [
        createApiGameArray($this->faker),
    ];

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query, $limit) {
            return $searchQuery->query === $query && $searchQuery->limit === $limit;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($formattedResult);
    expect($result['games'])->toHaveCount(1);
});

it('handles special characters in search query', function () {
    // Given
    $query = 'game-with-special:characters!@#$%';
    $apiGames = [
        createApiGameArray($this->faker),
    ];

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('handles empty search query', function () {
    // Given
    $query = '';

    $expectedResult = [
        'local' => [],
        'api' => [],
        'total' => 0
    ];

    $formattedResult = [
        'games' => [],
        'total' => 0
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('handles unicode characters in search query', function () {
    // Given
    $query = 'ポケモン';
    $apiGames = [
        createApiGameArray($this->faker),
    ];

    $expectedResult = [
        'local' => [],
        'api' => $apiGames,
        'total' => count($apiGames)
    ];

    $formattedResult = [
        'games' => $apiGames,
        'total' => count($apiGames)
    ];

    // Create a HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new \stdClass(), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willReturn($envelope);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

function createApiGameArray(Generator $faker): array
{
    return [
        'id' => $faker->numberBetween(1000, 999999),
        'name' => $faker->words(3, true),
        'slug' => $faker->slug(),
        'cover' => [
            'id' => $faker->numberBetween(10000, 999999),
            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/' . $faker->lexify('??????') . '.jpg',
        ],
        'first_release_date' => $faker->unixTime(),
        'involved_companies' => [
            [
                'id' => $faker->numberBetween(1000, 99999),
                'company' => [
                    'id' => $faker->numberBetween(1, 9999),
                    'name' => $faker->company(),
                ],
            ],
            [
                'id' => $faker->numberBetween(1000, 99999),
                'company' => [
                    'id' => $faker->numberBetween(1, 9999),
                    'name' => $faker->company(),
                ],
            ],
        ],
        'platforms' => [
            [
                'id' => $faker->numberBetween(1, 200),
                'name' => 'Nintendo Switch',
            ],
            [
                'id' => $faker->numberBetween(1, 200),
                'name' => $faker->randomElement([
                    'PlayStation 5',
                    'Xbox Series',
                    'PC',
                ]),
            ],
        ],
        'summary' => $faker->paragraph(3),
        'websites' => [
            [
                'id' => $faker->numberBetween(10000, 999999),
                'url' => $faker->url(),
            ],
            [
                'id' => $faker->numberBetween(10000, 999999),
                'url' => 'https://en.wikipedia.org/wiki/' . $faker->slug(),
            ],
            [
                'id' => $faker->numberBetween(10000, 999999),
                'url' => 'https://www.twitch.tv/directory/game/' . urlencode($faker->words(3, true)),
            ],
        ],
    ];
}
