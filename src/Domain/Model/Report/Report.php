<?php

declare(strict_types=1);

namespace App\Domain\Model\Report;

use App\Domain\Model\Common\ModelInterface;
use App\Domain\Model\Game\Game;
use App\Domain\Trait\IdTrait;
use App\Domain\Trait\TimestampableTrait;
use App\Infrastructure\Enum\ReportGameStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Report implements ModelInterface
{
    use IdTrait;
    use TimestampableTrait;

    public function __construct(
        private Game $game,
        private bool $is60FpsPortable = false,
        private bool $hasStableFrameratePortable = false,
        private bool $hasResolutionImprovedPortable = false,
        private bool $isNativeResolutionPortable = false,
        private bool $is60FpsDocked = false,
        private bool $hasStableFramerateDocked = false,
        private bool $hasResolutionImprovedDocked = false,
        private bool $isNativeResolutionDocked = false,
        private bool $hasImprovedLoadingTimes = false,
        private bool $isSwitch2Edition = false,
        private ReportGameStatusEnum $gameStatus = ReportGameStatusEnum::OK,
        private bool $isPatched = false,
        private int $upvoteCount = 0,
        private bool $isVisible = true,
        /** @var Collection<int, ReportComment> $reportComments */
        private Collection $reportComments = new ArrayCollection(),
    ) {
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function isSwitch2Edition(): bool
    {
        return $this->isSwitch2Edition;
    }

    public function setIsSwitch2Edition(bool $isSwitch2Edition): self
    {
        $this->isSwitch2Edition = $isSwitch2Edition;

        return $this;
    }

    public function is60FpsPortable(): bool
    {
        return $this->is60FpsPortable;
    }

    public function setIs60FpsPortable(bool $is60FpsPortable): self
    {
        $this->is60FpsPortable = $is60FpsPortable;

        return $this;
    }

    public function isHasStableFrameratePortable(): bool
    {
        return $this->hasStableFrameratePortable;
    }

    public function setHasStableFrameratePortable(
        bool $hasStableFrameratePortable,
    ): self {
        $this->hasStableFrameratePortable = $hasStableFrameratePortable;

        return $this;
    }

    public function isHasResolutionImprovedPortable(): bool
    {
        return $this->hasResolutionImprovedPortable;
    }

    public function setHasResolutionImprovedPortable(
        bool $hasResolutionImprovedPortable,
    ): self {
        $this->hasResolutionImprovedPortable = $hasResolutionImprovedPortable;

        return $this;
    }

    public function getGameStatus(): ReportGameStatusEnum
    {
        return $this->gameStatus;
    }

    public function setGameStatus(ReportGameStatusEnum $gameStatus): self
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function isPatched(): bool
    {
        return $this->isPatched;
    }

    public function setIsPatched(bool $isPatched): self
    {
        $this->isPatched = $isPatched;

        return $this;
    }

    public function isNativeResolutionPortable(): bool
    {
        return $this->isNativeResolutionPortable;
    }

    public function setIsNativeResolutionPortable(
        bool $isNativeResolutionPortable,
    ): self {
        $this->isNativeResolutionPortable = $isNativeResolutionPortable;

        return $this;
    }

    public function is60FpsDocked(): bool
    {
        return $this->is60FpsDocked;
    }

    public function setIs60FpsDocked(bool $is60FpsDocked): self
    {
        $this->is60FpsDocked = $is60FpsDocked;

        return $this;
    }

    public function isHasStableFramerateDocked(): bool
    {
        return $this->hasStableFramerateDocked;
    }

    public function setHasStableFramerateDocked(
        bool $hasStableFramerateDocked,
    ): self {
        $this->hasStableFramerateDocked = $hasStableFramerateDocked;

        return $this;
    }

    public function isHasResolutionImprovedDocked(): bool
    {
        return $this->hasResolutionImprovedDocked;
    }

    public function setHasResolutionImprovedDocked(
        bool $hasResolutionImprovedDocked,
    ): self {
        $this->hasResolutionImprovedDocked = $hasResolutionImprovedDocked;

        return $this;
    }

    public function isNativeResolutionDocked(): bool
    {
        return $this->isNativeResolutionDocked;
    }

    public function setIsNativeResolutionDocked(
        bool $isNativeResolutionDocked,
    ): self {
        $this->isNativeResolutionDocked = $isNativeResolutionDocked;

        return $this;
    }

    public function isHasImprovedLoadingTimes(): bool
    {
        return $this->hasImprovedLoadingTimes;
    }

    public function setHasImprovedLoadingTimes(
        bool $hasImprovedLoadingTimes,
    ): self {
        $this->hasImprovedLoadingTimes = $hasImprovedLoadingTimes;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getUpvoteCount(): int
    {
        return $this->upvoteCount;
    }

    public function increaseUpvoteCount(): self
    {
        ++$this->upvoteCount;

        return $this;
    }

    /**
     * @return Collection<int, ReportComment>
     */
    public function getReportComments(): Collection
    {
        return $this->reportComments;
    }

    public function addReportComment(ReportComment $reportComment): self
    {
        if (!$this->reportComments->contains($reportComment)) {
            $this->reportComments->add($reportComment);
        }

        return $this;
    }

    public function removeReportComment(ReportComment $reportComment): self
    {
        if ($this->reportComments->contains($reportComment)) {
            $this->reportComments->removeElement($reportComment);
        }

        return $this;
    }
}
