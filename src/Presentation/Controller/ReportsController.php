<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Command\Game\CreateGameWithCacheCheckCommand;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\GetGameBySlugQuery;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;
use App\Presentation\Form\CreateReportForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/reports')]
class ReportsController extends AbstractController
{
    #[Route(path: '/{gameSlug}', name: 'reports_for_game', methods: [Request::METHOD_GET])]
    public function reportsForGame(
        string $gameSlug,
        MessageBusInterface $messageBus,
        MessageBusHelper $messageBusHelper,
    ): Response {
        $gameEnvelope = $messageBus->dispatch(new GetGameBySlugQuery($gameSlug));

        /** @var ?Game $game */
        $game = $messageBusHelper->getContentFromEnvelope(
            envelope: $gameEnvelope,
            logErrorMessage: 'Game retrieval failed',
            class: Game::class,
        );

        if (null === $game) {
            try {
                $messageBus->dispatch(new CreateGameWithCacheCheckCommand($gameSlug));

                $gameEnvelope = $messageBus->dispatch(new GetGameBySlugQuery($gameSlug));

                /** @var ?Game $game */
                $game = $messageBusHelper->getContentFromEnvelope(
                    envelope: $gameEnvelope,
                    logErrorMessage: 'Game retrieval failed',
                    class: Game::class,
                );
            } catch (ExceptionInterface $e) {
                return $this->render(
                    '@app/reports/reports_for_game.html.twig',
                    [
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        if (null === $game) {
            return $this->render(
                '@app/reports/reports_for_game.html.twig',
                [
                    'error' => 'Game retrieval failed',
                ]
            );
        }

        $reports = $game->getReports()->toArray();

        if (!empty($reports)) {
            /** @var array<int, Report> $reports */
            usort($reports, static function ($a, $b) {
                if ($a->getUpvoteCount() !== $b->getUpvoteCount()) {
                    return $b->getUpvoteCount() <=> $a->getUpvoteCount();
                }

                return $b->getCreatedAt() <=> $a->getCreatedAt();
            });
        }

        $createReportForm = $this->createForm(
            CreateReportForm::class,
            null,
            [
                'action' => $this->generateUrl('create_report'),
                'method' => Request::METHOD_POST,
            ]
        );

        return $this->render(
            '@app/reports/reports_for_game.html.twig',
            [
                'game' => $game,
                'reports' => $reports ?? [],
                'gameSlug' => $game->getSlug(),
                'createReportForm' => $createReportForm->createView(),
            ]
        );
    }
}
