<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Command\Report\IncreaseUpvoteCountCommand;
use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\UX\Turbo\TurboBundle;

class ReportIncreaseUpvoteCountController extends AbstractController
{
    #[
        Route(
            path: '/reports/{id}/increase_upvote',
            name: 'report_increase_upvote',
            requirements: ['id' => Requirement::UUID],
            methods: [Request::METHOD_PATCH],
        ),
    ]
    public function __invoke(
        Report $report,
        Request $request,
        MessageBusInterface $messageBus,
        ReportRepositoryInterface $reportRepository,
    ): Response {
        /** @var string $reportId */
        $reportId = $report->getId();
        /** @var string $gameSlug */
        $gameSlug = $report->getGame()->getSlug();

        $messageBus->dispatch(new IncreaseUpvoteCountCommand($reportId, $gameSlug));

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            /** @var string $gameId */
            $gameId = $report->getGame()->getId();
            $mostUpvotedReport = $reportRepository->findMostUpvotedReportForGame($gameId);

            return $this->render('@turbo/reports/increase_upvote.html.twig', [
                'reportId' => $reportId,
                'reportUpvoteCount' => $report->getUpvoteCount() + 1,
                'mostUpvotedReportId' => $mostUpvotedReport?->getId(),
            ]);
        }

        return $this->json(['message' => 'Upvote count increased']);
    }
}
