<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Factory\Game\GameFactory;
use App\Domain\Model\Game\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GameFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /** @var Publisher $testPublisher */
        $testPublisher = $this->getReference(
            PublisherFixtures::PUBLISHER_TEST,
            Publisher::class,
        );

        $definedGame = GameFactory::create(
            name: 'Splinter Turtle',
            slug: 'splinter-turtle',
            description: $faker->text,
            releaseDate: (string) new \DateTime()->getTimestamp(),
            imageCover: $faker->imageUrl(),
            publisher: $testPublisher,
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

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [PublisherFixtures::class];
    }
}
