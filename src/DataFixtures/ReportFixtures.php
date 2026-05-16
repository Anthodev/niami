<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Enum\ReportGameStatusEnum;
use App\Domain\Factory\Report\ReportFactory;
use App\Domain\Model\Game\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReportFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $game = $this->getReference(GameFixtures::FIRST_GAME_SLUG, Game::class);

        $testReport = ReportFactory::create(
            game: $game,
            isSwitch2Edition: $faker->boolean,
            is60FpsPortable: $faker->boolean,
            hasStableFrameratePortable: $faker->boolean,
            hasResolutionImprovedPortable: $faker->boolean,
            isNativeResolutionPortable: $faker->boolean,
            is60FpsDocked: $faker->boolean,
            hasStableFramerateDocked: $faker->boolean,
            hasResolutionImprovedDocked: $faker->boolean,
            hasImprovedLoadingTimes: $faker->boolean,
            gameStatus: ReportGameStatusEnum::GREAT,
            upvoteCount: $faker->numberBetween(0, 100),
        );

        $manager->persist($testReport);

        for ($i = 0; $i < 10; ++$i) {
            $randomGame = $this->getReference(
                GameFixtures::RANDOM_GAME_SLUG.$faker->numberBetween(0, 2),
                Game::class,
            );

            /** @var ReportGameStatusEnum $randomStatus */
            $randomStatus = $faker->randomElement(ReportGameStatusEnum::cases());

            $report = ReportFactory::create(
                game: $randomGame,
                isSwitch2Edition: $faker->boolean,
                is60FpsPortable: $faker->boolean,
                hasStableFrameratePortable: $faker->boolean,
                hasResolutionImprovedPortable: $faker->boolean,
                isNativeResolutionPortable: $faker->boolean,
                is60FpsDocked: $faker->boolean,
                hasStableFramerateDocked: $faker->boolean,
                hasResolutionImprovedDocked: $faker->boolean,
                hasImprovedLoadingTimes: $faker->boolean,
                gameStatus: $randomStatus,
                upvoteCount: $faker->numberBetween(0, 100),
            );

            $manager->persist($report);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            GameFixtures::class,
        ];
    }
}
