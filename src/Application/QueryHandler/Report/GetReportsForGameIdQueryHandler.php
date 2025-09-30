<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Report;

use App\Application\Fetcher\Report\ReportFetcher;
use App\Application\Query\Report\GetReportsForGameIdQuery;
use App\Domain\Model\Report\Report;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetReportsForGameIdQueryHandler
{
    public function __construct(private ReportFetcher $reportFetcher)
    {
    }

    /**
     * @return Report[]
     */
    public function __invoke(GetReportsForGameIdQuery $query): array
    {
        return $this->reportFetcher->getAllVisibleReportsForGame(
            $query->gameId,
            $query->gameSlug,
        );
    }
}
