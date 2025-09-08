<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\QueryHandler\Game;

use App\Application\Command\Game\CreateGameCommand;
use App\Application\Exception\CannotCreateGameException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetOrCreateGameQuery;
use App\Application\QueryHandler\Game\GetOrCreateGameQueryHandler;
use App\Domain\Factory\Game\GamePublisherFactory;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
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
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );

    $this->createdPublisher = GamePublisherFactory::create($this->publisherName, $this->publisherApiId, $this->publisherWebsite);

    $this->developerName = $this->faker->company();
    $this->developerWebsite = $this->faker->url();
    $this->developerApiId = $this->faker->randomNumber(5);

    $this->developerDto = new GameCompanyDataDto(
        $this->developerName,
        $this->developerWebsite,
        $this->developerApiId,
    );
});

it('returns game when found in repository', function () {
    // Given
    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger,
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        releaseDate: '2017-03-03',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $existingGame = new Game(slug: $gameSlug);

    $this->gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn($existingGame);

    // Message bus should not be called when game exists
    $this->messageBus
        ->expects($this->never())
        ->method('dispatch');

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($existingGame);
});

it('creates game via message bus when not found in repository', function () {
    // Given
    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        releaseDate: '2017-03-03',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $createdGame = new Game(slug: $gameSlug);

    $handledStamp = new HandledStamp($createdGame, 'handler.service_id');
    $envelope = new Envelope($handler, [$handledStamp]);

    $this->gameRepository
        ->expects($this->exactly(2))
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturnOnConsecutiveCalls(null, $createdGame);

    $this->messageBus
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

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($createdGame);
});

it('throws CannotCreateGameException when message bus dispatch fails with Exception', function () {
    // Given
    $this->gameRepository = $this->createMock(GameRepositoryInterface::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        releaseDate: '2017-03-03',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn(null);

    $exception = new \Exception('Database error');

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Database error');

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When & Then
    expect(fn() => $handler->__invoke($query))
        ->toThrow(CannotCreateGameException::class);
});

it('throws CannotCreateGameException when message bus dispatch fails with ExceptionInterface', function () {
    // Given
    $this->gameRepository = $this->createMock(GameRepositoryInterface::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);
    $exception = new CannotCreateGameException();

    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger
    );

    $gameSlug = 'zelda-breath-of-the-wild';
    $apiGame = new ApiGame(
        name: 'The Legend of Zelda: Breath of the Wild',
        slug: $gameSlug,
        description: 'An open-world adventure game',
        imageCover: 'https://example.com/image.jpg',
        releaseDate: '2017-03-03',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturn(null);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willThrowException($exception);

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When & Then
    $handler->__invoke($query);
})->throws(CannotCreateGameException::class);

it('properly handles different ApiGame properties', function () {
    // Given
    $this->gameRepository = $this->createMock(GameRepositoryInterface::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger
    );

    $gameSlug = 'mario-odyssey';
    $apiGame = new ApiGame(
        name: 'Super Mario Odyssey',
        slug: $gameSlug,
        description: 'A 3D platform game',
        imageCover: 'https://example.com/mario.jpg',
        releaseDate: '2017-10-27',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
    );

    $createdGame = new Game(slug: $gameSlug);

    // Game not found initially, then found after creation
    $this->gameRepository
        ->expects($this->exactly(2))
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturnOnConsecutiveCalls(null, $createdGame);

    $handledStamp = new HandledStamp($createdGame, 'handler.service_id');
    $envelope = new Envelope($handler, [$handledStamp]);

    $this->messageBus
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

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($createdGame);
});

it('creates game with developer when not found in repository', function () {
    // Given
    $handler = new GetOrCreateGameQueryHandler(
        $this->messageBus,
        $this->gameRepository,
        $this->logger
    );

    $gameSlug = 'witcher-3-wild-hunt';
    $apiGame = new ApiGame(
        name: 'The Witcher 3: Wild Hunt',
        slug: $gameSlug,
        description: 'An open-world RPG game',
        imageCover: 'https://example.com/witcher.jpg',
        releaseDate: '2015-05-19',
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $this->publisherDto,
        developer: $this->developerDto,
    );

    $createdGame = new Game(slug: $gameSlug);

    $handledStamp = new HandledStamp($createdGame, 'handler.service_id');
    $envelope = new Envelope($handler, [$handledStamp]);

    $this->gameRepository
        ->expects($this->exactly(2))
        ->method('getOneBySlugEnabledGame')
        ->with($gameSlug)
        ->willReturnOnConsecutiveCalls(null, $createdGame);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($command) use ($apiGame) {
            return $command instanceof CreateGameCommand
                && $command->name === $apiGame->getName()
                && $command->slug === $apiGame->getSlug()
                && $command->releaseDate === $apiGame->getReleaseDate()
                && $command->imageCover === $apiGame->getImageCover()
                && $command->description === $apiGame->getDescription()
                && $command->publisher === $apiGame->getPublisher()
                && $command->developer === $apiGame->getDeveloper();
        }))
        ->willReturn($envelope);

    $query = new GetOrCreateGameQuery($gameSlug, $apiGame);

    // When
    $result = $handler->__invoke($query);

    // Then
    expect($result)->toBe($createdGame);
});
