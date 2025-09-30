<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\UpdatePublisherCommand;
use App\Application\Fetcher\Game\PublisherFetcher;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdatePublisherCommandHandler
{
    public function __construct(
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly PublisherFetcher $publisherFetcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdatePublisherCommand $command): void
    {
        /** @var Publisher $publisher */
        $publisher = $this->publisherFetcher->findOneByApiId(
            $command->publisherApiId,
            $command->name,
        );

        if (null !== $publisher) {
            $publisher->setName($command->name);
            $publisher->setWebsite($command->website);
        }

        try {
            $this->publisherRepository->update($publisher);
            $this->publisherFetcher->deleteCacheName($command->name);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
