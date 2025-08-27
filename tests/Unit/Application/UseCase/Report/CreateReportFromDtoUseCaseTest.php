<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Report;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameByIdQuery;
use App\Application\UseCase\Report\CreateReportFromDtoUseCase;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;
use App\Infrastructure\Enum\ReportGameStatusEnum;
use App\Infrastructure\Persistence\Doctrine\Report\DoctrineReportRepository;
use App\Presentation\Dto\CreateReportFormInputDto;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

it('successfully executes and creates a report from DTO', function () {
    // Given
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $reportRepository = $this->createMock(DoctrineReportRepository::class);

    $useCase = new CreateReportFromDtoUseCase(
        $messageBus,
        $messageBusHelper,
        $reportRepository
    );

    $gameId = 'game-123';
    $game = new Game(
        name: 'Test Game',
        slug: 'test-game',
        description: 'A test game',
        releaseDate: '2023-01-01',
        imageCover: 'https://example.com/cover.jpg'
    );

    $createReportDto = new CreateReportFormInputDto();
    $createReportDto->gameId = $gameId;
    $createReportDto->isSwitch2Edition = true;
    $createReportDto->is60FpsPortable = true;
    $createReportDto->hasStableFrameratePortable = true;
    $createReportDto->hasResolutionImprovedPortable = false;
    $createReportDto->isNativeResolutionPortable = true;
    $createReportDto->is60FpsDocked = true;
    $createReportDto->hasStableFramerateDocked = true;
    $createReportDto->hasResolutionImprovedDocked = true;
    $createReportDto->isNativeResolutionDocked = true;
    $createReportDto->hasImprovedLoadingTimes = true;
    $createReportDto->gameStatus = ReportGameStatusEnum::OK;

    $gameEnvelope = new Envelope(new GetGameByIdQuery($gameId), [
        new HandledStamp($game, 'handler.service_id')
    ]);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameId) {
            return $query instanceof GetGameByIdQuery && $query->gameId === $gameId;
        }))
        ->willReturn($gameEnvelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($gameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn($game);

    $reportRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($report) {
            return $report instanceof Report;
        }));

    $useCase->execute($createReportDto);
});

it('handles different DTO values correctly', function () {
    // Given
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $reportRepository = $this->createMock(DoctrineReportRepository::class);

    $useCase = new CreateReportFromDtoUseCase(
        $messageBus,
        $messageBusHelper,
        $reportRepository
    );

    // Create test data with different values
    $gameId = 'game-456';
    $game = new Game(
        name: 'Another Test Game',
        slug: 'another-test-game',
        description: 'Another test game',
        releaseDate: '2023-06-01',
        imageCover: 'https://example.com/another-cover.jpg'
    );

    $createReportDto = new CreateReportFormInputDto();
    $createReportDto->gameId = $gameId;
    $createReportDto->isSwitch2Edition = false;
    $createReportDto->is60FpsPortable = false;
    $createReportDto->hasStableFrameratePortable = false;
    $createReportDto->hasResolutionImprovedPortable = true;
    $createReportDto->isNativeResolutionPortable = false;
    $createReportDto->is60FpsDocked = false;
    $createReportDto->hasStableFramerateDocked = false;
    $createReportDto->hasResolutionImprovedDocked = false;
    $createReportDto->isNativeResolutionDocked = false;
    $createReportDto->hasImprovedLoadingTimes = false;
    $createReportDto->gameStatus = ReportGameStatusEnum::BUGGED;

    $gameEnvelope = new Envelope(new GetGameByIdQuery($gameId), [
        new HandledStamp($game, 'handler.service_id')
    ]);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($gameId) {
            return $query instanceof GetGameByIdQuery && $query->gameId === $gameId;
        }))
        ->willReturn($gameEnvelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with($gameEnvelope, 'Game retrieval failed', Game::class)
        ->willReturn($game);

    $reportRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($report) {
            return $report instanceof Report;
        }));

    // When
    $useCase->execute($createReportDto);
});

it('propagates exceptions from message bus helper', function () {
    // Given
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $reportRepository = $this->createMock(DoctrineReportRepository::class);

    $useCase = new CreateReportFromDtoUseCase(
        $messageBus,
        $messageBusHelper,
        $reportRepository
    );

    $gameId = 'invalid-game-id';
    $createReportDto = new CreateReportFormInputDto();
    $createReportDto->gameId = $gameId;

    $gameEnvelope = new Envelope(new GetGameByIdQuery($gameId));

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willReturn($gameEnvelope);

    $exception = new \RuntimeException('Game not found');
    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->willThrowException($exception);

    $reportRepository
        ->expects($this->never())
        ->method('save');

    // When & Then
    expect(fn() => $useCase->execute($createReportDto))
        ->toThrow(\RuntimeException::class, 'Game not found');
});

it('propagates exceptions from repository save', function () {
    // Given
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBusHelper = $this->createMock(MessageBusHelper::class);
    $reportRepository = $this->createMock(DoctrineReportRepository::class);

    $useCase = new CreateReportFromDtoUseCase(
        $messageBus,
        $messageBusHelper,
        $reportRepository
    );

    $gameId = 'game-789';
    $game = new Game(
        name: 'Test Game',
        slug: 'test-game',
        description: 'A test game',
        releaseDate: '2023-01-01',
        imageCover: 'https://example.com/cover.jpg'
    );

    $createReportDto = new CreateReportFormInputDto();
    $createReportDto->gameId = $gameId;
    $createReportDto->isSwitch2Edition = true;
    $createReportDto->is60FpsPortable = true;
    $createReportDto->hasStableFrameratePortable = true;
    $createReportDto->hasResolutionImprovedPortable = false;
    $createReportDto->isNativeResolutionPortable = true;
    $createReportDto->is60FpsDocked = true;
    $createReportDto->hasStableFramerateDocked = true;
    $createReportDto->hasResolutionImprovedDocked = true;
    $createReportDto->isNativeResolutionDocked = true;
    $createReportDto->hasImprovedLoadingTimes = true;
    $createReportDto->gameStatus = ReportGameStatusEnum::GREAT;

    $gameEnvelope = new Envelope(new GetGameByIdQuery($gameId), [
        new HandledStamp($game, 'handler.service_id')
    ]);

    $messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willReturn($gameEnvelope);

    $messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->willReturn($game);

    $exception = new \RuntimeException('Database save failed');
    $reportRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    // When & Then
    expect(fn() => $useCase->execute($createReportDto))
        ->toThrow(\RuntimeException::class, 'Database save failed');
});
