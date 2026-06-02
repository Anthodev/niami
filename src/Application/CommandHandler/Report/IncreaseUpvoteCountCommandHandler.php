<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Report;

use App\Application\Command\Report\IncreaseUpvoteCountCommand;
use App\Application\Fetcher\Report\ReportFetcher;
use App\Application\Helper\CacheKeyBuilderHelper;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class IncreaseUpvoteCountCommandHandler
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository,
        private readonly ReportFetcher $reportFetcher,
    ) {
    }

    public function __invoke(IncreaseUpvoteCountCommand $command): void
    {
        /** @var \App\Domain\Model\Report\Report $report */
        $report = $this->reportRepository->find($command->reportId);

        $report->increaseUpvoteCount();
        $this->reportRepository->save($report);

        $this->reportFetcher->deleteCache(
            CacheKeyBuilderHelper::build(ReportFetcher::REPORTS_CACHE_KEY, $command->gameSlug),
        );
    }
}
