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
        bool $hasStableFrameratePortable = true,
        bool $hasResolutionImprovedPortable = false,
        bool $isNativeResolutionPortable = false,
        bool $is60FpsDocked = false,
        bool $hasStableFramerateDocked = true,
        bool $hasResolutionImprovedDocked = false,
        bool $isNativeResolutionImprovedDocked = false,
        bool $hasImprovedLoadingTimes = true,
        ReportGameStatusEnum $gameStatus = ReportGameStatusEnum::OK,
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
            isNativeResolutionImprovedDocked: $isNativeResolutionImprovedDocked,
            hasImprovedLoadingTimes: $hasImprovedLoadingTimes,
            isSwitch2Edition: $isSwitch2Edition,
            gameStatus: $gameStatus,
            upvoteCount: $upvoteCount,
            isVisible: $isVisible,
        );
    }
}
