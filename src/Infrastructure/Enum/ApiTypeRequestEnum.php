<?php

declare(strict_types=1);

namespace App\Infrastructure\Enum;

enum ApiTypeRequestEnum: string
{
    case SEARCH = 'search';
    case SLUG = 'slug';
}
