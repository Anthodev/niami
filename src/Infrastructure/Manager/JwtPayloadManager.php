<?php

declare(strict_types=1);

namespace App\Infrastructure\Manager;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class JwtPayloadManager
{
    public function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
        private Security $security,
    ) {
    }

    public function getJwtToken(): string
    {
        $user = $this->security->getUser();
        \assert($user instanceof UserInterface);

        return $this->jwtTokenManager->create($user);
    }
}
