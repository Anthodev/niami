<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\GetDeveloperByNameQuery;
use App\Domain\Model\Game\Developer;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDeveloperByNameQueryHandler
{
    public function __construct(
        private DeveloperRepositoryInterface $developerRepository,
    ) {
    }

    public function __invoke(GetDeveloperByNameQuery $query): ?Developer
    {
        return $this->developerRepository->findByName($query->name);
    }
}
