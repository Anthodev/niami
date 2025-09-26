<?php

declare(strict_types=1);

namespace App\Application\Query\Game;

readonly class GetGameBySlugOnApiQuery
{
    public function __construct(public string $slug)
    {
    }
}
