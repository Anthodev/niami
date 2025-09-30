<?php

declare(strict_types=1);

namespace App\Application\Query\Report;

readonly class GetReportsForGameIdQuery
{
    public function __construct(
        public string $gameId,
        public string $gameSlug,
    ) {
    }
}
