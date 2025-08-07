<?php

declare(strict_types=1);

namespace App\Infrastructure\Enum;

enum IgdbGameTypeEnum: int
{
    case MAIN_GAME = 0;
    case REMAKE = 8;
    case EXPANDED_GAME = 10;
    case PORT = 11;
}
