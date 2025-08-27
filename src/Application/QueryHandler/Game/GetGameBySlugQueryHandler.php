<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\GetGameBySlugQuery;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetGameBySlugQueryHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
    ) {
    }

    public function __invoke(GetGameBySlugQuery $query): ?Game
    {
        return $this->gameRepository->getOneBySlugEnabledGame($query->gameSlug);
    }
}
