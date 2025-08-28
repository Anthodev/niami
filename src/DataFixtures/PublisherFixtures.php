<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Model\Game\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PublisherFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $publisher = new Publisher();
        $publisher->setName('Test Publisher');
        $publisher->setWebsite($faker->url);
        $publisher->setApiId($faker->randomNumber());
        $manager->persist($publisher);

        $publisher = new Publisher();
        $publisher->setName($faker->company);
        $publisher->setWebsite($faker->url);
        $publisher->setApiId($faker->randomNumber());
        $manager->persist($publisher);

        $manager->flush();
    }
}
