<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\GetPublisherByNameQuery;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\PublisherRepositoryInterface as GamePublisherRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetPublisherByNameQueryHandler
{
    public function __construct(
        private GamePublisherRepositoryInterface $publisherRepository,
    ) {
    }

    public function __invoke(GetPublisherByNameQuery $query): ?Publisher
    {
        return $this->publisherRepository->findByName($query->name);
    }
}
