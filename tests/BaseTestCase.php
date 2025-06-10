<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Model\User\User;
use App\Infrastructure\Enum\RoleCodeEnum;
use App\Infrastructure\Factory\User\UserFactory;
use App\Infrastructure\Persistence\Doctrine\User\Repository\DoctrineRoleRepository;
use App\Tests\Trait\UtilsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class BaseTestCase extends KernelTestCase
{
    use UtilsTrait;

    protected const string DEFAULT_USER_EMAIL = 'test@test.io';

    protected readonly EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
    }

    public function makeUser(
        string $email = self::DEFAULT_USER_EMAIL,
        string $username = 'Test',
        string $password = 'test1234',
        bool $enabled = true,
    ): User {
        $roleRepository = static::getContainer()->get(DoctrineRoleRepository::class);
        $roleUser = $roleRepository->findOneBy(['code' => RoleCodeEnum::ROLE_USER->value]);

        $user = UserFactory::makeVerifiedUserWithRole(
            email: $email,
            username: $username,
            role: $roleUser,
            plainPassword: $password
        );

        if (false === $enabled) {
            $user->setEnabled(false);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
