<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Report;

use App\Application\Command\Report\CreateReportCommentCommand;
use App\Application\Exception\Report\ReportNotFoundException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Report\GetReportByIdQuery;
use App\Domain\Factory\Report\ReportCommentFactory;
use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportCommentRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateReportCommentCommandHandler
{
    public function __construct(
        private readonly ReportCommentRepositoryInterface $reportCommentRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusHelper $messageBusHelper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateReportCommentCommand $command): void
    {
        $reportEnvelope = $this->messageBus->dispatch(
            new GetReportByIdQuery($command->reportId),
        );
        /** @var Report $report */
        $report = $this->messageBusHelper->getContentFromEnvelope(
            envelope: $reportEnvelope,
            logErrorMessage: 'Report not found',
            class: Report::class,
        );

        if (null === $report) {
            $this->logger->error(
                sprintf('Report %s not found', $command->reportId),
            );
            throw new ReportNotFoundException();
        }

        $reportComment = ReportCommentFactory::create(
            comment: $command->comment,
            ip: $command->ip,
            report: $report,
        );

        $this->reportCommentRepository->save($reportComment);
    }
}
