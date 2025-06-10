<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Model\User\User;
use App\Infrastructure\Enum\RoleCodeEnum;
use App\Infrastructure\Factory\User\UserFactory;
use App\Infrastructure\Manager\JwtPayloadManager;
use App\Infrastructure\Persistence\Doctrine\User\Repository\DoctrineRoleRepository;
use App\Infrastructure\Persistence\Doctrine\User\Repository\DoctrineUserRepository;
use App\Tests\Trait\WebUtilsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseWebTestCase extends WebTestCase
{
    use WebUtilsTrait;

    protected const string DEFAULT_USER_EMAIL = 'test@test.io';

    protected readonly EntityManagerInterface $entityManager;
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        static::$client = static::createClient(
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
    }

    public function createAuthenticatedClient(string $email): void
    {
        static::$client = static::createClient();
        $this->loginUser($email);
    }

    public function loginUser(string $email): void
    {
        /** @var DoctrineUserRepository $userRepository */
        $userRepository = static::$client->getContainer()->get(DoctrineUserRepository::class);

        /** @var User|UserInterface $user */
        $user = $userRepository->findOneBy(['email' => $email]);

        static::$client->loginUser($user);

        /** @var JwtPayloadManager $jwtPayloadManager */
        $jwtPayloadManager = static::$client->getContainer()->get(JwtPayloadManager::class);
        $token = $jwtPayloadManager->getJwtToken();

        static::$client->setServerParameter('Authorization', 'Bearer ' . $token);
    }

    public function getUser(): User
    {
        $security = $this->getSecurity();
        $user = $security->getUser();

        static::assertInstanceOf(User::class, $user);

        return $user;
    }

    public function makeDefaultUser(): User
    {
        $roleRepository = static::getContainer()->get(DoctrineRoleRepository::class);
        $roleAdmin = $roleRepository->findOneBy(['code' => RoleCodeEnum::ROLE_ADMIN->value]);

        $user = UserFactory::makeVerifiedUserWithRole(
            email: self::DEFAULT_USER_EMAIL,
            username: 'Test',
            role: $roleAdmin,
            plainPassword: 'test1234',
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
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
            plainPassword: $password,
        );

        if (false === $enabled) {
            $user->setEnabled(false);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
