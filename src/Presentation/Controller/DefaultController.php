<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Presentation\Form\SearchGameForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
class DefaultController extends AbstractController
{
    #[Route(path: '', name: 'homepage', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $searchGamesForm = $this->createForm(SearchGameForm::class, null, [
            'action' => $this->generateUrl('search_games'),
            'method' => Request::METHOD_POST,
        ]);

        return $this->render('@app/index.html.twig', [
            'searchGamesForm' => $searchGamesForm->createView(),
        ]);
    }
}
