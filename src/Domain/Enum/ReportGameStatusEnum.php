<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum ReportGameStatusEnum: string
{
    case GREAT = 'great';
    case OK = 'ok';
    case BAD = 'bad';
    case BUGGED = 'bugged';
}
