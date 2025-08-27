<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\UseCase\Report\CreateReportFromDtoUseCase;
use App\Presentation\Dto\CreateReportFormInputDto;
use App\Presentation\Form\CreateReportForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreateReportController extends AbstractController
{
    #[Route(path: '/reports/new', name: 'create_report', methods: [Request::METHOD_POST])]
    public function __invoke(
        Request $request,
        CreateReportFromDtoUseCase $createReportFromDtoUseCase,
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

            $createReportFromDtoUseCase->execute($createReportInputDto);
        }

        return $this->redirectToRoute('reports_for_game', ['gameSlug' => $dataGameSlug]);
    }
}
