<?php

declare(strict_types=1);

namespace App\Application\Enum;

enum CacheDurationEnum: int
{
    case ONE_HOUR = 3600;
    case SIX_HOURS = 21600;
    case ONE_DAY = 86400;
    case THREE_DAYS = 259200;
}
