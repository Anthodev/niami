<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Fetcher\Game\PublisherFetcher;
use App\Application\Query\Game\GetPublisherByNameQuery;
use App\Domain\Model\Game\Publisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetPublisherByNameQueryHandler
{
    public function __construct(
        private PublisherFetcher $publisherFetcher,
    ) {
    }

    public function __invoke(GetPublisherByNameQuery $query): ?Publisher
    {
        return $this->publisherFetcher->findOneByName($query->name);
    }
}
