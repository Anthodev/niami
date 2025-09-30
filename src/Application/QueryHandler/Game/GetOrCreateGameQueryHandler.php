<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Command\Game\CreateGameCommand;
use App\Application\Exception\Game\CannotCreateGameException;
use App\Application\Fetcher\Game\GameFetcher;
use App\Application\Query\Game\GetOrCreateGameQuery;
use App\Domain\Model\Game\Game;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class GetOrCreateGameQueryHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly GameFetcher $gameFetcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CannotCreateGameException
     */
    public function __invoke(GetOrCreateGameQuery $query): Game
    {
        $game = $this->gameFetcher->getOneBySlugEnabledGame($query->gameSlug);

        if (null === $game) {
            try {
                $this->messageBus->dispatch(
                    new CreateGameCommand(
                        name: $query->apiGame->getName(),
                        slug: $query->apiGame->getSlug(),
                        releaseDate: $query->apiGame->getReleaseDate(),
                        imageCover: $query->apiGame->getImageCover(),
                        publisher: $query->apiGame->getPublisher(),
                        developer: $query->apiGame->getDeveloper(),
                        description: $query->apiGame->getDescription(),
                    ),
                );
            } catch (\Exception|ExceptionInterface $e) {
                $this->logger->error($e->getMessage());

                throw new CannotCreateGameException();
            }

            /** @var Game $game */
            $game = $this->gameFetcher->getOneBySlugEnabledGame(
                $query->gameSlug,
            );
        }

        return $game;
    }
}
