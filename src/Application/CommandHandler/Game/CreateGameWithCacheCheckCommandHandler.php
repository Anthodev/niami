<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateGameCommand;
use App\Application\Command\Game\CreateGameWithCacheCheckCommand;
use App\Application\Exception\Game\CannotCreateGameException;
use App\Domain\Model\Game\ApiGame;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
class CreateGameWithCacheCheckCommandHandler
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CannotCreateGameException|ExceptionInterface
     */
    public function __invoke(CreateGameWithCacheCheckCommand $command): void
    {
        /** @var ?ApiGame $apiGame */
        $apiGame = $this->cache->get(
            'api_game_'.$command->gameSlug,
            function (): ?ApiGame {
                return null;
            },
        );

        if (null === $apiGame) {
            return;
        }

        try {
            $this->messageBus->dispatch(
                new CreateGameCommand(
                    name: $apiGame->getName(),
                    slug: $apiGame->getSlug(),
                    releaseDate: $apiGame->getReleaseDate(),
                    imageCover: $apiGame->getImageCover(),
                    publisher: $apiGame->getPublisher(),
                    developer: $apiGame->getDeveloper(),
                    description: $apiGame->getDescription(),
                )
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CannotCreateGameException();
        }
    }
}
