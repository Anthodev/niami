<?php

declare(strict_types=1);

namespace App\Domain\Factory\Game;

use App\Domain\Model\Game\Developer;

class DeveloperFactory
{
    public static function create(
        string $name,
        int $apiId,
        ?string $website = null,
    ): Developer {
        return new Developer(
            name: $name,
            website: $website,
            apiId: $apiId,
        );
    }
}
