<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/reports')]
class ReportsController extends AbstractController
{
    #[Route(path: '/{gameSlug}', name: 'reports_for_game', methods: [Request::METHOD_GET])]
    public function reportsForGame(string $gameSlug): Response
    {
        return $this->render(
            '@app/reports/reports_for_game.html.twig',
            [
                'gameSlug' => $gameSlug,
            ]
        );
    }
}
