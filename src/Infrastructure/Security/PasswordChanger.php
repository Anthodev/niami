<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Model\User\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class PasswordChanger
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function changePassword(
        User $user,
        #[\SensitiveParameter] ?string $plainPassword = null,
    ): void {
        /** @var string $plainPassword */
        $plainPassword = $plainPassword ?? $user->getPlainPassword();

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}
