<?php

declare(strict_types=1);

namespace App\Domain\Factory\Game;

use App\Domain\Model\Game\ApiGame;

class ApiGameFactory
{
    public static function create(
        string $name,
        string $slug,
        string $description,
        string $imageCover,
        string $publisher,
        string $releaseDate,
    ): ApiGame {
        return new ApiGame(
            name: $name,
            slug: $slug,
            description: $description,
            imageCover: $imageCover,
            publisher: $publisher,
            releaseDate: $releaseDate,
        );
    }
}
