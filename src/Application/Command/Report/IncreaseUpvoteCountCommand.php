<?php

declare(strict_types=1);

namespace App\Application\Command\Report;

readonly class IncreaseUpvoteCountCommand
{
    public function __construct(
        public string $reportId,
        public string $gameSlug,
    ) {
    }
}
