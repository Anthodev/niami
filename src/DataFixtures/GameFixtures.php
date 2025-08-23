<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Factory\Game\GameFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GameFixtures extends Fixture
{
    /**
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $definedGame = GameFactory::create(
            name: 'Splinter Turtle',
            slug: 'splinter-turtle',
            description: $faker->text,
            releaseDate: (string) new \DateTime()->getTimestamp(),
            imageCover: $faker->imageUrl(),
        );

        $manager->persist($definedGame);

        for ($i = 0; $i < 3; ++$i) {
            $releaseDatePeriod = $faker->dateTimeBetween('-3 year', '-1 year');

            $game = GameFactory::create(
                name: $faker->unique()->word(),
                slug: $faker->slug,
                description: $faker->text,
                releaseDate: $releaseDatePeriod->format(DATE_ATOM),
                imageCover: $faker->imageUrl(),
            );

            $manager->persist($game);
        }

        $manager->flush();
    }
}
