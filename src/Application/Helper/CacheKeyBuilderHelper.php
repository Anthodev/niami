<?php

declare(strict_types=1);

namespace App\Application\Helper;

readonly class CacheKeyBuilderHelper
{
    public static function build(string $prefix, string ...$suffixes): string
    {
        return sprintf('%s_%s', $prefix, implode('_', $suffixes));
    }
}
