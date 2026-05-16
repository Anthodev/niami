<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Enum\ReportGameStatusEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ReportGameStatusEnumType extends Type
{
    public const string NAME = 'ReportGameStatusEnum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ReportGameStatusEnum
    {
        if (!is_string($value)) {
            return null;
        }

        return ReportGameStatusEnum::from((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ReportGameStatusEnum) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', ReportGameStatusEnum::class, gettype($value)));
        }

        return $value->value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
