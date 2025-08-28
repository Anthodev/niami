<?php

declare(strict_types=1);

namespace App\Shared\Dto\Game;

class GameCompanyDataDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $website,
        public readonly int $apiId,
    ) {
    }
}
