<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Game;

use App\Domain\Model\Game\ApiGame;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Infrastructure\Client\IgdbClient;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

readonly class IgdbApiRepository implements ApiGameRepositoryInterface
{
    public function __construct(
        private IgdbClient $igdbClient,
    ) {
    }

    /**
     * @return ApiGame[]
     *
     * @throws InvalidArgumentException|ClientExceptionInterface
     */
    public function searchGames(string $query, int $limit = 10): array
    {
        return $this->igdbClient->searchGames($query, $limit);
    }

    public function getCoverForGame(string $slug): ?string
    {
        return $this->igdbClient->getGameBySlug($slug)?->getImageCover();
    }

    public function getGameBySlug(string $slug): ?ApiGame
    {
        return $this->igdbClient->getGameBySlug($slug);
    }
}
