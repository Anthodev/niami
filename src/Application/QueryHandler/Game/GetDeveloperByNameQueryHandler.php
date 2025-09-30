<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Fetcher\Game\DeveloperFetcher;
use App\Application\Query\Game\GetDeveloperByNameQuery;
use App\Domain\Model\Game\Developer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDeveloperByNameQueryHandler
{
    public function __construct(
        private DeveloperFetcher $developerFetcher,
    ) {
    }

    public function __invoke(GetDeveloperByNameQuery $query): ?Developer
    {
        return $this->developerFetcher->findOneByName($query->name);
    }
}
