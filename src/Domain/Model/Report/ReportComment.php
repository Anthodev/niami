<?php

declare(strict_types=1);

namespace App\Domain\Model\Report;

use App\Domain\Model\Common\ModelInterface;
use App\Domain\Trait\IdTrait;
use App\Domain\Trait\TimestampableTrait;

class ReportComment implements ModelInterface
{
    use IdTrait;
    use TimestampableTrait;

    public function __construct(
        private string $comment,
        private string $ip,
        private Report $report,
    ) {
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): self
    {
        $this->report = $report;

        return $this;
    }
}
