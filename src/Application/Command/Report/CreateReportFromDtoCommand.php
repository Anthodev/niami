<?php

declare(strict_types=1);

namespace App\Application\Command\Report;

use App\Shared\Dto\Report\CreateReportFormInputDto;

readonly class CreateReportFromDtoCommand
{
    public function __construct(
        public CreateReportFormInputDto $formInputDto,
    ) {
    }
}
