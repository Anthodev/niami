<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Report\Repository;

use App\Domain\Model\Report\ReportComment;
use App\Domain\Repository\Report\ReportCommentRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Common\DoctrineBaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineReportCommentRepository extends DoctrineBaseEntityRepository implements ReportCommentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportComment::class);
    }
}
