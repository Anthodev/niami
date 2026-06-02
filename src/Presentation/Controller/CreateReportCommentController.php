<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Command\Report\CreateReportCommentCommand;
use App\Presentation\Dto\CreateReportCommentFormInputDto;
use App\Presentation\Form\CreateReportCommentForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class CreateReportCommentController extends AbstractController
{
    #[
        Route(
            path: '/reports/{reportId}/comment/new',
            name: 'create_report_comment',
            methods: [Request::METHOD_POST],
        ),
    ]
    public function __invoke(
        Request $request,
        string $reportId,
        MessageBusInterface $messageBus,
    ): Response {
        $createReportCommentForm = $this->createForm(
            CreateReportCommentForm::class,
        );
        $createReportCommentForm->handleRequest($request);

        /** @var CreateReportCommentFormInputDto $createReportCommentFormInputDto */
        $createReportCommentFormInputDto = $createReportCommentForm->getData();
        $formDataGameSlug = $createReportCommentFormInputDto->gameSlug;

        if (
            $createReportCommentForm->isSubmitted()
            && 0 === $createReportCommentForm->getErrors()->count()
        ) {
            /** @var CreateReportCommentFormInputDto $createReportCommentFormInputDto */
            $createReportCommentFormInputDto = $createReportCommentForm->getData();

            $messageBus->dispatch(
                new CreateReportCommentCommand(
                    $createReportCommentFormInputDto->comment,
                    $request->getClientIp() ?? '',
                    $reportId,
                ),
            );
        }

        return $this->redirectToRoute('reports_for_game', [
            'gameSlug' => $formDataGameSlug,
        ]);
    }
}
