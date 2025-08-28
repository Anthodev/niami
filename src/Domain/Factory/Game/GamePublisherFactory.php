<?php

declare(strict_types=1);

namespace App\Domain\Factory\Game;

use App\Domain\Model\Game\Publisher;

class GamePublisherFactory
{
    public static function create(
        string $name,
        int $apiId,
        ?string $website = null,
    ): Publisher {
        return new Publisher(
            name: $name,
            website: $website,
            apiId: $apiId,
        );
    }
}
