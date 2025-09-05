<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\UseCase\Search\GetSearchResultsUseCase;
use App\Presentation\Form\SearchGameForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route(path: '/search', name: 'search_games', methods: [Request::METHOD_POST])]
    public function search(
        Request $request,
        GetSearchResultsUseCase $getSearchResultsUseCase,
    ): Response {
        $searchGamesForm = $this->createForm(SearchGameForm::class);
        $searchGamesForm->handleRequest($request);

        $searchResultOutputDto = $getSearchResultsUseCase->execute($searchGamesForm);

        return $this->render('@turbo/search_results_frame.html.twig', [
            'searchGamesForm' => $searchGamesForm->createView(),
            'games' => $searchResultOutputDto->results['games'] ?? [],
            'searchQuery' => $searchResultOutputDto->searchQuery,
            'hasError' => $searchResultOutputDto->hasError,
            'errorMessage' => $searchResultOutputDto->errorMessage,
        ]);
    }
}
