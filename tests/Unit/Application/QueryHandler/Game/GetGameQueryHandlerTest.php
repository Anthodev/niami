<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\QueryHandler\Game;

use App\Application\Command\CreateGameCommand;
use App\Application\Exception\CannotCreateGameException;
use App\Application\Query\Game\GetGameQuery;
use App\Application\QueryHandler\Game\GetGameQueryHandler;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

test('returns game when found in repository', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger,
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-03-03'
    );

    $existingGame = new Game(slug: $gameSlug);

    $gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn($existingGame);

    // Message bus should not be called when game exists
    $messageBus
        ->expects($this->never())
        ->method('dispatch');

    $query = new GetGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($existingGame);
});

test('creates game via message bus when not found in repository', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-03-03'
    );

    $createdGame = new Game(slug: $gameSlug);

    $handledStamp = new HandledStamp($createdGame, 'handler.service_id');
    $envelope = new Envelope($handler, [$handledStamp]);

    $gameRepository
        ->expects($this->exactly(2))
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturnOnConsecutiveCalls(null, $createdGame);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($message) use ($apiGame) {
            return $message instanceof CreateGameCommand
                && $message->name === $apiGame->getName()
                && $message->slug === $apiGame->getSlug()
                && $message->releaseDate === $apiGame->getReleaseDate()
                && $message->imageCover === $apiGame->getImageCover()
                && $message->description === $apiGame->getDescription();
        }))
        ->willReturn($envelope);

    $query = new GetGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($createdGame);
});

test('throws CannotCreateGameException when message bus dispatch fails with Exception', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-03-03'
    );

    // Game not found in repository
    $gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn(null);

    $exception = new \Exception('Database error');

    // Message bus throws exception
    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    // Logger should be called with error message
    $logger
        ->expects($this->once())
        ->method('error')
        ->with('Database error');

    $query = new GetGameQuery($gameSlug, $apiGame);

    // When & Then
    expect(fn() => $handler->__invoke($query))
        ->toThrow(CannotCreateGameException::class);
});

test('throws CannotCreateGameException when message bus dispatch fails with ExceptionInterface', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);
    $exception = new CannotCreateGameException();

    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        publisher: 'Nintendo',
        releaseDate: '2017-03-03'
    );

    $gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn(null);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $query = new GetGameQuery($gameSlug, $apiGame);

    // When & Then
    $handler->__invoke($query);
})->throws(CannotCreateGameException::class);

test('properly handles different ApiGame properties', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger
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

    $createdGame = new Game(slug: $gameSlug);

    // Game not found initially, then found after creation
    $gameRepository
        ->expects($this->exactly(2))
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturnOnConsecutiveCalls(null, $createdGame);

    $handledStamp = new HandledStamp($createdGame, 'handler.service_id');
    $envelope = new Envelope($handler, [$handledStamp]);

    // Verify all ApiGame properties are passed to CreateGameCommand
    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof CreateGameCommand
                && $command->getName() === 'Super Mario Odyssey'
                && $command->getSlug() === 'mario-odyssey'
                && $command->getReleaseDate() === '2017-10-27'
                && $command->getImageCover() === 'https://example.com/mario.jpg'
                && $command->getDescription() === 'A 3D platform game';
        }))
    ->willReturn($envelope);

    $query = new GetGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($createdGame);
});

test('can be instantiated with required dependencies', function () {
    // Given
    $gameRepository = $this->createMock(GameRepositoryInterface::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    // When
    $handler = new GetGameQueryHandler(
        $messageBus,
        $gameRepository,
        $logger
    );

    // Then
    expect($handler)->toBeInstanceOf(GetGameQueryHandler::class);
});
