<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service\Game;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\SearchGamesQuery;
use App\Application\Service\Game\ApiGameSearchService;
use App\Domain\Model\Game\ApiGame;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->apiGameSearchService = new ApiGameSearchService($this->messageBus, $this->messageBusHelper);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );
});

it('delegates searchGames call to message bus with correct parameters', function () {
    // Given
    $query = 'integration-test';
    $limit = 15;

    $expectedResult = [
        'local' => [],
        'api' => [createApiGameData($this->faker, $this->publisherDto)],
        'total' => 1
    ];
    $formattedResult = [
        'games' => $expectedResult['api'],
        'total' => $expectedResult['total']
    ];

    // Create a real HandledStamp with the expected result
    $handledStamp = new HandledStamp($expectedResult, 'handler.service_id');
    $envelope = new Envelope(new SearchGamesQuery($query), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($message) use ($query, $limit) {
            return $message instanceof SearchGamesQuery
                && $message->query === $query
                && $message->limit === $limit;
        }))
        ->willReturn($envelope);


    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Error during game search',
            null,
            'array',
        )
        ->willReturn($expectedResult);

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
    $envelope = new Envelope(new SearchGamesQuery($query), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($message) use ($query) {
            return $message instanceof SearchGamesQuery
                && $message->query === $query
                && $message->limit === 25;
        }))
        ->willReturn($envelope);


    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Error during game search',
            null,
            'array',
        )
        ->willReturn($expectedResult);

    // When
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('handles message bus returning large dataset', function () {
    // Given
    $query = 'large-dataset';
    $limit = 100;
    $largeDataset = array_map(fn() => createApiGameData($this->faker, $this->publisherDto), range(1, 100));

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
    $envelope = new Envelope(new SearchGamesQuery($query), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($message) use ($query) {
            return $message instanceof SearchGamesQuery
                && $message->query === $query
                && $message->limit === 100;
        }))
        ->willReturn($envelope);


    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Error during game search',
            null,
            'array',
        )
        ->willReturn($expectedResult);

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
        ->with($this->callback(function ($message) use ($query) {
            return $message instanceof SearchGamesQuery
                && $message->query === $query
                && $message->limit === 25;
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
    $firstApiResult = [createApiGameData($this->faker, $this->publisherDto)];
    $secondApiResult = [createApiGameData($this->faker, $this->publisherDto), createApiGameData($this->faker, $this->publisherDto)];

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
    $firstEnvelope = new Envelope(new SearchGamesQuery($firstQuery), [$firstHandledStamp]);

    $secondHandledStamp = new HandledStamp($secondExpectedResult, 'handler.service_id');
    $secondEnvelope = new Envelope(new SearchGamesQuery($secondQuery), [$secondHandledStamp]);

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->with($this->callback(function (SearchGamesQuery $searchQuery) use (
            $firstQuery,
            $secondQuery,
        ) {
            return in_array($searchQuery->query, [$firstQuery, $secondQuery]);
        }))
        ->willReturnCallback(function (SearchGamesQuery $searchQuery) use ($firstQuery, $secondQuery, $firstEnvelope, $secondEnvelope) {
            return match ($searchQuery->query) {
                $firstQuery => $firstEnvelope,
                $secondQuery => $secondEnvelope,
                default => throw new \RuntimeException('Unexpected query')
            };
        });

    $this->messageBusHelper
        ->expects($this->exactly(2))
        ->method('getContentFromEnvelope')
        ->with($this->callback(function ($envelope) use ($firstEnvelope, $secondEnvelope) {
            return in_array($envelope, [$firstEnvelope, $secondEnvelope]);
        }))
        ->willReturnCallback(function ($envelope) use (
            $firstEnvelope,
            $secondEnvelope,
            $firstExpectedResult,
            $secondExpectedResult,
        ) {
            return match ($envelope) {
                $firstEnvelope => $firstExpectedResult,
                $secondEnvelope => $secondExpectedResult,
                default => throw new \RuntimeException('Unexpected envelope'),
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
    $envelope = new Envelope(new SearchGamesQuery($query), [$handledStamp]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($message) use ($query) {
            return $message instanceof SearchGamesQuery
                && $message->query === $query
                && $message->limit === 25;
        }))
        ->willReturn($envelope);


    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Error during game search',
            null,
            'array',
        )
        ->willReturn($expectedResult);

    // When
    $this->apiGameSearchService->searchGames($query);

    // Then - Mock expectations are automatically verified
    expect(true)->toBeTrue(); // Assertion to ensure test runs
});

function createApiGameData(
    Generator $faker,
    GameCompanyDataDto $publisherDto,
): ApiGame {
    return new ApiGame(
        name: $faker->words(3, true),
        slug: $faker->slug(),
        description: $faker->paragraph(3),
        imageCover: '//images.igdb.com/igdb/image/upload/t_thumb/' . $faker->lexify('??????') . '.jpg',
        publisher: $publisherDto,
        releaseDate: date('Y-m-d', new \DateTime()->getTimestamp())
    );
}
