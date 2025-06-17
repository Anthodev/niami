<?php

declare(strict_types=1);

namespace App\Domain\Model\Game;

class ApiGame
{
    public function __construct(
        private string $name,
        private string $slug,
        private string $description,
        private string $imageCover,
        private string $publisher,
        private string $releaseDate,
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

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function getReleaseDate(): string
    {
        return $this->releaseDate;
    }
}
