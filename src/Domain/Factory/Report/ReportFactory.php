<?php

declare(strict_types=1);

namespace App\Domain\Factory\Report;

use App\Domain\Enum\ReportGameStatusEnum;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;

class ReportFactory
{
    public static function create(
        Game $game,
        bool $isSwitch2Edition = false,
        bool $is60FpsPortable = false,
        bool $hasStableFrameratePortable = true,
        bool $hasResolutionImprovedPortable = false,
        bool $isNativeResolutionPortable = false,
        bool $is60FpsDocked = false,
        bool $hasStableFramerateDocked = true,
        bool $hasResolutionImprovedDocked = false,
        bool $isNativeResolutionImprovedDocked = false,
        bool $hasImprovedLoadingTimes = true,
        ReportGameStatusEnum $gameStatus = ReportGameStatusEnum::OK,
        bool $isPatched = false,
        int $upvoteCount = 0,
        bool $isVisible = true,
    ): Report {
        return new Report(
            game: $game,
            is60FpsPortable: $is60FpsPortable,
            hasStableFrameratePortable: $hasStableFrameratePortable,
            hasResolutionImprovedPortable: $hasResolutionImprovedPortable,
            isNativeResolutionPortable: $isNativeResolutionPortable,
            is60FpsDocked: $is60FpsDocked,
            hasStableFramerateDocked: $hasStableFramerateDocked,
            hasResolutionImprovedDocked: $hasResolutionImprovedDocked,
            isNativeResolutionDocked: $isNativeResolutionImprovedDocked,
            hasImprovedLoadingTimes: $hasImprovedLoadingTimes,
            isSwitch2Edition: $isSwitch2Edition,
            gameStatus: $gameStatus,
            isPatched: $isPatched,
            upvoteCount: $upvoteCount,
            isVisible: $isVisible,
        );
    }
}
