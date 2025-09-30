<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Game;

use App\Application\Enum\CacheDurationEnum;
use App\Application\Helper\SlugHelper;
use App\Domain\Model\Game\Developer;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class DeveloperFetcher
{
    public const DEVELOPER_CACHE_KEY = 'developer_';

    public function __construct(
        private DeveloperRepositoryInterface $developerRepository,
        private CacheInterface $cache,
    ) {
    }

    public function findOneByName(string $developerName): ?Developer
    {
        $sluggedDeveloperName = SlugHelper::slugify($developerName);

        /** @var ?Developer */
        return $this->cache->get(
            self::DEVELOPER_CACHE_KEY.$sluggedDeveloperName,
            function (ItemInterface $item) use ($developerName) {
                $developer = $this->developerRepository->findOneByName(
                    $developerName,
                );

                if (null === $developer) {
                    return null;
                }

                $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

                return $developer;
            },
        );
    }

    public function findOneByApiId(
        int $developerApiId,
        string $developerName,
    ): ?Developer {
        /** @var ?Developer */
        return $this->cache->get(
            self::DEVELOPER_CACHE_KEY.$developerName,
            function (ItemInterface $item) use ($developerApiId) {
                $developer = $this->developerRepository->findOneByApiId($developerApiId);

                if (null === $developer) {
                    return null;
                }

                $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

                return $developer;
            },
        );
    }

    public function deleteCacheName(string $developerName): void
    {
        $this->cache->delete(
            self::DEVELOPER_CACHE_KEY.SlugHelper::slugify($developerName),
        );
    }
}
