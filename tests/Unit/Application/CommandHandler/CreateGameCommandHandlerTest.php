<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\Command\CreateGameCommand;
use App\Application\CommandHandler\CreateGameCommandHandler;
use App\Domain\Model\Game\Game;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineGameRepository;
use Psr\Log\LoggerInterface;

test('successfully creates and saves game', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: 'zelda-breath-of-the-wild',
        releaseDate: '2017-03-03',
        imageCover: 'https://example.com/zelda.jpg',
        description: 'An open-world adventure game'
    );

    // Expect repository save to be called once with a Game instance
    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) use ($command) {
            return $game instanceof Game
                && $game->getName() === $command->getName()
                && $game->getSlug() === $command->getSlug()
                && $game->getDescription() === $command->getDescription()
                && $game->getReleaseDate() === $command->getReleaseDate()
                && $game->getImageCover() === $command->getImageCover();
        }));

    // When
    $handler->__invoke($command);

    // Then - no exception should be thrown and method completes successfully
    expect(true)->toBeTrue();
});

test('handles exception during save operation', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Super Mario Odyssey',
        slug: 'mario-odyssey',
        releaseDate: '2017-10-27',
        imageCover: 'https://example.com/mario.jpg',
        description: 'A 3D platform game'
    );

    $exception = new \Exception('Database connection failed');

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $logger
        ->expects($this->once())
        ->method('error')
        ->with('Database connection failed');

    // When
    $handler->__invoke($command);

    // Then - exception should be caught and logged, but not re-thrown
    expect(true)->toBeTrue();
});

test('properly handles command with null description', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Metroid Dread',
        slug: 'metroid-dread',
        releaseDate: '2021-10-08',
        imageCover: 'https://example.com/metroid.jpg',
        description: null
    );

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) {
            return $game instanceof Game
                && $game->getName() === 'Metroid Dread'
                && $game->getSlug() === 'metroid-dread'
                && $game->getDescription() === '' // null description becomes empty string
                && $game->getReleaseDate() === '2021-10-08'
                && $game->getImageCover() === 'https://example.com/metroid.jpg';
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

test('properly handles command with non-null description', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Hollow Knight',
        slug: 'hollow-knight',
        releaseDate: '2017-02-24',
        imageCover: 'https://example.com/hollow-knight.jpg',
        description: 'A challenging 2D Metroidvania'
    );

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) {
            return $game instanceof Game
                && $game->getName() === 'Hollow Knight'
                && $game->getSlug() === 'hollow-knight'
                && $game->getDescription() === 'A challenging 2D Metroidvania'
                && $game->getReleaseDate() === '2017-02-24'
                && $game->getImageCover() === 'https://example.com/hollow-knight.jpg';
        }));

    $logger
        ->expects($this->never())
        ->method('error');

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

test('handles different types of exceptions during save', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Celeste',
        slug: 'celeste',
        releaseDate: '2018-01-25',
        imageCover: 'https://example.com/celeste.jpg',
        description: 'A challenging platformer'
    );

    $exception = new \RuntimeException('Runtime error occurred');

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $logger
        ->expects($this->once())
        ->method('error')
        ->with('Runtime error occurred');

    // When
    $handler->__invoke($command);

    // Then - should handle any type of exception gracefully
    expect(true)->toBeTrue();
});

test('creates game using GameFactory with correct parameters', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Hades',
        slug: 'hades',
        releaseDate: '2020-09-17',
        imageCover: 'https://example.com/hades.jpg',
        description: 'A rogue-like dungeon crawler'
    );

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) {
            return $game instanceof Game
                && $game->getName() === 'Hades'
                && $game->getSlug() === 'hades'
                && $game->getDescription() === 'A rogue-like dungeon crawler'
                && $game->getReleaseDate() === '2020-09-17'
                && $game->getImageCover() === 'https://example.com/hades.jpg'
                && $game->isPatched() === false
                && $game->isActive() === true
                && $game->getPublisher() === null
                && $game->getReports()->isEmpty();
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

test('can be instantiated with required dependencies', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    // When
    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    // Then
    expect($handler)->toBeInstanceOf(CreateGameCommandHandler::class);
});

test('handles command properties validation through CreateGameCommand', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $logger
    );

    $command = new CreateGameCommand(
        name: 'Ori and the Will of the Wisps',
        slug: 'ori-will-of-wisps',
        releaseDate: '2020-03-11',
        imageCover: 'https://example.com/ori.jpg'
    );

    expect($command->getName())->toBe('Ori and the Will of the Wisps')
        ->and($command->getSlug())->toBe('ori-will-of-wisps')
        ->and($command->getReleaseDate())->toBe('2020-03-11')
        ->and($command->getImageCover())->toBe('https://example.com/ori.jpg')
        ->and($command->getDescription())->toBeNull()
        ->and($command->isActive())->toBeTrue();

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) {
            return $game instanceof Game
                && $game->getName() === 'Ori and the Will of the Wisps'
                && $game->getSlug() === 'ori-will-of-wisps'
                && $game->getDescription() === '' // null becomes empty string
                && $game->getReleaseDate() === '2020-03-11'
                && $game->getImageCover() === 'https://example.com/ori.jpg';
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
