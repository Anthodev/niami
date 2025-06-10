<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory\User;

use App\Domain\Model\User\Role;
use App\Domain\Model\User\User;

class UserFactory
{
    public static function makeUser(
        string $email,
        string $username,
        ?string $password = null,
        ?string $plainPassword = null,
    ): User {
        return new User(
            email: $email,
            username: $username,
            password: $password,
            plainPassword: $plainPassword,
        );
    }

    public static function makeVerifiedUser(
        string $email,
        string $username,
        ?string $password = null,
        ?string $plainPassword = null,
    ): User {
        $user = self::makeUser($email, $username, $password, $plainPassword);
        $user->setEnabled(true);

        return $user;
    }

    public static function makeVerifiedUserWithRole(
        string $email,
        string $username,
        Role $role,
        ?string $password = null,
        ?string $plainPassword = null,
    ): User {
        $user = self::makeVerifiedUser($email, $username, $password, $plainPassword);
        $user->setRole($role);

        return $user;
    }
}
