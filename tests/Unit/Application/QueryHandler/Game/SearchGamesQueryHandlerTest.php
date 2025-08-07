<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\QueryHandler\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Application\QueryHandler\Game\SearchGamesQueryHandler;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

test('returns empty results when query is empty', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger,
    );

    $query = new SearchGamesQuery('');

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(0);
});

test('returns empty results when query is too short', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger,
    );

    $query = new SearchGamesQuery('a');

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(0);
});

test('searches only local games when includeApi is false', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game()];
    $gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willReturn($localGames);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger,
    );

    $query = new SearchGamesQuery($query, $limit, false);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(1);
});

test('searches both local and api games when includeApi is true', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game()];
    $gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willReturn($localGames);

    $apiGames = [new ApiGame(
        name: 'Zelda',
        slug: 'zelda',
        description: 'Game description',
        imageCover: 'image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2023-01-01',
    )];

    $apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with('zelda', 9)
        ->willReturn($apiGames);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger,
    );

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBe($apiGames)
        ->and($result['total'])->toBe(2);
});

test('handles exceptions during local search', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $query = 'zelda';
    $limit = 10;

    $exception = new \Exception('Database error');
    $gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willThrowException($exception);

    $logger
        ->expects($this->once())
        ->method('error')
        ->with('Erreur lors de la recherche locale de jeux', [
            'query' => $query,
            'error' => 'Database error',
        ]);

    $apiGames = [new ApiGame(
        name: 'Zelda',
        slug: 'zelda',
        description: 'Game description',
        imageCover: 'image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2023-01-01'
    )];

    $apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($apiGames);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger
    );

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBe($apiGames)
        ->and($result['total'])->toBe(1);
});

test('handles exceptions during api search', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game()];
    $gameRepository
        ->method('findGamesByNameOrSlug')
        ->willReturn($localGames);

    $exception = new \Exception('API error');
    $apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 9)
        ->willThrowException($exception);

    $logger
        ->expects($this->once())
        ->method('error')
        ->with('Erreur lors de la recherche API de jeux', [
            'query' => $query,
            'error' => 'API error',
        ]);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger
    );

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(1);
});

test('respects the limit parameter for combined results', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $query = 'zelda';
    $limit = 2;

    $localGames = [new Game(), new Game(), new Game()];
    $gameRepository
        ->method('findGamesByNameOrSlug')
        ->willReturn($localGames);

    $apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn([new ApiGame(
            name: 'Zelda',
            slug: 'zelda',
            description: 'Game description',
            imageCover: 'image.jpg',
            publisher: 'Nintendo',
            releaseDate: '2023-01-01'
        ), new ApiGame(
            name: 'Zelda 2',
            slug: 'zelda-2',
            description: 'Game description 2',
            imageCover: 'image2.jpg',
            publisher: 'Nintendo',
            releaseDate: '2023-01-02'
        )]);

    $handler = new SearchGamesQueryHandler(
        $gameRepository,
        $apiGameRepository,
        $logger
    );

    $limit = 5;
    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toHaveCount(3)
        ->and($result['api'])->toHaveCount(2)
        ->and($result['total'])->toBe(5);
});
