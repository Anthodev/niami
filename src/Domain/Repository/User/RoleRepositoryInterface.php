<?php

declare(strict_types=1);

namespace App\Domain\Repository\User;

use App\Domain\Model\User\Role;

/**
 * @method ?Role  find(string $id)
 * @method Role[] findAll()
 * @method Role[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method ?Role  findOneBy(array<string, mixed> $criteria)
 * @method void   save(Role $role)
 * @method void   update(Role $role)
 * @method void   delete(Role $role)
 * @method void   refresh(Role $role)
 * @method void   rollback()
 */
interface RoleRepositoryInterface
{
}
