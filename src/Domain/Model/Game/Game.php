<?php

declare(strict_types=1);

namespace App\Domain\Model\Game;

use App\Domain\Model\Common\ModelInterface;
use App\Domain\Model\Report\Report;
use App\Domain\Trait\IdTrait;
use App\Domain\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Game implements ModelInterface
{
    use IdTrait;
    use TimestampableTrait;

    public function __construct(
        private ?string $name = null,
        private ?string $slug = null,
        private ?string $description = null,
        private ?string $releaseDate = null,
        private ?string $imageCover = null,
        private bool $isPatched = false,
        private bool $isActive = true,
        private ?Publisher $publisher = null,
        private ?Developer $developer = null,
        /** @var Collection<int, Report> */
        private Collection $reports = new ArrayCollection(),
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(string $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getImageCover(): ?string
    {
        return $this->imageCover;
    }

    public function setImageCover(string $imageCover): self
    {
        $this->imageCover = $imageCover;

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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getDeveloper(): ?Developer
    {
        return $this->developer;
    }

    public function setDeveloper(?Developer $developer): self
    {
        $this->developer = $developer;

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
        }

        return $this;
    }
}
