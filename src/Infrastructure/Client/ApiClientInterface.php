<?php

declare(strict_types=1);

namespace App\Infrastructure\Client;

use App\Domain\Model\Game\ApiGame;

interface ApiClientInterface
{
    /** @return ApiGame[] */
    public function searchGames(string $query, int $limit = 10): array;

    public function getGameBySlug(string $slug, int $limit = 3): ?ApiGame;
}
