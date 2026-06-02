<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Domain\Enum\ReportGameStatusEnum;
use App\Domain\Factory\Report\ReportFactory;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route(path: '/test/report/new/{gameId}', name: 'test_create_report', methods: [Request::METHOD_GET], env: 'dev')]
    public function createReport(
        string $gameId,
        GameRepositoryInterface $gameRepository,
        ReportRepositoryInterface $reportRepository,
    ): Response {
        $faker = Factory::create();

        /** @var Game $game */
        $game = $gameRepository->find($gameId);

        /** @var ReportGameStatusEnum $randomGameStatus */
        $randomGameStatus = $faker->randomElement(ReportGameStatusEnum::cases());

        $newReport = ReportFactory::create(
            game: $game,
            isSwitch2Edition: $faker->boolean,
            is60FpsPortable: $faker->boolean,
            hasStableFrameratePortable: $faker->boolean,
            hasResolutionImprovedPortable: $faker->boolean,
            isNativeResolutionPortable: $faker->boolean,
            is60FpsDocked: $faker->boolean,
            hasStableFramerateDocked: $faker->boolean,
            hasResolutionImprovedDocked: $faker->boolean,
            isNativeResolutionImprovedDocked: $faker->boolean,
            hasImprovedLoadingTimes: $faker->boolean,
            gameStatus: $randomGameStatus,
            upvoteCount: $faker->numberBetween(0, 100),
        );

        $reportRepository->save($newReport);

        return $this->redirectToRoute('reports_for_game', ['gameSlug' => $game->getSlug()]);
    }
}
