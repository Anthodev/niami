<?php

declare(strict_types=1);

namespace App\Domain\Repository\Game;

use App\Domain\Model\Game\ApiGame;

interface ApiGameRepositoryInterface
{
    /**
     * @return ApiGame[]
     */
    public function searchGames(string $query, int $limit = 10): array;

    public function getCoverForGame(string $slug): ?string;

    public function getGameBySlug(string $slug): ?ApiGame;
}
