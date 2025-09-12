<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Command\Report\CreateReportFromDtoCommand;
use App\Presentation\Dto\CreateReportFormInputDto;
use App\Presentation\Form\CreateReportForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ReportCreateController extends AbstractController
{
    #[Route(path: '/reports/new', name: 'create_report', methods: [Request::METHOD_POST])]
    public function __invoke(
        Request $request,
        MessageBusInterface $messageBus,
    ): Response {
        $createReportForm = $this->createForm(CreateReportForm::class);
        $createReportForm->handleRequest($request);

        $dataGameSlug = $request->request->all()['create_report_form']['gameSlug'];

        if (
            $createReportForm->isSubmitted()
            && 0 === $createReportForm->getErrors()->count()
        ) {
            /** @var CreateReportFormInputDto $createReportInputDto */
            $createReportInputDto = $createReportForm->getData();

            try {
                $messageBus->dispatch(new CreateReportFromDtoCommand($createReportInputDto));
            } catch (ExceptionInterface) {
                return $this->json('Error while creating a comment', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return $this->redirectToRoute('reports_for_game', ['gameSlug' => $dataGameSlug]);
    }
}
