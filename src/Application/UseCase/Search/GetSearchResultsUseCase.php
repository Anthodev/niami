<?php

declare(strict_types=1);

namespace App\Application\UseCase\Search;

use App\Application\Service\Game\ApiGameSearchService;
use App\Shared\Dto\Game\SearchResultsOutputDto;
use App\Shared\Enum\SearchParameterEnum;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetSearchResultsUseCase
{
    public function __construct(
        private readonly ApiGameSearchService $apiGameSearchService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function execute(FormInterface $searchGamesForm): SearchResultsOutputDto
    {
        $results = [
            'games' => [],
            'total' => 0,
        ];
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
                foreach ($searchGamesForm->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }

                $errorMessage = implode(' ', $errors);
            } elseif (strlen($searchQuery) < SearchParameterEnum::MIN_SEARCH_LENGTH->value) {
                $hasError = true;
                $errorMessage = $this->translator->trans('form.errorMessage', ['minLength' => SearchParameterEnum::MIN_SEARCH_LENGTH->value], 'search');
            } else {
                $results = $this->apiGameSearchService->searchGames($searchQuery);
            }
        }

        return new SearchResultsOutputDto(
            searchQuery: $searchQuery,
            results: $results,
            hasError: $hasError,
            errorMessage: $errorMessage,
        );
    }
}
