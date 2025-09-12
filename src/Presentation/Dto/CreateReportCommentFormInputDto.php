<?php

declare(strict_types=1);

namespace App\Presentation\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateReportCommentFormInputDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $comment = '',
        #[Assert\NotBlank, Assert\Ip]
        public string $ip = '',
        #[Assert\NotBlank]
        public string $gameSlug = '',
    ) {
    }
}
