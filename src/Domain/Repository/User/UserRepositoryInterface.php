<?php

declare(strict_types=1);

namespace App\Domain\Repository\User;

use App\Domain\Model\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method ?User  find(string $id)
 * @method User[] findAll()
 * @method User[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?User  findOneBy(array<string, mixed> $criteria)
 * @method void   save(User $user)
 * @method void   update(User $user)
 * @method void   delete(User $user)
 * @method void   refresh(User $user)
 * @method void   rollback()
 */
interface UserRepositoryInterface
{
    public function findOneByEmailOrUsername(string $email, string $username): ?User;

    /**
     * @return User[]
     */
    public function getAllEnabled(): array;

    public function getOneByIdEnabled(string $id): ?User;

    public function loadUserByIdentifier(string $identifier): ?UserInterface;
}
