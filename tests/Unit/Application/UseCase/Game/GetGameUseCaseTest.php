<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Game;

use App\Application\Exception\CannotGetGameException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameQuery;
use App\Application\UseCase\Game\GetGameUseCase;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\CacheInterface;

test('returns null when no ApiGame found in cache', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache,
    );

    $gameSlug = 'zelda-breath-of-the-wild';

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug, $this->isType('callable'))
        ->willReturnCallback(function ($key, $callback) {
            return $callback();
        });

    // Message bus should not be called when no ApiGame in cache
    $messageBus
        ->expects($this->never())
        ->method('dispatch');

    // MessageBusHelper should not be called
    $messageBusHelper
        ->expects($this->never())
        ->method('getContentFromEnvelope');

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

test('successfully executes when ApiGame found in cache', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
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
    $envelope = new Envelope(new GetGameQuery($gameSlug, $apiGame), [
        new HandledStamp($expectedGame, 'handler.service_id')
    ]);

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameSlug, $apiGame) {
            return $query instanceof GetGameQuery
                && $query->gameSlug === $gameSlug
                && $query->apiGame === $apiGame;
        }))
        ->willReturn($envelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Game retrieval failed',
            Game::class,
        )
        ->willReturn($expectedGame);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

test('throws CannotGetGameException when message bus dispatch fails', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
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

    $cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $gameSlug)
        ->willReturn($apiGame);

    $exception = new \Exception('Message bus error');

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    // MessageBusHelper should not be called when dispatch fails
    $messageBusHelper
        ->expects($this->never())
        ->method('getContentFromEnvelope');

    // When & Then
    expect(fn() => $useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});

test('properly handles cache key format', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
        $messageBusHelper,
        $messageBus,
        $cache
    );

    $gameSlug = 'hollow-knight';

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

test('handles different ApiGame scenarios', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
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

    $envelope = new Envelope(new GetGameQuery($gameSlug, $apiGame));

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    // Verify GetGameQuery is created with correct ApiGame properties
    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($apiGame) {
            return $query instanceof GetGameQuery
                && $query->apiGame->getName() === 'Hades'
                && $query->apiGame->getSlug() === 'hades'
                && $query->apiGame->getDescription() === 'A rogue-like dungeon crawler'
                && $query->apiGame->getImageCover() === 'https://example.com/hades.jpg'
                && $query->apiGame->getPublisher() === 'Supergiant Games'
                && $query->apiGame->getReleaseDate() === '2020-09-17';
        }))
        ->willReturn($envelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->willReturn($expectedGame);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBe($expectedGame);
});

test('handles MessageBusHelper returning null', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
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

    $envelope = new Envelope(new GetGameQuery($gameSlug, $apiGame));

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willReturn($envelope);

    // MessageBusHelper returns null (no handled stamp or other issue)
    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $envelope,
            'Game retrieval failed',
            Game::class
        )
        ->willReturn(null);

    // When
    $result = $useCase->execute($gameSlug);

    // Then
    expect($result)->toBeNull();
});

test('handles RuntimeException from message bus', function () {
    // Given
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $cache = $this->createMock(CacheInterface::class);

    $useCase = new GetGameUseCase(
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

    $cache
        ->expects($this->once())
        ->method('get')
        ->willReturn($apiGame);

    $exception = new \RuntimeException('Runtime error during dispatch');

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $messageBusHelper
        ->expects($this->never())
        ->method('getContentFromEnvelope');

    // When & Then
    expect(fn() => $useCase->execute($gameSlug))
        ->toThrow(CannotGetGameException::class);
});
