<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Service\Game;

use App\Domain\Model\Game\ApiGame;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Infrastructure\Service\Game\ApiGameSearchService;
use Exception;
use Faker\Factory;
use Faker\Generator;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $this->apiGameSearchService = new ApiGameSearchService($this->apiGameRepository);
});

it('delegates searchGames call to repository with correct parameters', function () {
    // Given
    $query = 'integration-test';
    $limit = 15;
    $expectedResult = [createApiGameData($this->faker)];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with(
            $this->identicalTo($query),
            $this->identicalTo($limit)
        )
        ->willReturn($expectedResult);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedResult);
});

it('delegates searchGames call with default limit when not provided', function () {
    // Given
    $query = 'default-limit-test';
    $expectedResult = [];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with(
            $this->identicalTo($query),
            $this->identicalTo(10)
        )
        ->willReturn($expectedResult);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($expectedResult);
});

it('handles repository returning large dataset', function () {
    // Given
    $query = 'large-dataset';
    $limit = 100;
    $largeDataset = array_map(fn() => createApiGameData($this->faker), range(1, 100));

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($largeDataset);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)
        ->toBe($largeDataset)
        ->and($result)->toHaveCount(100);
});

it('handles repository returning empty array', function () {
    // Given
    $query = 'no-results';
    $limit = 10;

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn([]);

    // When
    $result = $this->apiGameSearchService->searchGames($query, $limit);

    // Then
    expect($result)
        ->toBe([])
        ->and($result)->toBeEmpty();
});

it('propagates repository exceptions during search', function () {
    // Given
    $query = 'exception-test';
    $exceptionMessage = 'Repository connection failed';

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willThrowException(new Exception($exceptionMessage));

    // When & Then
    expect(fn() => $this->apiGameSearchService->searchGames($query))
        ->toThrow(Exception::class, $exceptionMessage);
});

it('handles multiple consecutive calls to repository', function () {
    // Given
    $firstQuery = 'first-query';
    $secondQuery = 'second-query';
    $firstResult = [createApiGameData($this->faker)];
    $secondResult = [createApiGameData($this->faker), createApiGameData($this->faker)];

    $this->apiGameRepository
        ->expects($this->exactly(2))
        ->method('searchGames')
        ->willReturnCallback(function ($query, $limit) use ($firstQuery, $secondQuery, $firstResult, $secondResult) {
            return match ($query) {
                $firstQuery => $firstResult,
                $secondQuery => $secondResult,
                default => []
            };
        });

    // When
    $result1 = $this->apiGameSearchService->searchGames($firstQuery);
    $result2 = $this->apiGameSearchService->searchGames($secondQuery);

    // Then
    expect($result1)
        ->toBe($firstResult)
        ->and($result2)->toBe($secondResult);
});

it('verifies repository is called exactly once per service call', function () {
    // Given
    $query = 'single-call-test';
    $expectedResult = [];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedResult);

    // When
    $this->apiGameSearchService->searchGames($query);

    // Then - Mock expectations are automatically verified
    expect(true)->toBeTrue(); // Assertion to ensure test runs
});

it('preserves repository response structure exactly', function () {
    // Given
    $query = 'structure-test';
    $complexStructure = [
        createApiGameData($this->faker),
        [
            'custom_field' => 'custom_value',
            'nested' => [
                'deeply' => [
                    'nested' => 'value'
                ]
            ]
        ]
    ];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($complexStructure);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toEqual($complexStructure);
    expect($result[1]['nested']['deeply']['nested'])->toBe('value');
});

function createApiGameData(Generator $faker): array
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
        ],
        'platforms' => [
            [
                'id' => $faker->numberBetween(1, 200),
                'name' => 'Nintendo Switch',
            ],
        ],
        'summary' => $faker->paragraph(3),
        'websites' => [
            [
                'id' => $faker->numberBetween(10000, 999999),
                'url' => $faker->url(),
            ],
        ],
    ];
}
