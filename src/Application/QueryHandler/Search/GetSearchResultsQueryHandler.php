<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Search;

use App\Application\Query\Search\GetSearchResultsQuery;
use App\Application\Service\Game\ApiGameSearchService;
use App\Shared\Dto\Game\SearchResultsOutputDto;
use App\Shared\Enum\SearchParameterEnum;
use Symfony\Component\Form\FormError;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class GetSearchResultsQueryHandler
{
    public function __construct(
        private readonly ApiGameSearchService $apiGameSearchService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(GetSearchResultsQuery $query): SearchResultsOutputDto
    {
        $results = [
            'games' => [],
            'total' => 0,
        ];
        $searchQuery = '';
        $hasError = false;
        $errorMessage = '';

        if ($query->form->isSubmitted()) {
            $gameData = $query->form->get('game')->getData();
            $searchQuery = is_string($gameData) ? trim($gameData) : '';

            if (!$query->form->isValid()) {
                $hasError = true;
                $errors = [];

                /**
                 * @var FormError $error
                 */
                foreach ($query->form->getErrors(true) as $error) {
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
