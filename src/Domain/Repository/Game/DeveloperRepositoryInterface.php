<?php

declare(strict_types=1);

namespace App\Domain\Repository\Game;

use App\Domain\Model\Game\Developer;

/**
 * @method ?Developer  find(string $id)
 * @method Developer[] findAll()
 * @method Developer[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?Developer  findOneBy(array<string, mixed> $criteria)
 * @method void        save(Developer $developer)
 * @method void        update(Developer $developer)
 * @method void        delete(Developer $developer)
 * @method void        refresh(Developer $developer)
 * @method void        rollback()
 */
interface DeveloperRepositoryInterface
{
    public function findOneByName(string $name): ?Developer;

    public function findOneByApiId(int $apiId): ?Developer;
}
