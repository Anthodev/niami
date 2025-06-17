<?php

declare(strict_types=1);

namespace App\Shared\Dto\Game;

readonly class IgdbSearchResponseDto
{
    public function __construct(
        public string $name,
        public string $slug,
        /** @var array<string, mixed> $involvedCompanies */
        public array $involvedCompanies,
        /** @var array<string, mixed> $cover */
        public array $cover,
        public int $firstReleaseDate,
        public string $summary,
        /** @var array<string, mixed> $websites */
        public array $websites,
    ) {
    }
}
