<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Command\Game\CreatePublisherCommand;
use App\Application\Command\Game\UpdateDeveloperCommand;
use App\Application\Command\Game\UpdateGameFromApiCommand;
use App\Application\Command\Game\UpdatePublisherCommand;
use App\Application\Exception\Game\CannotCreateDeveloperException;
use App\Application\Exception\Game\CannotCreatePublisherException;
use App\Application\Exception\Game\CannotUpdateDeveloperException;
use App\Application\Exception\Game\CannotUpdateGameException;
use App\Application\Exception\Game\CannotUpdatePublisherException;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UpdateGameFromApiCommandHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly PublisherRepositoryInterface $publisherRepository,
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws CannotCreatePublisherException
     * @throws ExceptionInterface
     * @throws CannotUpdatePublisherException
     * @throws CannotCreateDeveloperException
     * @throws CannotUpdateDeveloperException
     * @throws CannotUpdateGameException
     */
    public function __invoke(UpdateGameFromApiCommand $command): void
    {
        $publisher = $command->game->getPublisher();
        $developer = $command->game->getDeveloper();

        if (null !== $command->apiGame->getPublisher()) {
            if (null === $publisher) {
                try {
                    $this->messageBus->dispatch(new CreatePublisherCommand(
                        name: $command->apiGame->getPublisher()->name,
                        website: $command->apiGame->getPublisher()->website,
                        apiId: $command->apiGame->getPublisher()->apiId,
                    ));
                } catch (ExceptionInterface) {
                    throw new CannotCreatePublisherException();
                }
            } else {
                /** @var int $publisherApiId */
                $publisherApiId = $publisher->getApiId();

                try {
                    $this->messageBus->dispatch(new UpdatePublisherCommand(
                        publisherApiId: $publisherApiId,
                        name: $command->apiGame->getPublisher()->name,
                        website: $command->apiGame->getPublisher()->website,
                    ));
                } catch (ExceptionInterface) {
                    throw new CannotUpdatePublisherException();
                }
            }

            $publisher = $this->publisherRepository->findByApiId($command->apiGame->getPublisher()->apiId);
        }

        if (null !== $command->apiGame->getDeveloper()) {
            if (null === $developer) {
                try {
                    $this->messageBus->dispatch(new CreateDeveloperCommand(
                        name: $command->apiGame->getDeveloper()->name,
                        website: $command->apiGame->getDeveloper()->website,
                        apiId: $command->apiGame->getDeveloper()->apiId,
                    ));
                } catch (ExceptionInterface) {
                    throw new CannotCreateDeveloperException();
                }
            } else {
                /** @var int $developerApiId */
                $developerApiId = $developer->getApiId();

                try {
                    $this->messageBus->dispatch(new UpdateDeveloperCommand(
                        developerApiId: $developerApiId,
                        name: $command->apiGame->getDeveloper()->name,
                        website: $command->apiGame->getDeveloper()->website,
                    ));
                } catch (ExceptionInterface) {
                    throw new CannotUpdateDeveloperException();
                }
            }

            $developer = $this->developerRepository->findByApiId($command->apiGame->getDeveloper()->apiId);
        }

        $command->game->setName($command->apiGame->getName());
        $command->game->setDescription($command->apiGame->getDescription());
        $command->game->setImageCover($command->apiGame->getImageCover());
        $command->game->setReleaseDate($command->apiGame->getReleaseDate());
        $command->game->setPublisher($publisher);
        $command->game->setDeveloper($developer);

        try {
            $this->gameRepository->update($command->game);
        } catch (\Exception) {
            throw new CannotUpdateGameException();
        }
    }
}
