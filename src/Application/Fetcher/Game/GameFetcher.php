<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Game;

use App\Application\Enum\CacheDurationEnum;
use App\Application\Helper\CacheKeyBuilderHelper;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class GameFetcher
{
    public const string GAME_CACHE_KEY = 'game-';

    public function __construct(
        private GameRepositoryInterface $gameRepository,
        private CacheInterface $cache,
    ) {
    }

    public function getOneBySlugEnabledGame(string $slug): ?Game
    {
        $cacheKey = CacheKeyBuilderHelper::build(
            self::GAME_CACHE_KEY.'getOneBySlugEnabledGame',
            $slug,
        );

        $game = $this->cache->get($cacheKey, function (
            ItemInterface $item,
        ) use ($slug) {
            $game = $this->gameRepository->getOneBySlugEnabledGame($slug);

            if (null === $game) {
                $item->expiresAfter(10);

                return null;
            }

            $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

            return $game;
        });

        return $game;
    }

    public function getOneByIdEnabledGame(string $gameId): ?Game
    {
        return $this->cache->get(
            CacheKeyBuilderHelper::build(
                self::GAME_CACHE_KEY.'getOneByIdEnabledGame',
                $gameId,
            ),
            function (ItemInterface $item) use ($gameId) {
                $game = $this->gameRepository->getOneByIdEnabledGame(
                    gameId: $gameId,
                );

                if (null === $game) {
                    return null;
                }

                $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

                return $game;
            },
        );
    }

    public function deleteCacheForSlug(string $slug): bool
    {
        return $this->cache->delete(
            CacheKeyBuilderHelper::build(
                self::GAME_CACHE_KEY.'getOneBySlugEnabledGame',
                $slug,
            ),
        );
    }
}
