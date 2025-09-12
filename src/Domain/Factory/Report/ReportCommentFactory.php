<?php

declare(strict_types=1);

namespace App\Domain\Factory\Report;

use App\Domain\Model\Report\Report;
use App\Domain\Model\Report\ReportComment;

class ReportCommentFactory
{
    public static function create(
        string $comment,
        string $ip,
        Report $report,
    ): ReportComment {
        return new ReportComment(
            comment: $comment,
            ip: $ip,
            report: $report,
        );
    }
}
