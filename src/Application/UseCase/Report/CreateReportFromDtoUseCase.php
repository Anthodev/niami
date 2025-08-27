<?php

declare(strict_types=1);

namespace App\Application\UseCase\Report;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameByIdQuery;
use App\Domain\Factory\Report\ReportFactory;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use App\Presentation\Dto\CreateReportFormInputDto;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateReportFromDtoUseCase
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusHelper $messageBusHelper,
        private readonly ReportRepositoryInterface $reportRepository,
    ) {
    }

    public function execute(
        CreateReportFormInputDto $createReportFormInputDto,
    ): void {
        $gameEnvelope = $this->messageBus->dispatch(new GetGameByIdQuery($createReportFormInputDto->gameId));
        /** @var Game $game */
        $game = $this->messageBusHelper->getContentFromEnvelope(
            envelope: $gameEnvelope,
            logErrorMessage: 'Game retrieval failed',
            class: Game::class,
        );

        $newReport = ReportFactory::create(
            game: $game,
            isSwitch2Edition: $createReportFormInputDto->isSwitch2Edition,
            is60FpsPortable: $createReportFormInputDto->is60FpsPortable,
            hasStableFrameratePortable: $createReportFormInputDto->hasStableFrameratePortable,
            hasResolutionImprovedPortable: $createReportFormInputDto->hasResolutionImprovedPortable,
            isNativeResolutionPortable: $createReportFormInputDto->isNativeResolutionPortable,
            is60FpsDocked: $createReportFormInputDto->is60FpsDocked,
            hasStableFramerateDocked: $createReportFormInputDto->hasStableFramerateDocked,
            hasResolutionImprovedDocked: $createReportFormInputDto->hasResolutionImprovedDocked,
            isNativeResolutionImprovedDocked: $createReportFormInputDto->isNativeResolutionDocked,
            hasImprovedLoadingTimes: $createReportFormInputDto->hasImprovedLoadingTimes,
            gameStatus: $createReportFormInputDto->gameStatus,
        );

        $this->reportRepository->save($newReport);
    }
}
