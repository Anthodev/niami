<?php

declare(strict_types=1);

namespace App\Application\UseCase\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\Command\Game\UpdateDeveloperCommand;
use App\Application\Command\Game\UpdatePublisherCommand;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Developer;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateGameFromApiUseCase
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function execute(
        Game $game,
        ApiGame $apiGame,
    ): void {
        $publisher = $game->getPublisher();
        $developer = $game->getDeveloper();

        if (null !== $apiGame->getPublisher()) {
            if (null === $publisher) {
                $this->messageBus->dispatch(new CreatePublisherCommand(
                    name: $apiGame->getPublisher()->name,
                    website: $apiGame->getPublisher()->website,
                    apiId: $apiGame->getPublisher()->apiId,
                ));
            } else {
                /** @var int $publisherApiId */
                $publisherApiId = $publisher->getApiId();
                /** @var Publisher $publisher */
                $publisher = $this->publisherRepository->findByApiId($publisherApiId);

                $this->messageBus->dispatch(new UpdatePublisherCommand(
                    publisherApiId: $publisherApiId,
                    name: $apiGame->getPublisher()->name,
                    website: $apiGame->getPublisher()->website,
                ));
            }

            $publisher = $this->publisherRepository->findByApiId($apiGame->getPublisher()->apiId);
        }

        if (null !== $apiGame->getDeveloper()) {
            if (null === $developer) {
                $this->messageBus->dispatch(new CreateDeveloperCommand(
                    name: $apiGame->getDeveloper()->name,
                    website: $apiGame->getDeveloper()->website,
                    apiId: $apiGame->getDeveloper()->apiId,
                ));
            } else {
                /** @var int $developerApiId */
                $developerApiId = $developer->getApiId();
                /** @var Developer $developer */
                $developer = $this->developerRepository->findByApiId($developerApiId);

                $this->messageBus->dispatch(new UpdateDeveloperCommand(
                    developerApiId: $developerApiId,
                    name: $apiGame->getDeveloper()->name,
                    website: $apiGame->getDeveloper()->website,
                ));
            }

            $developer = $this->developerRepository->findByApiId($apiGame->getDeveloper()->apiId);
        }

        $game->setName($apiGame->getName());
        $game->setDescription($apiGame->getDescription());
        $game->setImageCover($apiGame->getImageCover());
        $game->setReleaseDate($apiGame->getReleaseDate());
        $game->setPublisher($publisher);
        $game->setDeveloper($developer);

        $this->gameRepository->update($game);
    }
}
