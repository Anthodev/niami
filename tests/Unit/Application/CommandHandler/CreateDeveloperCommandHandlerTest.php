<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\Command\CreateDeveloperCommand;
use App\Application\CommandHandler\CreateDeveloperCommandHandler;
use App\Domain\Factory\Game\DeveloperFactory;
use App\Domain\Model\Game\Developer;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineDeveloperRepository;
use Faker\Factory;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->developerRepository = $this->createMock(DoctrineDeveloperRepository::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->developerName = $this->faker->company();
    $this->developerWebsite = $this->faker->url();
    $this->developerApiId = $this->faker->randomNumber(5);

    $this->existingDeveloper = DeveloperFactory::create(
        name: $this->developerName,
        apiId: $this->developerApiId,
        website: $this->developerWebsite
    );
});

it('successfully creates and saves developer when developer does not exist', function () {
    // Given
    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->developerName)
        ->willReturn(null);

    $this->developerRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($developer) {
            return $developer instanceof Developer
                && $developer->getName() === $this->developerName
                && $developer->getWebsite() === $this->developerWebsite
                && $developer->getApiId() === $this->developerApiId;
        }));

    $this->logger
        ->expects($this->never())
        ->method('error');

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('returns early when developer already exists', function () {
    // Given
    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->developerName)
        ->willReturn($this->existingDeveloper);

    $this->developerRepository
        ->expects($this->never())
        ->method('save');

    $this->logger
        ->expects($this->never())
        ->method('error');

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles exception during save operation', function () {
    // Given
    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->developerName)
        ->willReturn(null);

    $exception = new \Exception('Database connection failed');

    $this->developerRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Database connection failed');

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles different types of exceptions during save', function () {
    // Given
    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($this->developerName)
        ->willReturn(null);

    $exception = new \RuntimeException('Runtime error occurred');

    $this->developerRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with('Runtime error occurred');

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $this->developerName,
        website: $this->developerWebsite,
        apiId: $this->developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('creates developer using DeveloperFactory with correct parameters', function () {
    // Given
    $developerName = 'Valve Corporation';
    $developerWebsite = 'https://www.valvesoftware.com';
    $developerApiId = 123456;

    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($developerName)
        ->willReturn(null);

    $this->developerRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($developer) use ($developerName, $developerWebsite, $developerApiId) {
            return $developer instanceof Developer
                && $developer->getName() === $developerName
                && $developer->getWebsite() === $developerWebsite
                && $developer->getApiId() === $developerApiId;
        }));

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $developerName,
        website: $developerWebsite,
        apiId: $developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles command with null website', function () {
    // Given
    $developerName = 'Indie Developer';
    $developerApiId = 789012;

    $this->developerRepository
        ->expects($this->once())
        ->method('findByName')
        ->with($developerName)
        ->willReturn(null);

    $this->developerRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($developer) use ($developerName, $developerApiId) {
            return $developer instanceof Developer
                && $developer->getName() === $developerName
                && $developer->getWebsite() === null
                && $developer->getApiId() === $developerApiId;
        }));

    $handler = new CreateDeveloperCommandHandler(
        $this->developerRepository,
        $this->logger
    );

    $command = new CreateDeveloperCommand(
        name: $developerName,
        website: null,
        apiId: $developerApiId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
