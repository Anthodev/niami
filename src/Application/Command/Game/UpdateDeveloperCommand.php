<?php

declare(strict_types=1);

namespace App\Application\Command\Game;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateDeveloperCommand
{
    public function __construct(
        #[Assert\NotNull]
        public int $developerApiId,
        #[Assert\NotBlank]
        public string $name,
        public ?string $website,
    ) {
    }
}
