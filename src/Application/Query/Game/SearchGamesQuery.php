<?php

declare(strict_types=1);

namespace App\Application\Query\Game;

class SearchGamesQuery
{
    public function __construct(
        public readonly ?string $query = '',
        public readonly int $limit = 10,
        public readonly bool $includeApi = true,
    ) {
    }
}
