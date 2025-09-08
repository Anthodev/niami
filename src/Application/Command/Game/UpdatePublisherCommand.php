<?php

declare(strict_types=1);

namespace App\Application\Command\Game;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdatePublisherCommand
{
    public function __construct(
        #[Assert\NotNull]
        public int $publisherApiId,
        #[Assert\NotBlank]
        public string $name,
        public string $website,
    ) {
    }
}
