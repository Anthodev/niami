<?php

declare(strict_types=1);

namespace App\Shared\Dto\Game;

readonly class IgdbSearchResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        /** @var array<string, mixed> $involved_companies */
        public array $involved_companies = [],
        /** @var array<string, mixed>|null $cover */
        public ?array $cover = null,
        public ?int $first_release_date = null,
        public ?string $summary = null,
        /** @var array<string, mixed> $websites */
        public array $websites = [],
    ) {
    }
}
