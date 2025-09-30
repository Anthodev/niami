<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Command\Game\CreateGameCommand;
use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\Fetcher\Game\GameFetcher;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetDeveloperByNameQuery;
use App\Application\Query\Game\GetPublisherByNameQuery;
use App\Domain\Factory\Game\GameFactory;
use App\Domain\Model\Game\Developer;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
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
        private readonly GameFetcher $gameFetcher,
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusHelper $messageBusHelper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateGameCommand $command): void
    {
        $game = $this->gameRepository->getOneBySlugEnabledGame($command->slug);

        if (null !== $game) {
            return;
        }

        $publisher = null;
        $developer = null;

        if (null !== $command->publisher) {
            /** @var ?Publisher $publisher */
            $publisher = $this->publisherRepository->findOneByName(
                $command->publisher->name,
            );
        }

        if (null !== $command->developer) {
            /** @var ?Developer $developer */
            $developer = $this->developerRepository->findOneByName(
                $command->developer->name,
            );
        }

        if (null !== $command->publisher && null === $publisher) {
            $this->messageBus->dispatch(
                new CreatePublisherCommand(
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

        if (null !== $command->developer && null === $developer) {
            $this->messageBus->dispatch(
                new CreateDeveloperCommand(
                    name: $command->developer->name,
                    website: $command->developer->website,
                    apiId: $command->developer->apiId,
                ),
            );

            $developerEnvelope = $this->messageBus->dispatch(
                new GetDeveloperByNameQuery($command->developer->name),
            );

            /** @var Developer $developer */
            $developer = $this->messageBusHelper->getContentFromEnvelope(
                $developerEnvelope,
                'Developer not found',
                Developer::class,
            );
        }

        $game = GameFactory::create(
            name: $command->name,
            slug: $command->slug,
            description: $command->description ?? '',
            releaseDate: $command->releaseDate,
            imageCover: $command->imageCover,
            publisher: $publisher,
            developer: $developer,
        );

        try {
            $this->gameRepository->save($game);
            $this->gameFetcher->deleteCacheForSlug($command->slug);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
