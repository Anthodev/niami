<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateGameCommand;
use App\Application\Command\Game\CreateGameWithCacheCheckCommand;
use App\Application\CommandHandler\Game\CreateGameWithCacheCheckCommandHandler;
use App\Application\Exception\Game\CannotCreateGameException;
use App\Domain\Model\Game\ApiGame;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\CacheInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->cache = $this->createMock(CacheInterface::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->gameSlug = $this->faker->slug();
    $this->gameName = $this->faker->words(3, true);
    $this->gameDescription = $this->faker->paragraph();
    $this->gameImageCover = $this->faker->imageUrl();
    $this->gameReleaseDate = $this->faker->date();

    $this->publisher = new GameCompanyDataDto(
        name: $this->faker->company(),
        website: $this->faker->url(),
        apiId: $this->faker->randomNumber(5),
    );

    $this->developer = new GameCompanyDataDto(
        name: $this->faker->company(),
        website: $this->faker->url(),
        apiId: $this->faker->randomNumber(5),
    );

    $this->apiGame = new ApiGame(
        name: $this->gameName,
        slug: $this->gameSlug,
        description: $this->gameDescription,
        imageCover: $this->gameImageCover,
        releaseDate: $this->gameReleaseDate,
        updatedAt: new \DateTimeImmutable(),
        publisher: $this->publisher,
        developer: $this->developer,
    );
});

it('successfully creates game when ApiGame exists in cache', function () {
    // Given
    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $this->gameSlug, $this->anything())
        ->willReturn($this->apiGame);

    $createGameCommand = new CreateGameCommand(
        name: $this->gameName,
        slug: $this->gameSlug,
        releaseDate: $this->gameReleaseDate,
        imageCover: $this->gameImageCover,
        publisher: $this->publisher,
        developer: $this->developer,
        description: $this->gameDescription,
    );

    $envelope = new Envelope($createGameCommand, [new HandledStamp(true, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof CreateGameCommand
                && $command->getName() === $this->gameName
                && $command->getSlug() === $this->gameSlug
                && $command->getDescription() === $this->gameDescription
                && $command->getImageCover() === $this->gameImageCover
                && $command->getReleaseDate() === $this->gameReleaseDate
                && $command->getPublisher() === $this->publisher
                && $command->getDeveloper() === $this->developer;
        }))
        ->willReturn($envelope);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand($this->gameSlug);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('returns early when ApiGame is not found in cache', function () {
    // Given
    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $this->gameSlug, $this->anything())
        ->willReturn(null);

    $this->messageBus
        ->expects($this->never())
        ->method('dispatch');

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand($this->gameSlug);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles exception during message bus dispatch', function () {
    // Given
    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $this->gameSlug, $this->anything())
        ->willReturn($this->apiGame);

    $exception = new \Exception('Message bus error');

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand($this->gameSlug);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotCreateGameException::class);
});

it('handles different types of exceptions during message bus dispatch', function () {
    // Given
    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $this->gameSlug, $this->anything())
        ->willReturn($this->apiGame);

    $exception = new \RuntimeException('Runtime error occurred');

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand($this->gameSlug);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotCreateGameException::class);
});

it('creates CreateGameCommand with correct parameters from ApiGame', function () {
    // Given
    $customPublisher = new GameCompanyDataDto(
        name: 'Custom Publisher',
        website: 'https://custompublisher.com',
        apiId: 123456,
    );

    $customDeveloper = new GameCompanyDataDto(
        name: 'Custom Developer',
        website: 'https://customdeveloper.com',
        apiId: 789012,
    );

    $customApiGame = new ApiGame(
        name: 'Test Game Name',
        slug: 'test-game-slug',
        description: 'Test game description',
        imageCover: 'https://example.com/image.jpg',
        releaseDate: '2024-01-01',
        updatedAt: new \DateTimeImmutable(),
        publisher: $customPublisher,
        developer: $customDeveloper,
    );

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_test-game-slug', $this->anything())
        ->willReturn($customApiGame);

    $createGameCommand = new CreateGameCommand(
        name: 'Test Game Name',
        slug: 'test-game-slug',
        releaseDate: '2024-01-01',
        imageCover: 'https://example.com/image.jpg',
        publisher: $customPublisher,
        developer: $customDeveloper,
        description: 'Test game description',
    );

    $envelope = new Envelope($createGameCommand, [new HandledStamp(true, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) use ($customPublisher, $customDeveloper) {
            return $command instanceof CreateGameCommand
                && $command->getName() === 'Test Game Name'
                && $command->getSlug() === 'test-game-slug'
                && $command->getDescription() === 'Test game description'
                && $command->getImageCover() === 'https://example.com/image.jpg'
                && $command->getReleaseDate() === '2024-01-01'
                && $command->getPublisher() === $customPublisher
                && $command->getDeveloper() === $customDeveloper;
        }))
        ->willReturn($envelope);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand('test-game-slug');

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles ApiGame with null publisher and developer', function () {
    // Given
    $apiGameWithNullCompanies = new ApiGame(
        name: 'Indie Game',
        slug: 'indie-game-slug',
        description: 'An indie game',
        imageCover: 'https://example.com/indie.jpg',
        releaseDate: '2023-12-31',
        updatedAt: new \DateTimeImmutable(),
        publisher: null,
        developer: null,
    );

    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_indie-game-slug', $this->anything())
        ->willReturn($apiGameWithNullCompanies);

    $createGameCommand = new CreateGameCommand(
        name: 'Indie Game',
        slug: 'indie-game-slug',
        releaseDate: '2023-12-31',
        imageCover: 'https://example.com/indie.jpg',
        publisher: null,
        developer: null,
        description: 'An indie game',
    );

    $envelope = new Envelope($createGameCommand, [new HandledStamp(true, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof CreateGameCommand
                && $command->getName() === 'Indie Game'
                && $command->getSlug() === 'indie-game-slug'
                && $command->getDescription() === 'An indie game'
                && $command->getImageCover() === 'https://example.com/indie.jpg'
                && $command->getReleaseDate() === '2023-12-31'
                && $command->getPublisher() === null
                && $command->getDeveloper() === null;
        }))
        ->willReturn($envelope);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand('indie-game-slug');

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles ExceptionInterface during message bus dispatch', function () {
    // Given
    $this->cache
        ->expects($this->once())
        ->method('get')
        ->with('api_game_' . $this->gameSlug, $this->anything())
        ->willReturn($this->apiGame);

    $exception = $this->createMock(ExceptionInterface::class);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $handler = new CreateGameWithCacheCheckCommandHandler(
        $this->cache,
        $this->messageBus,
        $this->logger,
    );

    $command = new CreateGameWithCacheCheckCommand($this->gameSlug);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotCreateGameException::class);
});
