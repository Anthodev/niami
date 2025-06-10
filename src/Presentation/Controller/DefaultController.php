<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
class DefaultController extends AbstractController
{
    #[Route(path: '', name: 'homepage')]
    public function index(): Response
    {
        return new Response('Welcome to your new Symfony project!', Response::HTTP_OK);
    }
}
