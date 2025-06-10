<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Authenticator;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    /**
     * @param UserProviderInterface<UserInterface> $userProvider
     */
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserProviderInterface $userProvider,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $authorizationHeader = $request->headers->get('Authorization');

        return $authorizationHeader && str_starts_with($authorizationHeader, 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        /** @var string $authorizationHeader */
        $authorizationHeader = $request->headers->get('Authorization');

        $jwt = substr($authorizationHeader, 7);

        $payload = $this->jwtManager->parse($jwt);
        $userIdentifier = $payload['username'];

        $userBadge = new UserBadge($userIdentifier, function ($userIdentifier) {
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        });

        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }
}
