<?php

namespace App\Presentation\Controller;

use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        ReportRepositoryInterface $reportRepository,
    ): Response {
        $report->increaseUpvoteCount();
        $reportRepository->save($report);

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            /** @var string $currentReportGameId */
            $currentReportGameId = $report->getGame()->getId();
            $mostUpvotedReport = $reportRepository->findMostUpvotedReportForGame($currentReportGameId);

            /** @var string $mostUpvotedReportId */
            $mostUpvotedReportId = $mostUpvotedReport?->getId();

            return $this->render('@turbo/reports/increase_upvote.html.twig', [
                'reportId' => $report->getId(),
                'reportUpvoteCount' => $report->getUpvoteCount(),
                'mostUpvotedReportId' => $mostUpvotedReportId,
            ]);
        }

        return $this->json(['message' => 'Upvote count increased']);
    }
}
