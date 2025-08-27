<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Game;

use App\Application\Exception\CannotGetGameException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameBySlugQuery;
use App\Application\Query\Game\GetOrCreateGameQuery;
use App\Application\UseCase\Game\GetOrCreateGameUseCase;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\CacheInterface;


it('returns existing game when GetGameQuery finds it (early return)', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

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
    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameSlug) {
            return $query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug;
        }))
        ->willReturn($getGameEnvelope);

    // MessageBusHelper returns the existing game
    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn($existingGame);

    // Cache should never be accessed when game exists
    $cache
        ->expects($this->never())
        ->method('get');

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($existingGame);
});

it('successfully executes when ApiGame found in cache', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/zelda.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-03-03'
    );

    $expectedGame = new Game(slug: $gameSlug);
    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame), [
        new HandledStamp($expectedGame, 'handler.service_id')
    ]);

    // First GetGameQuery returns null (no existing game)
    // Then GetOrCreateGameQuery is dispatched
    $messageBus
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

    $messageBusHelper
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

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('throws CannotGetGameException when message bus dispatch fails', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'mario-odyssey';
    $apiGame = new ApiGame(
        name: 'Super Mario Odyssey',
        slug: $gameSlug,
        description: 'A 3D platform game',
        imageCover: 'https://example.com/mario.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-10-27'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $exception = new \Exception('Message bus error');

    // First GetGameQuery succeeds, but GetOrCreateGameQuery fails
    $messageBus
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

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    // When & Then
    expect(fn() => $useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});

it('properly handles cache key format', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'hollow-knight';
    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameSlug) {
            return $query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug;
        }))
        ->willReturn($getGameEnvelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_hollow-knight')
        ->willReturnCallback(function ($key, $callback) {
            return $callback();
        });

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

it('handles different ApiGame scenarios', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'hades';
    $apiGame = new ApiGame(
        name: 'Hades',
        slug: $gameSlug,
        description: 'A rogue-like dungeon crawler',
        imageCover: 'https://example.com/hades.jpg',
        publisher: 'Supergiant Games',
        releaseDate: '2020-09-17'
    );

    $expectedGame = new Game(
        name: 'Hades',
        slug: $gameSlug,
        description: 'A rogue-like dungeon crawler',
        releaseDate: '2020-09-17',
        imageCover: 'https://example.com/hades.jpg'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame));

    $messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($query) use ($gameSlug, $apiGame, $getGameEnvelope, $getOrCreateGameEnvelope) {
            if ($query instanceof GetGameBySlugQuery && $query->gameSlug === $gameSlug) {
                return $getGameEnvelope;
            }
            if ($query instanceof GetOrCreateGameQuery
                && $query->gameSlug === $gameSlug
                && $query->apiGame->getName() === 'Hades'
                && $query->apiGame->getSlug() === 'hades'
                && $query->apiGame->getDescription() === 'A rogue-like dungeon crawler'
                && $query->apiGame->getImageCover() === 'https://example.com/hades.jpg'
                && $query->apiGame->getPublisher() === 'Supergiant Games'
                && $query->apiGame->getReleaseDate() === '2020-09-17') {
                return $getOrCreateGameEnvelope;
            }
            throw new \Exception('Unexpected query type or properties');
        });

    $messageBusHelper
        ->expects($this->exactly(2))
        ->method('getContentFromEnvelope')
        ->willReturnCallback(function ($envelope) use ($getGameEnvelope, $getOrCreateGameEnvelope, $expectedGame) {
            if ($envelope === $getGameEnvelope) {
                return null; // No existing game found
            }
            if ($envelope === $getOrCreateGameEnvelope) {
                return $expectedGame; // Game created/retrieved successfully
            }
            throw new \Exception('Unexpected envelope');
        });

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('handles MessageBusHelper returning null', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'celeste';
    $apiGame = new ApiGame(
        name: 'Celeste',
        slug: $gameSlug,
        description: 'A challenging platformer',
        imageCover: 'https://example.com/celeste.jpg',
        publisher: 'Maddy Makes Games',
        releaseDate: '2018-01-25'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $getOrCreateGameEnvelope = new Envelope(new GetOrCreateGameQuery($gameSlug, $apiGame));

    $messageBus
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

    $messageBusHelper
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

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

it('handles RuntimeException from message bus', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetOrCreateGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'ori-will-of-wisps';
    $apiGame = new ApiGame(
        name: 'Ori and the Will of the Wisps',
        slug: $gameSlug,
        description: 'A beautiful Metroidvania',
        imageCover: 'https://example.com/ori.jpg',
        publisher: 'Moon Studios',
        releaseDate: '2020-03-11'
    );

    $getGameEnvelope = new Envelope(new GetGameBySlugQuery($gameSlug));
    $exception = new \RuntimeException('Runtime error during dispatch');

    // First GetGameQuery succeeds, but GetOrCreateGameQuery fails with RuntimeException
    $messageBus
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
    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($getGameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn(null);

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // When & Then
    expect(fn() => $useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});
