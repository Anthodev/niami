<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\Command\Game\UpdateDeveloperCommand;
use App\Application\Command\Game\UpdateGameFromApiCommand;
use App\Application\Command\Game\UpdatePublisherCommand;
use App\Application\CommandHandler\Game\UpdateGameFromApiCommandHandler;
use App\Application\Exception\Game\CannotCreateDeveloperException;
use App\Application\Exception\Game\CannotCreatePublisherException;
use App\Application\Exception\Game\CannotUpdateDeveloperException;
use App\Application\Exception\Game\CannotUpdateGameException;
use App\Application\Exception\Game\CannotUpdatePublisherException;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Developer;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Game\Publisher;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineDeveloperRepository;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineGameRepository;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrinePublisherRepository;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->gameRepository = $this->createMock(DoctrineGameRepository::class);
    $this->publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $this->developerRepository = $this->createMock(DoctrineDeveloperRepository::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);

    $this->game = $this->createMock(Game::class);
    $this->publisher = $this->createMock(Publisher::class);
    $this->developer = $this->createMock(Developer::class);

    $this->publisherDto = new GameCompanyDataDto(
        name: $this->faker->company(),
        website: $this->faker->url(),
        apiId: $this->faker->randomNumber(5),
    );

    $this->developerDto = new GameCompanyDataDto(
        name: $this->faker->company(),
        website: $this->faker->url(),
        apiId: $this->faker->randomNumber(5),
    );

    $this->apiGame = new ApiGame(
        name: $this->faker->words(3, true),
        slug: $this->faker->slug(),
        description: $this->faker->paragraph(),
        imageCover: $this->faker->imageUrl(),
        releaseDate: $this->faker->date(),
        updatedAt: new \DateTimeImmutable(),
        publisher: $this->publisherDto,
        developer: $this->developerDto,
    );
});

it('successfully updates game when publisher and developer need to be created', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn(null);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn(null);

    $createPublisherEnvelope = new Envelope(
        new CreatePublisherCommand($this->publisherDto->name, $this->publisherDto->website, $this->publisherDto->apiId),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $createDeveloperEnvelope = new Envelope(
        new CreateDeveloperCommand($this->developerDto->name, $this->developerDto->website, $this->developerDto->apiId),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            $createPublisherEnvelope,
            $createDeveloperEnvelope
        );

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherDto->apiId)
        ->willReturn($this->publisher);

    $this->developerRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->developerDto->apiId)
        ->willReturn($this->developer);

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($this->apiGame->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($this->apiGame->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($this->apiGame->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($this->apiGame->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('successfully updates game when publisher and developer need to be updated', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->publisher
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->publisherDto->apiId);

    $this->developer
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->developerDto->apiId);

    $updatePublisherEnvelope = new Envelope(
        new UpdatePublisherCommand($this->publisherDto->apiId, $this->publisherDto->name, $this->publisherDto->website),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $updateDeveloperEnvelope = new Envelope(
        new UpdateDeveloperCommand($this->developerDto->apiId, $this->developerDto->name, $this->developerDto->website),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            $updatePublisherEnvelope,
            $updateDeveloperEnvelope
        );

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherDto->apiId)
        ->willReturn($this->publisher);

    $this->developerRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->developerDto->apiId)
        ->willReturn($this->developer);

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($this->apiGame->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($this->apiGame->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($this->apiGame->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($this->apiGame->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('successfully updates game when apiGame has null publisher and developer', function () {
    // Given
    $apiGameWithNullCompanies = new ApiGame(
        name: $this->faker->words(3, true),
        slug: $this->faker->slug(),
        description: $this->faker->paragraph(),
        imageCover: $this->faker->imageUrl(),
        releaseDate: $this->faker->date(),
        updatedAt: new \DateTimeImmutable(),
        publisher: null,
        developer: null,
    );

    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->messageBus
        ->expects($this->never())
        ->method('dispatch');

    $this->publisherRepository
        ->expects($this->never())
        ->method('findByApiId');

    $this->developerRepository
        ->expects($this->never())
        ->method('findByApiId');

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($apiGameWithNullCompanies->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($apiGameWithNullCompanies->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($apiGameWithNullCompanies->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($apiGameWithNullCompanies->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $apiGameWithNullCompanies);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('throws CannotCreatePublisherException when publisher creation fails', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn(null);

    $exception = $this->createMock(ExceptionInterface::class);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotCreatePublisherException::class);
});

it('throws CannotUpdatePublisherException when publisher update fails', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->publisher
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->publisherDto->apiId);

    $exception = $this->createMock(ExceptionInterface::class);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotUpdatePublisherException::class);
});

it('throws CannotCreateDeveloperException when developer creation fails', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn(null);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn(null);

    $createPublisherEnvelope = new Envelope(
        new CreatePublisherCommand($this->publisherDto->name, $this->publisherDto->website, $this->publisherDto->apiId),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $exception = $this->createMock(ExceptionInterface::class);

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            $createPublisherEnvelope,
            $this->throwException($exception)
        );

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherDto->apiId)
        ->willReturn($this->publisher);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotCreateDeveloperException::class);
});

it('throws CannotUpdateDeveloperException when developer update fails', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->publisher
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->publisherDto->apiId);

    $this->developer
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->developerDto->apiId);

    $updatePublisherEnvelope = new Envelope(
        new UpdatePublisherCommand($this->publisherDto->apiId, $this->publisherDto->name, $this->publisherDto->website),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $exception = $this->createMock(ExceptionInterface::class);

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            $updatePublisherEnvelope,
            $this->throwException($exception)
        );

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherDto->apiId)
        ->willReturn($this->publisher);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotUpdateDeveloperException::class);
});

it('throws CannotUpdateGameException when game repository update fails', function () {
    // Given
    $apiGameWithNullCompanies = new ApiGame(
        name: $this->faker->words(3, true),
        slug: $this->faker->slug(),
        description: $this->faker->paragraph(),
        imageCover: $this->faker->imageUrl(),
        releaseDate: $this->faker->date(),
        updatedAt: new \DateTimeImmutable(),
        publisher: null,
        developer: null,
    );

    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($apiGameWithNullCompanies->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($apiGameWithNullCompanies->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($apiGameWithNullCompanies->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($apiGameWithNullCompanies->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $exception = new \Exception('Database connection failed');

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game)
        ->willThrowException($exception);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $apiGameWithNullCompanies);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotUpdateGameException::class);
});

it('handles different types of exceptions during game repository update', function () {
    // Given
    $apiGameWithNullCompanies = new ApiGame(
        name: $this->faker->words(3, true),
        slug: $this->faker->slug(),
        description: $this->faker->paragraph(),
        imageCover: $this->faker->imageUrl(),
        releaseDate: $this->faker->date(),
        updatedAt: new \DateTimeImmutable(),
        publisher: null,
        developer: null,
    );

    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($apiGameWithNullCompanies->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($apiGameWithNullCompanies->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($apiGameWithNullCompanies->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($apiGameWithNullCompanies->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $exception = new \RuntimeException('Runtime error occurred');

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game)
        ->willThrowException($exception);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $apiGameWithNullCompanies);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(CannotUpdateGameException::class);
});

it('handles mixed scenarios with publisher creation and developer update', function () {
    // Given
    $this->game
        ->expects($this->once())
        ->method('getPublisher')
        ->willReturn(null);

    $this->game
        ->expects($this->once())
        ->method('getDeveloper')
        ->willReturn($this->developer);

    $this->developer
        ->expects($this->once())
        ->method('getApiId')
        ->willReturn($this->developerDto->apiId);

    $createPublisherEnvelope = new Envelope(
        new CreatePublisherCommand($this->publisherDto->name, $this->publisherDto->website, $this->publisherDto->apiId),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $updateDeveloperEnvelope = new Envelope(
        new UpdateDeveloperCommand($this->developerDto->apiId, $this->developerDto->name, $this->developerDto->website),
        [new HandledStamp(true, 'handler.service_id')]
    );

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            $createPublisherEnvelope,
            $updateDeveloperEnvelope
        );

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherDto->apiId)
        ->willReturn($this->publisher);

    $this->developerRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->developerDto->apiId)
        ->willReturn($this->developer);

    $this->game
        ->expects($this->once())
        ->method('setName')
        ->with($this->apiGame->getName());

    $this->game
        ->expects($this->once())
        ->method('setDescription')
        ->with($this->apiGame->getDescription());

    $this->game
        ->expects($this->once())
        ->method('setImageCover')
        ->with($this->apiGame->getImageCover());

    $this->game
        ->expects($this->once())
        ->method('setReleaseDate')
        ->with($this->apiGame->getReleaseDate());

    $this->game
        ->expects($this->once())
        ->method('setPublisher')
        ->with($this->publisher);

    $this->game
        ->expects($this->once())
        ->method('setDeveloper')
        ->with($this->developer);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->game);

    $handler = new UpdateGameFromApiCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );

    $command = new UpdateGameFromApiCommand($this->game, $this->apiGame);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
