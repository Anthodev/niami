<?php

declare(strict_types=1);

namespace App\Application\Query\Game;

class GetDeveloperByNameQuery
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
