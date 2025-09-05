<?php

declare(strict_types=1);

namespace App\Shared\Dto\Game;

use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;

readonly class SearchResultsOutputDto
{
    public function __construct(
        public string $searchQuery,
        /** @var array{games:array<ApiGame|Game>, total:int} */
        public array $results,
        public bool $hasError = false,
        public string $errorMessage = '',
    ) {
    }
}
