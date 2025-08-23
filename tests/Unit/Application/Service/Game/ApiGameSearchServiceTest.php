<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service\Game;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\SearchGamesQuery;
use App\Application\Service\Game\ApiGameSearchService;
use App\Domain\Model\Game\ApiGame;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->apiGameSearchService = new ApiGameSearchService($this->messageBus, $this->messageBusHelper);
});

it('can search games with default limit', function () {
    // Given
    $query = 'zelda';
    $limit = 25;

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

it('returns empty array when no games found', function () {
    // Given
    $query = 'nonexistent-game';
    $limit = 25;

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
    expect($result['games'])->toHaveCount(1);
});

it('handles special characters in search query', function () {
    // Given
    $query = 'game-with-special:characters!@#$%';
    $limit = 25;

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
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('handles empty search query', function () {
    // Given
    $query = '';
    $limit = 25;

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
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

it('handles unicode characters in search query', function () {
    // Given
    $query = 'ポケモン';
    $limit = 25;

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
    $result = $this->apiGameSearchService->searchGames($query);

    // Then
    expect($result)->toBe($formattedResult);
});

function createApiGameArray(Generator $faker): ApiGame
{
    return new ApiGame(
        name: $faker->words(3, true),
        slug: $faker->slug(),
        description: $faker->paragraph(3),
        imageCover: '//images.igdb.com/igdb/image/upload/t_thumb/' . $faker->lexify('??????') . '.jpg',
        publisher: $faker->company(),
        releaseDate: date('Y-m-d', new \DateTime()->getTimestamp())
    );
}
