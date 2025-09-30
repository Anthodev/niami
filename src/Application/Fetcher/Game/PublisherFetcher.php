<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Game;

use App\Application\Enum\CacheDurationEnum;
use App\Application\Helper\SlugHelper;
use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class PublisherFetcher
{
    public const string PUBLISHER_CACHE_KEY = 'publisher_';

    public function __construct(
        private PublisherRepositoryInterface $publisherRepository,
        private CacheInterface $cache,
    ) {
    }

    public function findOneByName(string $name): ?Publisher
    {
        /** @var ?Publisher */
        return $this->cache->get(
            self::PUBLISHER_CACHE_KEY.SlugHelper::slugify($name),
            function (ItemInterface $item) use ($name) {
                $publisher = $this->publisherRepository->findOneByName($name);

                if (null === $publisher) {
                    return null;
                }

                $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

                return $publisher;
            },
        );
    }

    public function findOneByApiId(int $apiId, string $name): ?Publisher
    {
        /** @var ?Publisher */
        return $this->cache->get(self::PUBLISHER_CACHE_KEY.$name, function (
            ItemInterface $item,
        ) use ($apiId) {
            $publisher = $this->publisherRepository->findOneByApiId($apiId);

            if (null === $publisher) {
                return null;
            }

            $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

            return $publisher;
        });
    }

    public function deleteCacheName(string $name): void
    {
        $this->cache->delete(
            self::PUBLISHER_CACHE_KEY.SlugHelper::slugify($name),
        );
    }
}
