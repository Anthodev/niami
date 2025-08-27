<?php

declare(strict_types=1);

namespace App\Presentation\Dto;

use App\Infrastructure\Enum\ReportGameStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

class CreateReportFormInputDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $gameId = '',
        #[Assert\NotBlank]
        public string $gameSlug = '',
        public ReportGameStatusEnum $gameStatus = ReportGameStatusEnum::OK,
        public bool $is60FpsPortable = false,
        public bool $hasStableFrameratePortable = true,
        public bool $hasResolutionImprovedPortable = false,
        public bool $isNativeResolutionPortable = false,
        public bool $is60FpsDocked = false,
        public bool $hasStableFramerateDocked = true,
        public bool $hasResolutionImprovedDocked = false,
        public bool $isNativeResolutionDocked = false,
        public bool $hasImprovedLoadingTimes = true,
        public bool $isSwitch2Edition = false,
    ) {
    }
}
