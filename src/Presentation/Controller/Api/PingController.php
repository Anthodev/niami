<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Api;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/api')]
class PingController extends AbstractController
{
    #[Route('/token', name: 'api_get_token', methods: [Request::METHOD_GET])]
    public function getToken(UserInterface $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        return new JsonResponse(['token' => $jwtManager->create($user)]);
    }

    #[Route(path: '/ping', name: 'ping', methods: [Request::METHOD_GET])]
    public function unauthenticated(): Response
    {
        return new JsonResponse('pong', Response::HTTP_OK);
    }

    #[Route(path: '/ping_auth', name: 'ping_authenticated', methods: [Request::METHOD_GET])]
    public function authenticated(): Response
    {
        return new JsonResponse('pong', Response::HTTP_OK);
    }
}
