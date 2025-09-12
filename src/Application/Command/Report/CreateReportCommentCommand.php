<?php

declare(strict_types=1);

namespace App\Application\Command\Report;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateReportCommentCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $comment,
        #[Assert\NotBlank, Assert\Ip]
        public string $ip,
        #[Assert\NotBlank]
        public string $reportId,
    ) {
    }
}
