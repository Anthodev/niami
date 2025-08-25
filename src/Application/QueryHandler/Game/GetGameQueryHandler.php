<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\GetGameQuery;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetGameQueryHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
    ) {
    }

    public function __invoke(GetGameQuery $query): ?Game
    {
        return $this->gameRepository->getOneBySlugEnabledGame($query->gameSlug);
    }
}
