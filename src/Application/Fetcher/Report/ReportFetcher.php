<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Report;

use App\Application\Enum\CacheDurationEnum;
use App\Application\Helper\CacheKeyBuilderHelper;
use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class ReportFetcher
{
    public const string REPORTS_CACHE_KEY = 'reports_';

    public function __construct(
        private ReportRepositoryInterface $repository,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @return Report[]
     */
    public function getAllVisibleReportsForGame(
        string $gameId,
        string $gameSlug,
    ): array {
        /** @var Report[] */
        return $this->cache->get(
            CacheKeyBuilderHelper::build(self::REPORTS_CACHE_KEY, $gameSlug),
            function (ItemInterface $item) use ($gameId) {
                $reports = $this->repository->getAllVisibleReportsForGame(
                    $gameId,
                );

                if (empty($reports)) {
                    return [];
                }

                $item->expiresAfter(CacheDurationEnum::THREE_DAYS->value);

                return $reports;
            },
        );
    }

    public function deleteCache(string $key): bool
    {
        return $this->cache->delete($key);
    }
}
