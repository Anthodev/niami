<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\CommandHandler\Game\CreatePublisherCommandHandler;
use App\Domain\Factory\Game\GamePublisherFactory;
use App\Domain\Model\Game\Publisher;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrinePublisherRepository;
use Faker\Factory;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->publisherRepository = $this->createMock(DoctrinePublisherRepository::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->existingPublisher = GamePublisherFactory::create(
        $this->publisherName,
        $this->publisherApiId,
        $this->publisherWebsite
    );
});

it('successfully creates and saves publisher when publisher does not exist', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn(null);

    $this->publisherRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($publisher) {
            return $publisher instanceof Publisher
                && $publisher->getName() === $this->publisherName
                && $publisher->getWebsite() === $this->publisherWebsite
                && $publisher->getApiId() === $this->publisherApiId;
        }));

    $this->logger
        ->expects($this->never())
        ->method('error');

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('returns early when publisher already exists', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn($this->existingPublisher);

    $this->publisherRepository
        ->expects($this->never())
        ->method('save');

    $this->logger
        ->expects($this->never())
        ->method('error');

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

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
        ->willReturn(null);

    $exception = new \Exception('Database connection failed');

    $this->publisherRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Database connection failed');

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles different types of exceptions during save', function () {
    // Given
    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->publisherName)
        ->willReturn(null);

    $exception = new \RuntimeException('Runtime error occurred');

    $this->publisherRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Runtime error occurred');

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $this->publisherName,
        website: $this->publisherWebsite,
        apiId: $this->publisherApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('creates publisher using GamePublisherFactory with correct parameters', function () {
    // Given
    $publisherName = 'Nintendo';
    $publisherWebsite = 'https://www.nintendo.com';
    $publisherApiId = 123456;

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($publisherName)
        ->willReturn(null);

    $this->publisherRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($publisher) use ($publisherName, $publisherWebsite, $publisherApiId) {
            return $publisher instanceof Publisher
                && $publisher->getName() === $publisherName
                && $publisher->getWebsite() === $publisherWebsite
                && $publisher->getApiId() === $publisherApiId;
        }));

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $publisherName,
        website: $publisherWebsite,
        apiId: $publisherApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles command with null website', function () {
    // Given
    $publisherName = 'Indie Developer';
    $publisherApiId = 789012;

    $this->publisherRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($publisherName)
        ->willReturn(null);

    $this->publisherRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($publisher) use ($publisherName, $publisherApiId) {
            return $publisher instanceof Publisher
                && $publisher->getName() === $publisherName
                && $publisher->getWebsite() === null
                && $publisher->getApiId() === $publisherApiId;
        }));

    $handler = new CreatePublisherCommandHandler(
        $this->publisherRepository,
        $this->logger
    );

    $command = new CreatePublisherCommand(
        name: $publisherName,
        website: null,
        apiId: $publisherApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
