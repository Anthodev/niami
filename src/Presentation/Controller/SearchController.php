<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\Game\ApiGameSearchService;
use App\Presentation\Form\SearchGameForm;
use App\Shared\Enum\SearchParameterEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route(path: '/search', name: 'search_games', methods: [Request::METHOD_POST])]
    public function search(
        Request $request,
        ApiGameSearchService $apiGameSearchService,
    ): Response {
        $searchGamesForm = $this->createForm(SearchGameForm::class);
        $searchGamesForm->handleRequest($request);

        $results = [];
        $searchQuery = '';
        $hasError = false;
        $errorMessage = '';

        if ($searchGamesForm->isSubmitted()) {
            $gameData = $searchGamesForm->get('game')->getData();
            $searchQuery = is_string($gameData) ? trim($gameData) : '';

            if (!$searchGamesForm->isValid()) {
                $hasError = true;
                $errors = [];

                /**
                 * @var FormError $error
                 */
                foreach ($searchGamesForm->getErrors(true, true) as $error) {
                    $errors[] = $error->getMessage();
                }

                $errorMessage = implode(' ', $errors);
            } elseif (strlen($searchQuery) < SearchParameterEnum::MIN_SEARCH_LENGTH->value) {
                $hasError = true;
                $errorMessage = sprintf('Type at least %d characters...', SearchParameterEnum::MIN_SEARCH_LENGTH->value);
            } else {
                $results = $apiGameSearchService->searchGames($searchQuery);
            }
        }

        return $this->render('@turbo/search_results_frame.html.twig', [
            'searchGamesForm' => $searchGamesForm->createView(),
            'games' => $results['games'] ?? [],
            'searchQuery' => $searchQuery,
            'hasError' => $hasError,
            'errorMessage' => $errorMessage,
        ]);
    }
}
