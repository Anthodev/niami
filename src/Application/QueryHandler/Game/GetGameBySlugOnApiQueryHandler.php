<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\GetGameBySlugOnApiQuery;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetGameBySlugOnApiQueryHandler
{
    public function __construct(
        private ApiGameRepositoryInterface $apiGameRepository,
    ) {
    }

    public function __invoke(GetGameBySlugOnApiQuery $query): ?ApiGame
    {
        return $this->apiGameRepository->getGameBySlug($query->slug);
    }
}
