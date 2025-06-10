<?php

declare(strict_types=1);

namespace App\Infrastructure\Enum;

enum RoleCodeEnum: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
}
