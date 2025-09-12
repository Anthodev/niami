<?php

declare(strict_types=1);

namespace App\Application\Query\Report;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GetReportByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public string $reportId,
    ) {
    }
}
