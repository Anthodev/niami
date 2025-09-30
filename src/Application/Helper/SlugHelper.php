<?php

declare(strict_types=1);

namespace App\Application\Helper;

readonly class SlugHelper
{
    public static function slugify(string $stringToSlugify): string
    {
        return strtolower(str_replace(' ', '-', $stringToSlugify));
    }
}
