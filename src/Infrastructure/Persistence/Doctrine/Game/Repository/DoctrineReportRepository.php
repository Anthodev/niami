<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Game\Repository;

use App\Domain\Model\Game\Report;
use App\Domain\Repository\Game\ReportRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Common\DoctrineBaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineReportRepository extends DoctrineBaseEntityRepository implements ReportRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @return Report[]
     */
    public function getAllVisibleReports(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.isVisible = true')
            ->getQuery();

        /** @var Report[] */
        return $qb->getResult();
    }
}
