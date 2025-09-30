<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Report\Repository;

use App\Domain\Model\Report\Report;
use App\Domain\Repository\Report\ReportRepositoryInterface;
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
    public function getAllVisibleReportsForGame(string $gameId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r', 'g')
            ->where('r.isVisible = true')
            ->andWhere('r.game = :game')
            ->innerJoin('r.game', 'g')
            ->setParameter('game', $gameId)
            ->orderBy('r.upvoteCount', 'DESC')
            ->addOrderBy('r.createdAt', 'ASC')
            ->getQuery();

        /** @var Report[] */
        return $qb->getResult();
    }

    public function findMostUpvotedReportForGame(string $gameId): ?Report
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r', 'g')
            ->where('r.isVisible = true')
            ->andWhere('r.game = :game')
            ->innerJoin('r.game', 'g')
            ->setParameter('game', $gameId)
            ->orderBy('r.upvoteCount', 'DESC')
            ->addOrderBy('r.createdAt', 'ASC')
            ->setMaxResults(1);

        /** @var Report|null */
        return $qb->getQuery()->getOneOrNullResult();
    }
}
