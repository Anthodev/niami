<?php

declare(strict_types=1);

namespace App\Application\UseCase\Game;

use App\Application\Exception\CannotGetGameException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameQuery;
use App\Application\Query\Game\GetOrCreateGameQuery;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class GetOrCreateGameUseCase
{
    public function __construct(
        private readonly MessageBusHelper $messageBusHelper,
        private readonly MessageBusInterface $messageBus,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     * @throws CannotGetGameException
     */
    public function execute(
        string $gameSlug,
    ): ?Game {
        $gameEnvelope = $this->messageBus->dispatch(new GetGameQuery($gameSlug));

        /** @var ?Game $game */
        $game = $this->messageBusHelper->getContentFromEnvelope(
            envelope: $gameEnvelope,
            logErrorMessage: 'Game retrieval failed',
            class: Game::class,
        );

        if (null !== $game) {
            return $game;
        }

        $apiGame = $this->cache->get('api_game_'.$gameSlug, function (): ?ApiGame {
            return null;
        });

        if (null === $apiGame) {
            return null;
        }

        try {
            $gameEnvelope = $this->messageBus->dispatch(new GetOrCreateGameQuery($gameSlug, $apiGame));
        } catch (\Exception) {
            throw new CannotGetGameException();
        }

        /** @var Game */
        return $this->messageBusHelper->getContentFromEnvelope(
            envelope: $gameEnvelope,
            logErrorMessage: 'Game retrieval failed',
            class: Game::class,
        );
    }
}
