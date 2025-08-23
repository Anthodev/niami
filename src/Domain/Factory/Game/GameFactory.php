<?php

declare(strict_types=1);

namespace App\Domain\Factory\Game;

use App\Domain\Model\Game\Game;
use App\Domain\Model\Game\Publisher;
use App\Domain\Model\Report\Report;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GameFactory
{
    /**
     * @param Collection<int, Report> $reports
     */
    public static function create(
        string $name,
        string $slug,
        string $description,
        string $releaseDate,
        string $imageCover,
        bool $isPatched = false,
        bool $isActive = true,
        ?Publisher $publisher = null,
        Collection $reports = new ArrayCollection(),
    ): Game {
        return new Game(
            name: $name,
            slug: $slug,
            description: $description,
            releaseDate: $releaseDate,
            imageCover: $imageCover,
            isPatched: $isPatched,
            isActive: $isActive,
            publisher: $publisher,
            reports: $reports,
        );
    }
}
