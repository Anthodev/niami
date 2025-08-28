<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGameCommand;
use App\Application\Command\CreateGamePublisherCommand;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetPublisherByNameQuery;
use App\Domain\Factory\Game\GameFactory;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateGameCommandHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusHelper $messageBusHelper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateGameCommand $command): void
    {
        /** @var ?Publisher $publisher */
        $publisher = $this->publisherRepository->findByName(
            $command->publisher->name,
        );

        if (null === $publisher) {
            $this->messageBus->dispatch(
                new CreateGamePublisherCommand(
                    name: $command->publisher->name,
                    website: $command->publisher->website,
                    apiId: $command->publisher->apiId,
                ),
            );

            $publisherEnvelope = $this->messageBus->dispatch(
                new GetPublisherByNameQuery($command->publisher->name),
            );

            /** @var Publisher $publisher */
            $publisher = $this->messageBusHelper->getContentFromEnvelope(
                $publisherEnvelope,
                'Publisher not found',
                Publisher::class,
            );
        }

        $game = GameFactory::create(
            name: $command->name,
            slug: $command->slug,
            description: $command->description ?? '',
            releaseDate: $command->releaseDate,
            imageCover: $command->imageCover,
            publisher: $publisher,
        );

        try {
            $this->gameRepository->save($game);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
