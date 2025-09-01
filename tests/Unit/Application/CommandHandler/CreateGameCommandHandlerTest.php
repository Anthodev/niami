<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\Command\CreateGameCommand;
use App\Application\Command\CreateGamePublisherCommand;
use App\Application\CommandHandler\CreateGameCommandHandler;
use App\Application\Helper\MessageBusHelper;
use App\Domain\Factory\Game\GamePublisherFactory;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Game\Publisher;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineDeveloperRepository;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineGameRepository;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrinePublisherRepository;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->gameRepository = $this->createMock(DoctrineGameRepository::class);
    $this->publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $this->developerRepository = $this->createMock(DoctrineDeveloperRepository::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->createdPublisher = GamePublisherFactory::create($this->publisherName, $this->publisherApiId, $this->publisherWebsite);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );
});

it('successfully creates and saves game', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: 'zelda-breath-of-the-wild',
        releaseDate: '2017-03-03',
        imageCover: 'https://example.com/zelda.jpg',
        publisher: $this->publisherDto,
        description: 'An open-world adventure game',
    );

    $this->gameRepository
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

    // Then
    expect(true)->toBeTrue();
});

it('handles exception during save operation', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'Super Mario Odyssey',
        slug: 'mario-odyssey',
        releaseDate: '2017-10-27',
        imageCover: 'https://example.com/mario.jpg',
        publisher: $this->publisherDto,
        description: 'A 3D platform game'
    );

    $exception = new \Exception('Database connection failed');

    $this->gameRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Database connection failed');

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('properly handles command with null description', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'Metroid Dread',
        slug: 'metroid-dread',
        releaseDate: '2021-10-08',
        imageCover: 'https://example.com/metroid.jpg',
        publisher: $this->publisherDto,
        description: null,
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) {
            return $game instanceof Game
                && $game->getName() === 'Metroid Dread'
                && $game->getSlug() === 'metroid-dread'
                && $game->getDescription() === ''
                && $game->getReleaseDate() === '2021-10-08'
                && $game->getImageCover() === 'https://example.com/metroid.jpg';
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('properly handles command with non-null description', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'Hollow Knight',
        slug: 'hollow-knight',
        releaseDate: '2017-02-24',
        imageCover: 'https://example.com/hollow-knight.jpg',
        publisher: $this->publisherDto,
        description: 'A challenging 2D Metroidvania'
    );

    $this->gameRepository
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

    $this->logger
        ->expects($this->never())
        ->method('error');

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles different types of exceptions during save', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $logger = $this->createMock(LoggerInterface::class);

    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $publisherRepository,
        $this->developerRepository,
        $messageBus,
        $messageBusHelper,
        $logger,
    );

    $publisherEnvelope = new Envelope($this->createdPublisher, [new HandledStamp($this->createdPublisher, 'handler.service_id')]);

    $messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            new Envelope(new CreateGamePublisherCommand($this->publisherName, $this->publisherWebsite, $this->publisherApiId)),
            $publisherEnvelope,
        );

    $command = new CreateGameCommand(
        name: 'Celeste',
        slug: 'celeste',
        releaseDate: '2018-01-25',
        imageCover: 'https://example.com/celeste.jpg',
        publisher: $this->publisherDto,
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

it('creates game using GameFactory with correct parameters', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'Hades',
        slug: 'hades',
        releaseDate: '2020-09-17',
        imageCover: 'https://example.com/hades.jpg',
        publisher: $this->publisherDto,
        description: 'A rogue-like dungeon crawler'
    );

    $this->gameRepository
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
                && $game->getPublisher() === $this->createdPublisher
                && $game->getReports()->isEmpty();
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles command properties validation through CreateGameCommand', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->createdPublisher);

    $handler = new CreateGameCommandHandler(
        $this->gameRepository,
        $this->publisherRepository,
        $this->developerRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateGameCommand(
        name: 'Ori and the Will of the Wisps',
        slug: 'ori-will-of-wisps',
        releaseDate: '2020-03-11',
        imageCover: 'https://example.com/ori.jpg',
        publisher: $this->publisherDto,
    );

    expect($command->getName())->toBe('Ori and the Will of the Wisps')
        ->and($command->getSlug())->toBe('ori-will-of-wisps')
        ->and($command->getReleaseDate())->toBe('2020-03-11')
        ->and($command->getImageCover())->toBe('https://example.com/ori.jpg')
        ->and($command->getDescription())->toBeNull()
        ->and($command->isActive())->toBeTrue();

    $this->gameRepository
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

it('creates game and publisher using message bus when publisher does not exist', function () {
    // Given
    $gameRepository = $this->createMock(DoctrineGameRepository::class);
    $publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);

    $publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn(null);


    $handler = new CreateGameCommandHandler(
        $gameRepository,
        $publisherRepository,
        $this->developerRepository,
        $messageBus,
        $messageBusHelper,
        $this->logger,
    );

    $command = new CreateGameCommand(
        name: 'The Witcher 3: Wild Hunt',
        slug: 'witcher-3-wild-hunt',
        releaseDate: '2015-05-19',
        imageCover: 'https://example.com/witcher3.jpg',
        publisher: $this->publisherDto,
        description: 'An open world RPG',
    );

    $publisherEnvelope = new Envelope($this->createdPublisher, [new HandledStamp($this->createdPublisher, 'handler.service_id')]);

    $messageBus
        ->expects($this->exactly(2))
        ->method('dispatch')
        ->willReturnOnConsecutiveCalls(
            new Envelope(new CreateGamePublisherCommand($this->publisherName, $this->publisherWebsite, $this->publisherApiId)),
            $publisherEnvelope,
        );

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($publisherEnvelope, 'Publisher not found', Publisher::class)
        ->willReturn($this->createdPublisher);

    $gameRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($game) use ($command) {
            return $game instanceof Game
                && $game->getName() === $command->getName()
                && $game->getSlug() === $command->getSlug()
                && $game->getDescription() === $command->getDescription()
                && $game->getReleaseDate() === $command->getReleaseDate()
                && $game->getImageCover() === $command->getImageCover()
                && $game->getPublisher() === $this->createdPublisher;
        }));

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
