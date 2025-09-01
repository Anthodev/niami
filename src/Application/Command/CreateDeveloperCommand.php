<?php

declare(strict_types=1);

namespace App\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateDeveloperCommand
{
    public function __construct(
        #[Assert\NotBlank] public string $name,
        public ?string $website,
        #[Assert\NotNull] public int $apiId,
    ) {
    }
}
