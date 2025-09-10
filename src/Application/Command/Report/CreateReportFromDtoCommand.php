<?php

declare(strict_types=1);

namespace App\Application\Command\Report;

use App\Presentation\Dto\CreateReportFormInputDto;

readonly class CreateReportFromDtoCommand
{
    public function __construct(
        public CreateReportFormInputDto $formInputDto,
    ) {
    }
}
