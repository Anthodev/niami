<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use Symfony\Bundle\SecurityBundle\Security;

trait WebUtilsTrait
{
    public function getSecurity(): Security
    {
        return static::$client->getContainer()->get(Security::class);
    }
}
