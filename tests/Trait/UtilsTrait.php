<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use Faker\Factory;
use Faker\Generator;

trait UtilsTrait
{
    public static function getFaker(): Generator
    {
        return Factory::create('fr_FR');
    }
}
