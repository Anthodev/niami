<?php

declare(strict_types=1);

namespace App\Shared\Dto\Game;

class IgdbPublisherOutputDto
{
    public function __construct(
        public readonly string $name,
        public readonly int $apiId,
    ) {
    }
}
