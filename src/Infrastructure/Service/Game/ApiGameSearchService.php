<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Game;

use App\Domain\Model\Game\ApiGame;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Domain\Service\Game\GameApiServiceInterface;

class ApiGameSearchService implements GameApiServiceInterface
{
    public function __construct(
        private ApiGameRepositoryInterface $apiGameRepository,
    ) {
    }

    public function searchGames(string $query, int $limit = 10): array
    {
        return $this->apiGameRepository->searchGames($query, $limit);
    }

    public function getCoverForGame(string $slug): string
    {
        return '';
    }

    public function getGameBySlug(string $slug): ?ApiGame
    {
        return null;
    }
}
