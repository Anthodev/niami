<?php

declare(strict_types=1);

namespace App\Domain\Factory\Report;

use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;
use App\Infrastructure\Enum\ReportGameStatusEnum;

class ReportFactory
{
    public static function create(
        Game $game,
        bool $isSwitch2Edition = false,
        bool $is60FpsPortable = false,
        bool $hasStableFrameratePortable = false,
        bool $is60FpsDocked = false,
        bool $hasStableFramerateDocked = false,
        bool $hasResolutionImprovedPortable = false,
        bool $isNativeResolutionPortable = false,
        bool $hasResolutionImprovedDocked = false,
        bool $isNativeResolutionImprovedDocked = false,
        bool $hasImprovedLoadingTimes = false,
        ReportGameStatusEnum $gameStatus = ReportGameStatusEnum::OK,
        int $upvoteCount = 0,
        bool $isVisible = true,
    ): Report {
        return new Report(
            game: $game,
            isSwitch2Edition: $isSwitch2Edition,
            is60FpsPortable: $is60FpsPortable,
            hasStableFrameratePortable: $hasStableFrameratePortable,
            is60FpsDocked: $is60FpsDocked,
            hasStableFramerateDocked: $hasStableFramerateDocked,
            hasResolutionImprovedPortable: $hasResolutionImprovedPortable,
            isNativeResolutionPortable: $isNativeResolutionPortable,
            hasResolutionImprovedDocked: $hasResolutionImprovedDocked,
            isNativeResolutionImprovedDocked: $isNativeResolutionImprovedDocked,
            hasImprovedLoadingTimes: $hasImprovedLoadingTimes,
            gameStatus: $gameStatus,
            upvoteCount: $upvoteCount,
            isVisible: $isVisible,
        );
    }
}
