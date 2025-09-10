<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Search\GetSearchResultsQuery;
use App\Presentation\Form\SearchGameForm;
use App\Shared\Dto\Game\SearchResultsOutputDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route(path: '/search', name: 'search_games', methods: [Request::METHOD_POST])]
    public function search(
        Request $request,
        MessageBusInterface $messageBus,
        MessageBusHelper $messageBusHelper,
    ): Response {
        $searchGamesForm = $this->createForm(SearchGameForm::class);
        $searchGamesForm->handleRequest($request);

        $searchResultEnvelope = $messageBus->dispatch(new GetSearchResultsQuery($searchGamesForm));
        /** @var SearchResultsOutputDto $searchResultOutputDto */
        $searchResultOutputDto = $messageBusHelper->getContentFromEnvelope(
            envelope: $searchResultEnvelope,
            logErrorMessage: 'Unable to get search results.',
            class: SearchResultsOutputDto::class,
        );

        return $this->render('@turbo/search_results_frame.html.twig', [
            'searchGamesForm' => $searchGamesForm->createView(),
            'games' => $searchResultOutputDto->results['games'] ?? [],
            'searchQuery' => $searchResultOutputDto->searchQuery,
            'hasError' => $searchResultOutputDto->hasError,
            'errorMessage' => $searchResultOutputDto->errorMessage,
        ]);
    }
}
