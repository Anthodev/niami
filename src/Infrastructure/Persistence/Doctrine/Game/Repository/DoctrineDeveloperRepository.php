<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Game\Repository;

use App\Domain\Model\Game\Developer;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Common\DoctrineBaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineDeveloperRepository extends DoctrineBaseEntityRepository implements DeveloperRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Developer::class);
    }

    public function findByName(string $name): ?Developer
    {
        /** @var ?Developer */
        return $this->findOneBy(['name' => $name]);
    }

    public function findByApiId(int $apiId): ?Developer
    {
        /** @var ?Developer */
        return $this->findOneBy(['apiId' => $apiId]);
    }
}
