<?php

declare(strict_types=1);

namespace App\Application\Query\Game;

use App\Domain\Model\Game\ApiGame;

class GetOrCreateGameQuery
{
    public function __construct(
        public readonly string $gameSlug,
        public readonly ApiGame $apiGame,
        public readonly bool $includeApi = false,
    ) {
    }
}
