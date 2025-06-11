<?php

declare(strict_types=1);

namespace App\Domain\Model\Game;

use App\Domain\Model\Common\ModelInterface;
use Symfony\Component\Uid\Uuid;

class Report implements ModelInterface
{
    private ?string $id = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTime $updatedAt = null;

    public function __construct(
        private Game $game,
        private bool $isSwitch2Edition = false,
        private bool $is60FpsPortable = false,
        private bool $hasStableFrameratePortable = false,
        private bool $is60FpsDocked = false,
        private bool $hasStableFramerateDocked = false,
        private bool $hasResolutionImprovedPortable = false,
        private bool $isNativeResolutionPortable = false,
        private bool $hasResolutionImprovedDocked = false,
        private bool $isNativeResolutionImprovedDocked = false,
        private bool $hasImprovedLoadingTimes = false,
        private int $upvoteCount = 0,
        private bool $isVisible = true,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
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

    public function setHasStableFrameratePortable(bool $hasStableFrameratePortable): self
    {
        $this->hasStableFrameratePortable = $hasStableFrameratePortable;

        return $this;
    }

    public function isHasResolutionImprovedPortable(): bool
    {
        return $this->hasResolutionImprovedPortable;
    }

    public function setHasResolutionImprovedPortable(bool $hasResolutionImprovedPortable): self
    {
        $this->hasResolutionImprovedPortable = $hasResolutionImprovedPortable;

        return $this;
    }

    public function isNativeResolutionPortable(): bool
    {
        return $this->isNativeResolutionPortable;
    }

    public function setIsNativeResolutionPortable(bool $isNativeResolutionPortable): self
    {
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

    public function setHasStableFramerateDocked(bool $hasStableFramerateDocked): self
    {
        $this->hasStableFramerateDocked = $hasStableFramerateDocked;

        return $this;
    }

    public function isHasResolutionImprovedDocked(): bool
    {
        return $this->hasResolutionImprovedDocked;
    }

    public function setHasResolutionImprovedDocked(bool $hasResolutionImprovedDocked): self
    {
        $this->hasResolutionImprovedDocked = $hasResolutionImprovedDocked;

        return $this;
    }

    public function isNativeResolutionImprovedDocked(): bool
    {
        return $this->isNativeResolutionImprovedDocked;
    }

    public function setIsNativeResolutionImprovedDocked(bool $isNativeResolutionImprovedDocked): self
    {
        $this->isNativeResolutionImprovedDocked = $isNativeResolutionImprovedDocked;

        return $this;
    }

    public function isHasImprovedLoadingTimes(): bool
    {
        return $this->hasImprovedLoadingTimes;
    }

    public function setHasImprovedLoadingTimes(bool $hasImprovedLoadingTimes): self
    {
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setDefaultId(): self
    {
        $this->id = Uuid::v7()->toRfc4122();

        return $this;
    }
}
