<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Exception\CannotGetGameException;
use App\Application\UseCase\Game\GetGameUseCase;
use App\Domain\Model\Game\Game;
use App\Domain\Model\Report\Report;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/reports')]
class ReportsController extends AbstractController
{
    #[Route(path: '/{gameSlug}', name: 'reports_for_game', methods: [Request::METHOD_GET])]
    public function reportsForGame(
        string $gameSlug,
        GetGameUseCase $getGameUseCase,
    ): Response {
        try {
            /** @var Game $game */
            $game = $getGameUseCase->execute($gameSlug);
        } catch (CannotGetGameException $e) {
            return $this->render(
                '@app/reports/reports_for_game.html.twig',
                [
                    'error' => $e->getMessage(),
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

        return $this->render(
            '@app/reports/reports_for_game.html.twig',
            [
                'game' => $game,
                'reports' => $reports ?? [],
                'gameSlug' => $game->getSlug(),
            ]
        );
    }
}
