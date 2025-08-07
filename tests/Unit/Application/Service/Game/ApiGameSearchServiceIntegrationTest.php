<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Application\Service\Game\ApiGameSearchService;
use Exception;
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

it('delegates searchGames call to message bus with correct parameters', function () {
    // Given
    $query = 'integration-test';
    $limit = 15;
    $expectedResult = [
        'local' => [],
        'api' => [createApiGameData($this->faker)],
        'total' => 1
    ];
    $formattedResult = [
        'games' => $expectedResult['api'],
        'total' => $expectedResult['total']
    ];

    // Create a real HandledStamp with the expected result
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

it('delegates searchGames call with default limit when not provided', function () {
    // Given
    $query = 'default-limit-test';
    $expectedResult = [
        'local' => [],
        'api' => [],
        'total' => 0
    ];
    $formattedResult = [
        'games' => [],
        'total' => 0
    ];

    // Create a real HandledStamp with the expected result
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

it('handles message bus returning large dataset', function () {
    // Given
    $query = 'large-dataset';
    $limit = 100;
    $largeDataset = array_map(fn() => createApiGameData($this->faker), range(1, 100));

    $expectedResult = [
        'local' => [],
        'api' => $largeDataset,
        'total' => count($largeDataset)
    ];

    $formattedResult = [
        'games' => $largeDataset,
        'total' => count($largeDataset)
    ];

    // Create a real HandledStamp with the expected result
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
    expect($result)
        ->toBe($formattedResult)
        ->and($result['games'])->toHaveCount(100);
});

it('handles message bus returning empty array', function () {
    // Given
    $query = 'no-results';
    $limit = 10;

    $expectedResult = [
        'local' => [],
        'api' => [],
        'total' => 0
    ];

    $formattedResult = [
        'games' => [],
        'total' => 0
    ];

    // Create a real HandledStamp with the expected result
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
    expect($result)
        ->toBe($formattedResult)
        ->and($result['games'])->toBeEmpty();
});

it('propagates message bus exceptions during search', function () {
    // Given
    $query = 'exception-test';
    $exceptionMessage = 'Message bus dispatch failed';
    $exception = new Exception($exceptionMessage);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use ($query) {
            return $searchQuery->query === $query && $searchQuery->limit === 10;
        }))
        ->willThrowException($exception);

    // When & Then
    expect(fn() => $this->apiGameSearchService->searchGames($query))
        ->toThrow(Exception::class, $exceptionMessage);
});

it('handles multiple consecutive calls to message bus', function () {
    // Given
    $firstQuery = 'first-query';
    $secondQuery = 'second-query';
    $firstApiResult = [createApiGameData($this->faker)];
    $secondApiResult = [createApiGameData($this->faker), createApiGameData($this->faker)];

    $firstExpectedResult = [
        'local' => [],
        'api' => $firstApiResult,
        'total' => count($firstApiResult)
    ];

    $secondExpectedResult = [
        'local' => [],
        'api' => $secondApiResult,
        'total' => count($secondApiResult)
    ];

    $firstFormattedResult = [
        'games' => $firstApiResult,
        'total' => count($firstApiResult)
    ];

    $secondFormattedResult = [
        'games' => $secondApiResult,
        'total' => count($secondApiResult)
    ];

    // Create HandledStamps for both calls
    $firstHandledStamp = new HandledStamp($firstExpectedResult, 'handler.service_id');
    $firstEnvelope = new Envelope(new \stdClass(), [$firstHandledStamp]);

    $secondHandledStamp = new HandledStamp($secondExpectedResult, 'handler.service_id');
    $secondEnvelope = new Envelope(new \stdClass(), [$secondHandledStamp]);

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function (SearchGamesQuery $searchQuery) use ($firstQuery, $secondQuery, $firstEnvelope, $secondEnvelope) {
            return match ($searchQuery->query) {
                $firstQuery => $firstEnvelope,
                $secondQuery => $secondEnvelope,
                default => throw new \RuntimeException('Unexpected query')
            };
        });

    // When
    $result1 = $this->apiGameSearchService->searchGames($firstQuery);
    $result2 = $this->apiGameSearchService->searchGames($secondQuery);

    // Then
    expect($result1)
        ->toBe($firstFormattedResult)
        ->and($result2)->toBe($secondFormattedResult);
});

it('verifies message bus is called exactly once per service call', function () {
    // Given
    $query = 'single-call-test';
    $expectedResult = [
        'local' => [],
        'api' => [],
        'total' => 0
    ];

    // Create a real HandledStamp with the expected result
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
    $this->apiGameSearchService->searchGames($query);

    // Then - Mock expectations are automatically verified
    expect(true)->toBeTrue(); // Assertion to ensure test runs
});

it('preserves message bus response structure exactly', function () {
    // Given
    $query = 'structure-test';
    $complexData = [
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

    $expectedResult = [
        'local' => [],
        'api' => $complexData,
        'total' => count($complexData)
    ];

    $formattedResult = [
        'games' => $complexData,
        'total' => count($complexData)
    ];

    // Create a real HandledStamp with the expected result
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
    expect($result)->toEqual($formattedResult);
    expect($result['games'][1]['nested']['deeply']['nested'])->toBe('value');
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
