<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Fetcher\Game\GameFetcher;
use App\Application\Query\Game\GetGameBySlugQuery;
use App\Domain\Model\Game\Game;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetGameBySlugQueryHandler
{
    public function __construct(private readonly GameFetcher $gameFetcher)
    {
    }

    public function __invoke(GetGameBySlugQuery $query): ?Game
    {
        return $this->gameFetcher->getOneBySlugEnabledGame($query->gameSlug);
    }
}
