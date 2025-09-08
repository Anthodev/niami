<?php

declare(strict_types=1);

namespace App\Domain\Factory\Game;

use App\Domain\Model\Game\ApiGame;
use App\Shared\Dto\Game\GameCompanyDataDto;

class ApiGameFactory
{
    public static function create(
        string $name,
        string $slug,
        string $description,
        string $imageCover,
        string $releaseDate,
        \DateTimeImmutable $updatedAt,
        ?GameCompanyDataDto $publisher = null,
        ?GameCompanyDataDto $developer = null,
    ): ApiGame {
        return new ApiGame(
            name: $name,
            slug: $slug,
            description: $description,
            imageCover: $imageCover,
            releaseDate: $releaseDate,
            updatedAt: $updatedAt,
            publisher: $publisher,
            developer: $developer,
        );
    }
}
