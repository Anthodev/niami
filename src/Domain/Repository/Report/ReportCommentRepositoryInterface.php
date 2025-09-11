<?php

declare(strict_types=1);

namespace App\Domain\Repository\Report;

use App\Domain\Model\Report\ReportComment;

/**
 * @method ?ReportComment  find(string $id)
 * @method ReportComment[] findAll()
 * @method ReportComment[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?ReportComment  findOneBy(array<string, mixed> $criteria)
 * @method void            save(ReportComment $report)
 * @method void            update(ReportComment $report)
 * @method void            delete(ReportComment $report)
 * @method void            refresh(ReportComment $report)
 * @method void            rollback()
 */
interface ReportCommentRepositoryInterface
{
}
