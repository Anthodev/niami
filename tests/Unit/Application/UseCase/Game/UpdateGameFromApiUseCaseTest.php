<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\Command\Game\UpdateDeveloperCommand;
use App\Application\Command\Game\UpdatePublisherCommand;
use App\Application\UseCase\Game\UpdateGameFromApiUseCase;
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
use Symfony\Component\Messenger\MessageBusInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->gameRepository = $this->createMock(DoctrineGameRepository::class);
    $this->publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $this->developerRepository = $this->createMock(DoctrineDeveloperRepository::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);

    $this->publisherName = $this->faker->company;
    $this->publisherWebsite = $this->faker->url;
    $this->publisherApiId = $this->faker->unique->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );

    $this->developerName = $this->faker->company;
    $this->developerWebsite = $this->faker->url;
    $this->developerApiId = $this->faker->unique->randomNumber(5);

    $this->developerDto = new GameCompanyDataDto(
        $this->developerName,
        $this->developerWebsite,
        $this->developerApiId,
    );

    $this->gameName = $this->faker->words(3, true);
    $this->gameSlug = $this->faker->slug;
    $this->gameDescription = $this->faker->text(200);
    $this->gameImageCover = $this->faker->imageUrl;
    $this->gameReleaseDate = $this->faker->date;

    $this->game = new Game(
        name: $this->gameName,
        slug: $this->gameSlug,
        description: $this->gameDescription,
        releaseDate: $this->gameReleaseDate,
        imageCover: $this->gameImageCover,
    );

    $this->useCase = new UpdateGameFromApiUseCase(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
    );
});

it('successfully executes with publisher creation when game has no publisher', function () {
    // Given
    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $newPublisher = new Publisher(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

    expect($this->game->getPublisher())->toBeNull();

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof CreatePublisherCommand
                && $command->name === $this->publisherName
                && $command->website === $this->publisherWebsite
                && $command->apiId === $this->publisherApiId;
        }))
        ->willReturn(new Envelope(new CreatePublisherCommand(
            name: $this->publisherName,
            website: $this->publisherWebsite,
            apiId: $this->publisherApiId,
        )));

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->publisherApiId)
        ->willReturn($newPublisher);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame, $newPublisher) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getDescription() === $apiGame->getDescription()
                && $game->getImageCover() === $apiGame->getImageCover()
                && $game->getReleaseDate() === $apiGame->getReleaseDate()
                && $game->getPublisher() === $newPublisher
                && $game->getDeveloper() === null;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('successfully executes with publisher update when game has existing publisher', function () {
    // Given
    $existingPublisher = new Publisher(
        name: 'Old Publisher Name',
        website: 'https://old-website.com',
        apiId: $this->publisherApiId,
    );

    $this->game->setPublisher($existingPublisher);

    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $updatedPublisher = new Publisher(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

    $this->publisherRepository
        ->expects($this->exactly(2))
        ->method('findByApiId')
        ->with($this->publisherApiId)
        ->willReturnOnConsecutiveCalls($existingPublisher, $updatedPublisher);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof UpdatePublisherCommand
                && $command->publisherApiId === $this->publisherApiId
                && $command->name === $this->publisherName
                && $command->website === $this->publisherWebsite;
        }))
        ->willReturn(new Envelope(new UpdatePublisherCommand(
            publisherApiId: $this->publisherApiId,
            name: $this->publisherName,
            website: $this->publisherWebsite,
        )));

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame, $updatedPublisher) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getPublisher() === $updatedPublisher;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('successfully executes with developer creation when game has no developer', function () {
    // Given
    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
        developer: $this->developerDto,
    );

    $newDeveloper = new Developer(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId,
    );

    expect($this->game->getDeveloper())->toBeNull();

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof CreateDeveloperCommand
                && $command->name === $this->developerName
                && $command->website === $this->developerWebsite
                && $command->apiId === $this->developerApiId;
        }))
        ->willReturn(new Envelope(new CreateDeveloperCommand(
            name: $this->developerName,
            website: $this->developerWebsite,
            apiId: $this->developerApiId
        )));

    $this->developerRepository
        ->expects($this->once())
        ->method('findByApiId')
        ->with($this->developerApiId)
        ->willReturn($newDeveloper);

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame, $newDeveloper) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getDeveloper() === $newDeveloper
                && $game->getPublisher() === null;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('successfully executes with developer update when game has existing developer', function () {
    // Given
    $existingDeveloper = new Developer(
        name: 'Old Developer Name',
        website: 'https://old-dev-website.com',
        apiId: $this->developerApiId
    );

    $this->game->setDeveloper($existingDeveloper);

    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
        developer: $this->developerDto,
    );

    $updatedDeveloper = new Developer(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId
    );

    $this->developerRepository
        ->expects($this->exactly(2))
        ->method('findByApiId')
        ->with($this->developerApiId)
        ->willReturnOnConsecutiveCalls($existingDeveloper, $updatedDeveloper);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) {
            return $command instanceof UpdateDeveloperCommand
                && $command->developerApiId === $this->developerApiId
                && $command->name === $this->developerName
                && $command->website === $this->developerWebsite;
        }))
        ->willReturn(new Envelope(new UpdateDeveloperCommand(
            developerApiId: $this->developerApiId,
            name: $this->developerName,
            website: $this->developerWebsite
        )));

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame, $updatedDeveloper) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getDeveloper() === $updatedDeveloper;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('successfully executes with both publisher and developer updates', function () {
    // Given
    $existingPublisher = new Publisher(
        name: 'Old Publisher', website: 'https://old-pub.com', apiId: $this->publisherApiId
    );
    $existingDeveloper = new Developer(
        name: 'Old Developer', website: 'https://old-dev.com', apiId: $this->developerApiId
    );

    $this->game->setPublisher($existingPublisher);
    $this->game->setDeveloper($existingDeveloper);

    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
        developer: $this->developerDto,
    );

    $updatedPublisher = new Publisher(
        name: $this->publisherName, website: $this->publisherWebsite, apiId: $this->publisherApiId
    );
    $updatedDeveloper = new Developer(
        name: $this->developerName, website: $this->developerWebsite, apiId: $this->developerApiId
    );

    $this->publisherRepository
        ->expects($this->exactly(2))
        ->method('findByApiId')
        ->with($this->publisherApiId)
        ->willReturnOnConsecutiveCalls($existingPublisher, $updatedPublisher);

    $this->developerRepository
        ->expects($this->exactly(2))
        ->method('findByApiId')
        ->with($this->developerApiId)
        ->willReturnOnConsecutiveCalls($existingDeveloper, $updatedDeveloper);

    $this->messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnCallback(function ($command) {
            if ($command instanceof UpdatePublisherCommand) {
                expect($command->publisherApiId)
                    ->toBe($this->publisherApiId)
                    ->and($command->name)->toBe($this->publisherName)
                    ->and($command->website)->toBe($this->publisherWebsite);
                return new Envelope(new UpdatePublisherCommand(
                    publisherApiId: $this->publisherApiId,
                    name: $this->publisherName,
                    website: $this->publisherWebsite,
                ));
            }
            if ($command instanceof UpdateDeveloperCommand) {
                expect($command->developerApiId)
                    ->toBe($this->developerApiId)
                    ->and($command->name)->toBe($this->developerName)
                    ->and($command->website)->toBe($this->developerWebsite);
                return new Envelope(new UpdateDeveloperCommand(
                    developerApiId: $this->developerApiId,
                    name: $this->developerName,
                    website: $this->developerWebsite,
                ));
            }
            throw new \Exception('Unexpected command type');
        });

    // GameRepository should update the game with both publisher and developer
    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame, $updatedPublisher, $updatedDeveloper) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getDescription() === $apiGame->getDescription()
                && $game->getImageCover() === $apiGame->getImageCover()
                && $game->getReleaseDate() === $apiGame->getReleaseDate()
                && $game->getPublisher() === $updatedPublisher
                && $game->getDeveloper() === $updatedDeveloper;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('handles ApiGame with null publisher and developer', function () {
    // Given
    $apiGame = new ApiGame(
        name: 'Updated Game Name',
        slug: $this->gameSlug,
        description: 'Updated description',
        imageCover: 'https://example.com/updated.jpg',
        releaseDate: '2023-05-15',
        updatedAt: new \DateTimeImmutable('now'),
    );

    $this->messageBus
        ->expects($this->never())
        ->method('dispatch');

    $this->publisherRepository
        ->expects($this->never())
        ->method('findByApiId');

    $this->developerRepository
        ->expects($this->never())
        ->method('findByApiId');

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($apiGame) {
            return $game instanceof Game
                && $game->getName() === $apiGame->getName()
                && $game->getDescription() === $apiGame->getDescription()
                && $game->getImageCover() === $apiGame->getImageCover()
                && $game->getReleaseDate() === $apiGame->getReleaseDate()
                && $game->getPublisher() === null
                && $game->getDeveloper() === null;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect(true)->toBeTrue();
});

it('updates all game properties correctly', function () {
    // Given
    $newName = 'Completely New Game Name';
    $newDescription = 'Completely new description for the game';
    $newImageCover = 'https://example.com/new-cover.jpg';
    $newReleaseDate = '2024-01-01';

    $apiGame = new ApiGame(
        name: $newName,
        slug: $this->gameSlug,
        description: $newDescription,
        imageCover: $newImageCover,
        releaseDate: $newReleaseDate,
        updatedAt: new \DateTimeImmutable('now'),
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('update')
        ->with($this->callback(function ($game) use ($newName, $newDescription, $newImageCover, $newReleaseDate) {
            return $game instanceof Game
                && $game->getName() === $newName
                && $game->getDescription() === $newDescription
                && $game->getImageCover() === $newImageCover
                && $game->getReleaseDate() === $newReleaseDate;
        }));

    // When
    $this->useCase->execute($this->game, $apiGame);

    // Then
    expect($this->game->getName())
        ->toBe($newName)
        ->and($this->game->getDescription())->toBe($newDescription)
        ->and($this->game->getImageCover())->toBe($newImageCover)
        ->and($this->game->getReleaseDate())->toBe($newReleaseDate);
});
