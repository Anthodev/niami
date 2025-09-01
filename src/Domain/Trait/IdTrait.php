<?php

declare(strict_types=1);

namespace App\Domain\Trait;

use Symfony\Component\Uid\Uuid;

trait IdTrait
{
    private ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setDefaultId(): self
    {
        $this->id = Uuid::v7()->toRfc4122();

        return $this;
    }
}
