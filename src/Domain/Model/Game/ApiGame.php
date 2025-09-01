<?php

declare(strict_types=1);

namespace App\Domain\Model\Game;

use App\Shared\Dto\Game\GameCompanyDataDto;

class ApiGame
{
    public function __construct(
        private string $name,
        private string $slug,
        private string $description,
        private string $imageCover,
        private string $releaseDate,
        private ?GameCompanyDataDto $publisher = null,
        private ?GameCompanyDataDto $developer = null,
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
