<?php

declare(strict_types=1);

namespace App\Domain\Repository\Game;

use App\Domain\Model\Game\Publisher;

/**
 * @method ?Publisher  find(string $id)
 * @method Publisher[] findAll()
 * @method Publisher[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?Publisher  findOneBy(array<string, mixed> $criteria)
 * @method void        save(Publisher $publisher)
 * @method void        update(Publisher $publisher)
 * @method void        delete(Publisher $publisher)
 * @method void        refresh(Publisher $publisher)
 * @method void        rollback()
 */
interface PublisherRepositoryInterface
{
    public function findByName(string $name): ?Publisher;
}
