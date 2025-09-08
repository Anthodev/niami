<?php

declare(strict_types=1);

namespace App\Application\Command\Game;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreatePublisherCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        public ?string $website,
        #[Assert\NotNull]
        public int $apiId,
    ) {
    }
}
