<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\Game\ApiGameSearchService;
use App\Presentation\Form\SearchGameForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
class DefaultController extends AbstractController
{
    private const int MIN_SEARCH_LENGTH = 3;

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
            } elseif (strlen($searchQuery) < self::MIN_SEARCH_LENGTH) {
                $hasError = true;
                $errorMessage = sprintf('Veuillez saisir au moins %d caractères.', self::MIN_SEARCH_LENGTH);
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
