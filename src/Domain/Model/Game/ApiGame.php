<?php

declare(strict_types=1);

namespace App\Domain\Model\Game;

use App\Shared\Dto\Game\GameCompanyDataDto;

class ApiGame
{
    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly string $description,
        private readonly string $imageCover,
        private readonly string $releaseDate,
        private readonly \DateTimeImmutable $updatedAt,
        private readonly ?GameCompanyDataDto $publisher = null,
        private readonly ?GameCompanyDataDto $developer = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImageCover(): string
    {
        return $this->imageCover;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublisher(): ?GameCompanyDataDto
    {
        return $this->publisher;
    }

    public function getDeveloper(): ?GameCompanyDataDto
    {
        return $this->developer;
    }

    public function getReleaseDate(): string
    {
        return $this->releaseDate;
    }
}
