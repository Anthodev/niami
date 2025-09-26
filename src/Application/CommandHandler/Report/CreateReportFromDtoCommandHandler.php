<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Report;

use App\Application\Command\Report\CreateReportFromDtoCommand;
use App\Domain\Factory\Report\ReportFactory;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsMessageHandler]
class CreateReportFromDtoCommandHandler
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository,
        private readonly GameRepositoryInterface $gameRepository,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(CreateReportFromDtoCommand $command): void
    {
        /** @var Game $game */
        $game = $this->gameRepository->getOneByIdEnabledGame($command->formInputDto->gameId);

        $newReport = ReportFactory::create(
            game: $game,
            isSwitch2Edition: $command->formInputDto->isSwitch2Edition,
            is60FpsPortable: $command->formInputDto->is60FpsPortable,
            hasStableFrameratePortable: $command->formInputDto->hasStableFrameratePortable,
            hasResolutionImprovedPortable: $command->formInputDto->hasResolutionImprovedPortable,
            isNativeResolutionPortable: $command->formInputDto->isNativeResolutionPortable,
            is60FpsDocked: $command->formInputDto->is60FpsDocked,
            hasStableFramerateDocked: $command->formInputDto->hasStableFramerateDocked,
            hasResolutionImprovedDocked: $command->formInputDto->hasResolutionImprovedDocked,
            isNativeResolutionImprovedDocked: $command->formInputDto->isNativeResolutionDocked,
            hasImprovedLoadingTimes: $command->formInputDto->hasImprovedLoadingTimes,
            gameStatus: $command->formInputDto->gameStatus,
            isPatched: $command->formInputDto->isPatched,
        );

        $this->reportRepository->save($newReport);
    }
}
