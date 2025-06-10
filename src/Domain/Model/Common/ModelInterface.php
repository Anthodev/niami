<?php

declare(strict_types=1);

namespace App\Domain\Model\Common;

interface ModelInterface
{
    public function getId(): ?string;

    public function setId(string $id): self;

    public function setDefaultId(): self;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $createdAt): self;

    public function getUpdatedAt(): ?\DateTime;

    public function setUpdatedAt(\DateTime $updatedAt): self;
}
