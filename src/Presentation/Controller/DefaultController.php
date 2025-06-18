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
        return $this->render('@app/index.html.twig');
    }
}
