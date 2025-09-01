<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Shared\Dto\Game\GameCompanyDataDto;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateGameCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public string $slug,
        #[Assert\NotBlank]
        public string $releaseDate,
        #[Assert\NotBlank, Assert\Url]
        public string $imageCover,
        public ?GameCompanyDataDto $publisher = null,
        public ?GameCompanyDataDto $developer = null,
        public ?string $description = null,
        private bool $isActive = true,
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getReleaseDate(): string
    {
        return $this->releaseDate;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
