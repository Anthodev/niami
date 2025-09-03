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
    public const string FIRST_GAME_SLUG = 'splinter-turtle';
    public const string RANDOM_GAME_SLUG = 'random-game-';

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
            slug: self::FIRST_GAME_SLUG,
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

            $this->addReference(self::RANDOM_GAME_SLUG.$i, $game);
        }

        $manager->flush();

        $this->addReference(self::FIRST_GAME_SLUG, $definedGame);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            PublisherFixtures::class,
        ];
    }
}
