<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Report;

use App\Application\Query\Report\GetReportByIdQuery;
use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportRepositoryInterface;

class GetReportByIdQueryHandler
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository,
    ) {
    }

    public function __invoke(GetReportByIdQuery $query): ?Report
    {
        /** @var ?Report */
        return $this->reportRepository->find($query->reportId);
    }
}
