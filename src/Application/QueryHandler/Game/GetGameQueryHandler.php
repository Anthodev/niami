<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Command\CreateGameCommand;
use App\Application\Exception\CannotCreateGameException;
use App\Application\Query\Game\GetGameQuery;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class GetGameQueryHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly GameRepositoryInterface $gameRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CannotCreateGameException
     */
    public function __invoke(GetGameQuery $query): Game
    {
        $game = $this->gameRepository->getOneBySlugEnabledGame($query->gameSlug);

        if (null === $game) {
            try {
                $this->messageBus->dispatch(new CreateGameCommand(
                    name: $query->apiGame->getName(),
                    slug: $query->apiGame->getSlug(),
                    releaseDate: $query->apiGame->getReleaseDate(),
                    imageCover: $query->apiGame->getImageCover(),
                    description: $query->apiGame->getDescription(),
                ));
            } catch (\Exception|ExceptionInterface $e) {
                $this->logger->error($e->getMessage());

                throw new CannotCreateGameException();
            }

            /** @var Game $game */
            $game = $this->gameRepository->getOneBySlugEnabledGame($query->gameSlug);
        }

        return $game;
    }
}
