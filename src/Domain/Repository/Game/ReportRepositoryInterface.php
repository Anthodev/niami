<?php

declare(strict_types=1);

namespace App\Domain\Repository\Game;

use App\Domain\Model\Game\Report;

/**
 * @method ?Report  find(string $id)
 * @method Report[] findAll()
 * @method Report[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?Report  findOneBy(array<string, mixed> $criteria)
 * @method void     save(Report $report)
 * @method void     update(Report $report)
 * @method void     delete(Report $report)
 * @method void     refresh(Report $report)
 * @method void     rollback()
 */
interface ReportRepositoryInterface
{
    /**
     * @return Report[]
     */
    public function getAllVisibleReports(): array;
}
