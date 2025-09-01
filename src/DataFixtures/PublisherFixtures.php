<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Model\Game\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PublisherFixtures extends Fixture
{
    public const PUBLISHER_TEST = 'publisher_test';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $testPublisher = new Publisher();
        $testPublisher->setName('Test Publisher');
        $testPublisher->setWebsite($faker->url);
        $testPublisher->setApiId($faker->randomNumber());
        $manager->persist($testPublisher);

        $publisher = new Publisher();
        $publisher->setName($faker->company);
        $publisher->setWebsite($faker->url);
        $publisher->setApiId($faker->randomNumber());
        $manager->persist($publisher);

        $manager->flush();

        $this->addReference(self::PUBLISHER_TEST, $testPublisher);
    }
}
