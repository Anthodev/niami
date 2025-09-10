<?php

declare(strict_types=1);

namespace App\Application\Command\Game;

readonly class CreateGameWithCacheCheckCommand
{
    public function __construct(
        public string $gameSlug,
    ) {
    }
}
