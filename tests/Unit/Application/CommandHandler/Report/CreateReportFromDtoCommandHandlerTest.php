<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler\Report;

use App\Application\Command\Report\CreateReportFromDtoCommand;
use App\Application\CommandHandler\Report\CreateReportFromDtoCommandHandler;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;
use App\Infrastructure\Enum\ReportGameStatusEnum;
use App\Infrastructure\Persistence\Doctrine\Game\Repository\DoctrineGameRepository;
use App\Infrastructure\Persistence\Doctrine\Report\DoctrineReportRepository;
use App\Presentation\Dto\CreateReportFormInputDto;
use Faker\Factory;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->reportRepository = $this->createMock(DoctrineReportRepository::class);
    $this->gameRepository = $this->createMock(DoctrineGameRepository::class);

    $this->gameId = $this->faker->uuid();
    $this->gameSlug = $this->faker->slug();

    $this->game = $this->createMock(Game::class);
    $this->report = $this->createMock(Report::class);

    $this->createReportDto = new CreateReportFormInputDto(
        gameId: $this->gameId,
        gameSlug: $this->gameSlug,
        gameStatus: ReportGameStatusEnum::OK,
        is60FpsPortable: true,
        hasStableFrameratePortable: true,
        hasResolutionImprovedPortable: false,
        isNativeResolutionPortable: false,
        is60FpsDocked: true,
        hasStableFramerateDocked: true,
        hasResolutionImprovedDocked: true,
        isNativeResolutionDocked: true,
        hasImprovedLoadingTimes: true,
        isSwitch2Edition: false,
    );
});

it('successfully creates and saves report when game exists', function () {
    // Given
    $this->gameRepository
        ->expects($this->once())
        ->method('getOneByIdEnabledGame')
        ->with($this->gameId)
        ->willReturn($this->game);

    $this->reportRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($report) {
            return $report instanceof Report
                && $report->is60FpsPortable() === $this->createReportDto->is60FpsPortable
                && $report->isHasStableFrameratePortable() === $this->createReportDto->hasStableFrameratePortable
                && $report->isHasResolutionImprovedPortable() === $this->createReportDto->hasResolutionImprovedPortable
                && $report->isNativeResolutionPortable() === $this->createReportDto->isNativeResolutionPortable
                && $report->is60FpsDocked() === $this->createReportDto->is60FpsDocked
                && $report->isHasStableFramerateDocked() === $this->createReportDto->hasStableFramerateDocked
                && $report->isHasResolutionImprovedDocked() === $this->createReportDto->hasResolutionImprovedDocked
                && $report->isNativeResolutionDocked() === $this->createReportDto->isNativeResolutionDocked
                && $report->isHasImprovedLoadingTimes() === $this->createReportDto->hasImprovedLoadingTimes
                && $report->isSwitch2Edition() === $this->createReportDto->isSwitch2Edition
                && $report->getGameStatus() === $this->createReportDto->gameStatus;
        }));

    $handler = new CreateReportFromDtoCommandHandler(
        $this->reportRepository,
        $this->gameRepository,
    );

    $command = new CreateReportFromDtoCommand($this->createReportDto);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles exception when game is not found', function () {
    // Given
    $this->gameRepository
        ->expects($this->once())
        ->method('getOneByIdEnabledGame')
        ->with($this->gameId)
        ->willReturn(null);

    $this->reportRepository
        ->expects($this->never())
        ->method('save');

    $handler = new CreateReportFromDtoCommandHandler(
        $this->reportRepository,
        $this->gameRepository,
    );

    $command = new CreateReportFromDtoCommand($this->createReportDto);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(\TypeError::class);
});

it('handles exception during save operation', function () {
    // Given
    $this->gameRepository
        ->expects($this->once())
        ->method('getOneByIdEnabledGame')
        ->with($this->gameId)
        ->willReturn($this->game);

    $exception = new \Exception('Database connection failed');

    $this->reportRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $handler = new CreateReportFromDtoCommandHandler(
        $this->reportRepository,
        $this->gameRepository
    );

    $command = new CreateReportFromDtoCommand($this->createReportDto);

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(\Exception::class);
});

it('creates report with correct parameters from DTO', function () {
    // Given
    $customDto = new CreateReportFormInputDto(
        gameId: 'test-game-id',
        gameSlug: 'test-game-slug',
        gameStatus: ReportGameStatusEnum::BUGGED,
        is60FpsPortable: false,
        hasStableFrameratePortable: false,
        hasResolutionImprovedPortable: true,
        isNativeResolutionPortable: true,
        is60FpsDocked: false,
        hasStableFramerateDocked: false,
        hasResolutionImprovedDocked: true,
        isNativeResolutionDocked: false,
        hasImprovedLoadingTimes: false,
        isSwitch2Edition: true,
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('getOneByIdEnabledGame')
        ->with('test-game-id')
        ->willReturn($this->game);

    $this->reportRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($report) use ($customDto) {
            return $report instanceof Report
                && $report->is60FpsPortable() === $customDto->is60FpsPortable
                && $report->isHasStableFrameratePortable() === $customDto->hasStableFrameratePortable
                && $report->isHasResolutionImprovedPortable() === $customDto->hasResolutionImprovedPortable
                && $report->isNativeResolutionPortable() === $customDto->isNativeResolutionPortable
                && $report->is60FpsDocked() === $customDto->is60FpsDocked
                && $report->isHasStableFramerateDocked() === $customDto->hasStableFramerateDocked
                && $report->isHasResolutionImprovedDocked() === $customDto->hasResolutionImprovedDocked
                && $report->isNativeResolutionDocked() === $customDto->isNativeResolutionDocked
                && $report->isHasImprovedLoadingTimes() === $customDto->hasImprovedLoadingTimes
                && $report->isSwitch2Edition() === $customDto->isSwitch2Edition
                && $report->getGameStatus() === $customDto->gameStatus;
        }));

    $handler = new CreateReportFromDtoCommandHandler(
        $this->reportRepository,
        $this->gameRepository
    );

    $command = new CreateReportFromDtoCommand($customDto);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles different game status values', function () {
    // Given
    $dtoWithBadStatus = new CreateReportFormInputDto(
        gameId: $this->gameId,
        gameSlug: $this->gameSlug,
        gameStatus: ReportGameStatusEnum::BAD,
        is60FpsPortable: false,
        hasStableFrameratePortable: false,
        hasResolutionImprovedPortable: false,
        isNativeResolutionPortable: false,
        is60FpsDocked: false,
        hasStableFramerateDocked: false,
        hasResolutionImprovedDocked: false,
        isNativeResolutionDocked: false,
        hasImprovedLoadingTimes: false,
        isSwitch2Edition: false,
    );

    $this->gameRepository
        ->expects($this->once())
        ->method('getOneByIdEnabledGame')
        ->with($this->gameId)
        ->willReturn($this->game);

    $this->reportRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($report) use ($dtoWithBadStatus) {
            return $report instanceof Report
                && $report->is60FpsPortable() === $dtoWithBadStatus->is60FpsPortable
                && $report->isHasStableFrameratePortable() === $dtoWithBadStatus->hasStableFrameratePortable
                && $report->isHasResolutionImprovedPortable() === $dtoWithBadStatus->hasResolutionImprovedPortable
                && $report->isNativeResolutionPortable() === $dtoWithBadStatus->isNativeResolutionPortable
                && $report->is60FpsDocked() === $dtoWithBadStatus->is60FpsDocked
                && $report->isHasStableFramerateDocked() === $dtoWithBadStatus->hasStableFramerateDocked
                && $report->isHasResolutionImprovedDocked() === $dtoWithBadStatus->hasResolutionImprovedDocked
                && $report->isNativeResolutionDocked() === $dtoWithBadStatus->isNativeResolutionDocked
                && $report->isHasImprovedLoadingTimes() === $dtoWithBadStatus->hasImprovedLoadingTimes
                && $report->isSwitch2Edition() === $dtoWithBadStatus->isSwitch2Edition
                && $report->getGameStatus() === $dtoWithBadStatus->gameStatus;
        }));

    $handler = new CreateReportFromDtoCommandHandler(
        $this->reportRepository,
        $this->gameRepository
    );

    $command = new CreateReportFromDtoCommand($dtoWithBadStatus);

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
