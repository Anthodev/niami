<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Game\Repository;

use App\Domain\Model\Game\Publisher;
use App\Domain\Repository\Game\PublisherRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Common\DoctrineBaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePublisherRepository extends DoctrineBaseEntityRepository implements PublisherRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publisher::class);
    }

    public function findByName(string $name): ?Publisher
    {
        /** @var ?Publisher */
        return $this->findOneBy(['name' => $name]);
    }
}
