<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Service\Game;

use App\Application\Service\Game\ApiGameSearchService;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use Faker\Factory;
use Faker\Generator;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $this->apiGameSearchService = new ApiGameSearchService($this->apiGameRepository);
});

it('can search games with default limit', function () {
    // Given
    $query = 'zelda';
    $expectedGames = [
        createApiGameArray($this->faker),
        createApiGameArray($this->faker),
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
});

it('can search games with custom limit', function () {
    // Given
    $query = 'mario';
    $limit = 5;
    $expectedGames = [
        createApiGameArray($this->faker),
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedGames);
});

it('returns empty array when no games found', function () {
    // Given
    $query = 'nonexistent-game';
    $expectedGames = [];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
    expect($result)->toBeEmpty();
});

it('can search games with large limit', function () {
    // Given
    $query = 'adventure';
    $limit = 100;
    $expectedGames = array_map(fn() => createApiGameArray($this->faker), range(1, 50));

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedGames);
    expect($result)->toHaveCount(50);
});

it('can search games with minimum limit', function () {
    // Given
    $query = 'rpg';
    $limit = 1;
    $expectedGames = [
        createApiGameArray($this->faker),
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedGames);
    expect($result)->toHaveCount(1);
});

it('handles special characters in search query', function () {
    // Given
    $query = 'game-with-special:characters!@#$%';
    $expectedGames = [
        createApiGameArray($this->faker),
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
});

it('handles empty search query', function () {
    // Given
    $query = '';
    $expectedGames = [];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
});

it('handles unicode characters in search query', function () {
    // Given
    $query = 'ポケモン';
    $expectedGames = [
        createApiGameArray($this->faker),
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
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
