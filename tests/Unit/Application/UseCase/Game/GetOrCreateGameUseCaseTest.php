<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Game;

use App\Application\Exception\CannotGetGameException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameBySlugQuery;
use App\Application\Query\Game\GetOrCreateGameQuery;
use App\Application\UseCase\Game\GetOrCreateGameUseCase;
use App\Domain\Factory\Game\GamePublisherFactory;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\CacheInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->cache = $this->createMock(CacheInterface::class);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );

    $this->useCase = new GetOrCreateGameUseCase(
        $this->messageBusHelper,
        $this->messageBus,
        $this->cache
    );
});


it('returns existing game when GetGameQuery finds it (early return)', function () {
    // Given
    $gameSlug = 'existing-game';
    $existingGame = new Game(
        name: 'Existing Game',
        slug: $gameSlug,
        description: 'This game already exists',
        releaseDate: '2020-01-01',
        imageCover: 'https://example.com/existing.jpg'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug), [
        new HandledStamp($existingGame, 'handler.service_id')
    ]);

    // Only GetGameQuery should be dispatched (no GetOrCreateGameQuery)
    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameSlug) {
            return $query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug;
        }))
        ->willReturn($getGameEnvelope);

    // MessageBusHelper returns the existing game
    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn($existingGame);

    // Cache should never be accessed when game exists
    $this->cache
        ->expects($this->never())
        ->method('get');

    // When
    $result = $this->useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($existingGame);
});

it('successfully executes when ApiGame found in cache', function () {
    // Given
    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/zelda.jpg',
        publisher: $this->publisherDto,
        releaseDate: '2017-03-03'
    );

    $expectedGame = new Game(slug: $gameSlug);
    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame), [
        new HandledStamp($expectedGame, 'handler.service_id')
    ]);

    // First GetGameQuery returns null (no existing game)
    // Then GetOrCreateGameQuery is dispatched
    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $apiGame, $getGameEnvelope, $getOrCreateGameEnvelope) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if ($query instanceof GetOrCreateGameQuery && $query->gameSlug === $gameSlug && $query->apiGame === $apiGame) {
                return $getOrCreateGameEnvelope;
            }
            throw new \Exception('Unexpected query type');
        });

    $this->messageBusHelper
        ->expects($this->exactly(2))
        ->method('getContentFromEnvelope')
        ->willReturnCallback(function ($envelope, $logMessage, $class) use ($getGameEnvelope, $getOrCreateGameEnvelope, $expectedGame) {
            if ($envelope === $getGameEnvelope) {
                return null;
            }
            if ($envelope === $getOrCreateGameEnvelope) {
                return $expectedGame;
            }
            throw new \Exception('Unexpected envelope');
        });

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    // When
    $result = $this->useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('throws CannotGetGameException when message bus dispatch fails', function () {
    // Given
    $gameSlug = 'mario-odyssey';
    $apiGame = new ApiGame(
        name: 'Super Mario Odyssey',
        slug: $gameSlug,
        description: 'A 3D platform game',
        imageCover: 'https://example.com/mario.jpg',
        publisher: $this->publisherDto,
        releaseDate: '2017-10-27'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $exception = new \Exception('Message bus error');

    // First GetGameQuery succeeds, but GetOrCreateGameQuery fails
    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $getGameEnvelope, $exception) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if ($query instanceof GetOrCreateGameQuery) {
                throw $exception;
            }
            throw new \Exception('Unexpected query type');
        });

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    // When & Then
    expect(fn() => $this->useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});

it('properly handles cache key format', function () {
    // Given
    $gameSlug = 'hollow-knight';
    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameSlug) {
            return $query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug;
        }))
        ->willReturn($getGameEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_hollow-knight')
        ->willReturnCallback(function ($key, $callback) {
            return $callback();
        });

    // When
    $result = $this->useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

it('handles different ApiGame scenarios', function () {
    // Given
    $gameSlug = 'hades';
    $apiGame = new ApiGame(
        name: 'Hades',
        slug: $gameSlug,
        description: 'A rogue-like dungeon crawler',
        imageCover: 'https://example.com/hades.jpg',
        publisher: $this->publisherDto,
        releaseDate: '2020-09-17'
    );

    $publisher = $this->createdPublisher = GamePublisherFactory::create($this->publisherName, $this->publisherApiId, $this->publisherWebsite);

    $expectedGame = new Game(
        name: 'Hades',
        slug: $gameSlug,
        description: 'A rogue-like dungeon crawler',
        releaseDate: '2020-09-17',
        imageCover: 'https://example.com/hades.jpg',
        publisher: $publisher,
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame));

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $apiGame, $getGameEnvelope, $getOrCreateGameEnvelope) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if (
                $query instanceof GetOrCreateGameQuery
                && $query->gameSlug === $gameSlug
                && $query->apiGame === $apiGame
                && $query->includeApi === false
            ) {
                return $getOrCreateGameEnvelope;
            }
        });

    $this->messageBusHelper
        ->expects($this->exactly(2))
        ->method('getContentFromEnvelope')
        ->willReturnCallback(function ($envelope) use ($getGameEnvelope, $getOrCreateGameEnvelope, $expectedGame) {
            if ($envelope === $getGameEnvelope) {
                return null;
            }
            if ($envelope === $getOrCreateGameEnvelope) {
                return $expectedGame;
            }
            throw new \Exception('Unexpected envelope');
        });

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When
    $result = $this->useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('handles MessageBusHelper returning null', function () {
    // Given
    $gameSlug = 'celeste';
    $apiGame = new ApiGame(
        name: 'Celeste',
        slug: $gameSlug,
        description: 'A challenging platformer',
        imageCover: 'https://example.com/celeste.jpg',
        publisher: $this->publisherDto,
        releaseDate: '2018-01-25'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame));

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $apiGame, $getGameEnvelope, $getOrCreateGameEnvelope) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if ($query instanceof GetOrCreateGameQuery && $query->gameSlug === $gameSlug && $query->apiGame === $apiGame) {
                return $getOrCreateGameEnvelope;
            }
            throw new \Exception('Unexpected query type');
        });

    $this->messageBusHelper
        ->expects($this->exactly(2))
        ->method('getContentFromEnvelope')
        ->willReturnCallback(function ($envelope, $logMessage, $class) use ($getGameEnvelope, $getOrCreateGameEnvelope) {
            if ($envelope === $getGameEnvelope) {
                return null; // No existing game found
            }
            if ($envelope === $getOrCreateGameEnvelope) {
                return null; // No handled stamp or other issue
            }
            throw new \Exception('Unexpected envelope');
        });

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When
    $result = $this->useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

it('handles RuntimeException from message bus', function () {
    // Given
    $gameSlug = 'ori-will-of-wisps';
    $apiGame = new ApiGame(
        name: 'Ori and the Will of the Wisps',
        slug: $gameSlug,
        description: 'A beautiful Metroidvania',
        imageCover: 'https://example.com/ori.jpg',
        publisher: $this->publisherDto,
        releaseDate: '2020-03-11'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $exception = new \RuntimeException('Runtime error during dispatch');

    // First GetGameQuery succeeds, but GetOrCreateGameQuery fails with RuntimeException
    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $getGameEnvelope, $exception) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if ($query instanceof GetOrCreateGameQuery) {
                throw $exception;
            }
            throw new \Exception('Unexpected query type');
        });

    // First call returns null (no existing game)
    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When & Then
    expect(fn() => $this->useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});
