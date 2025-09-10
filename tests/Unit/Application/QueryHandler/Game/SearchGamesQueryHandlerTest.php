<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\QueryHandler\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Application\QueryHandler\Game\SearchGamesQueryHandler;
use App\Application\Exception\Game\CannotUpdateGameException;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineGameRepository;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->gameRepository = $this->createMock(DoctrineGameRepository::class);
    $this->apiGameRepository = $this->createMock(ApiGameRepositoryInterface::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );

    $this->handler = new SearchGamesQueryHandler(
        $this->gameRepository,
        $this->apiGameRepository,
        $this->messageBus,
        $this->logger,
    );
});

it('returns empty results when query is empty', function () {
    // Given
    $query = new SearchGamesQuery('');

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(0);
});

it('returns empty results when query is too short', function () {
    // Given
    $query = new SearchGamesQuery('a');

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(0);
});

it('searches only local games when includeApi is false', function () {
    // Given
    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game()];
    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willReturn($localGames);

    $query = new SearchGamesQuery($query, $limit, false);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(1);
});

it('searches both local and api games when includeApi is true', function () {
    // Given
    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game(
        slug: 'zelda-test',
    )];

    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willReturn($localGames);

    $apiGames = [new ApiGame(
        name: 'Zelda',
        slug: 'zelda',
        description: 'Game description',
        imageCover: 'image.jpg',
        releaseDate: '2023-01-01',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    )];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with('zelda', 9)
        ->willReturn($apiGames);

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBe($apiGames)
        ->and($result['total'])->toBe(2);
});

it('handles exceptions during local search', function () {
    // Given
    $query = 'zelda';
    $limit = 10;

    $exception = new \Exception('Database error');
    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->with($query, $limit)
        ->willThrowException($exception);

    $this->logger
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
        releaseDate: '2023-01-01',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    )];

    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($apiGames);

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBeEmpty()
        ->and($result['api'])->toBe($apiGames)
        ->and($result['total'])->toBe(1);
});

it('handles exceptions during api search', function () {
    // Given
    $query = 'zelda';
    $limit = 10;

    $localGames = [new Game()];
    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->willReturn($localGames);

    $exception = new \Exception('API error');
    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 9)
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Erreur lors de la recherche API de jeux', [
            'query' => $query,
            'error' => 'API error',
        ]);

    $query = new SearchGamesQuery($query, $limit, true);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toBe($localGames)
        ->and($result['api'])->toBeEmpty()
        ->and($result['total'])->toBe(1);
});

it('respects the limit parameter for combined results', function () {
    // Given
    $queryString = 'zelda';
    $limit = 5;

    $localGames = [
        new Game(slug: 'zelda-test'),
        new Game(slug: 'zelda-test-2'),
        new Game(slug: 'zelda-test-3'),
    ];

    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->willReturn($localGames);

    // API should get remaining limit (5 - 3 = 2)
    $this->apiGameRepository
        ->expects($this->once())
        ->method('searchGames')
        ->with($queryString, 2)
        ->willReturn([new ApiGame(
            name: 'Zelda',
            slug: 'zelda',
            description: 'Game description',
            imageCover: 'image.jpg',
            releaseDate: '2023-01-01',
            updatedAt: new \DateTimeImmutable('now'),
            publisher: $this->publisherDto,
        ), new ApiGame(
            name: 'Zelda 2',
            slug: 'zelda-2',
            description: 'Game description 2',
            imageCover: 'image2.jpg',
            releaseDate: '2023-01-02',
            updatedAt: new \DateTimeImmutable('now'),
            publisher: $this->publisherDto,
        )]);

    $query = new SearchGamesQuery($queryString, $limit, true);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['local', 'api', 'total'])
        ->and($result['local'])->toHaveCount(3)
        ->and($result['api'])->toHaveCount(2)
        ->and($result['total'])->toBe(5);
});

it('throws CannotUpdateGameException when message bus dispatch fails', function () {
    // Given
    $queryString = 'zelda';
    $limit = 10;

    $localGame = new Game(
        slug: 'zelda-test',
    );

    $this->gameRepository
        ->method('findGamesByNameOrSlug')
        ->willReturn([$localGame]);

    $apiGame = new ApiGame(
        name: 'Zelda Test',
        slug: 'zelda-test',
        description: 'Game description',
        imageCover: 'image.jpg',
        releaseDate: '2023-01-01',
        updatedAt: new \DateTimeImmutable('2023-02-01'), // Newer than local game
        publisher: $this->publisherDto,
    );

    $this->apiGameRepository
        ->method('searchGames')
        ->willReturn([$apiGame]);

    // Mock message bus to throw exception
    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException(new \Exception('Message bus error'));

    $query = new SearchGamesQuery($queryString, $limit, true);

    // When & Then
    expect(fn() => $this->handler->__invoke($query))
        ->toThrow(CannotUpdateGameException::class);
});
