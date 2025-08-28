<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGamePublisherCommand;
use App\Domain\Factory\Game\GamePublisherFactory;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGamePublisherCommandHandler
{
    public function __construct(
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateGamePublisherCommand $command): void
    {
        $publisher = $this->publisherRepository->findByName($command->name);

        if (null !== $publisher) {
            return;
        }

        $publisher = GamePublisherFactory::create(
            name: $command->name,
            apiId: $command->apiId,
            website: $command->website,
        );

        try {
            $this->publisherRepository->save($publisher);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
