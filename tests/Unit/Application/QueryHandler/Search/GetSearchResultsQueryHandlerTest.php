<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\QueryHandler\Search;

use App\Application\Query\Search\GetSearchResultsQuery;
use App\Application\QueryHandler\Search\GetSearchResultsQueryHandler;
use App\Application\Service\Game\ApiGameSearchService;
use App\Shared\Dto\Game\SearchResultsOutputDto;
use Faker\Factory;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->apiGameSearchService = $this->createMock(ApiGameSearchService::class);
    $this->translator = $this->createMock(TranslatorInterface::class);
    $this->form = $this->createMock(FormInterface::class);
    $this->gameField = $this->createMock(FormInterface::class);

    $this->handler = new GetSearchResultsQueryHandler(
        $this->apiGameSearchService,
        $this->translator
    );

    $this->searchQuery = $this->faker->words(3, true);
    $this->searchResults = [
        'games' => [$this->faker->word(), $this->faker->word()],
        'total' => 2
    ];
});

it('returns default state when form is not submitted', function () {
    // Given
    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(false);

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe('')
        ->and($result->results)->toBe(['games' => [], 'total' => 0])
        ->and($result->hasError)->toBeFalse()
        ->and($result->errorMessage)->toBe('');
});

it('successfully processes valid search query', function () {
    // Given
    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($this->searchQuery);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(true);

    $this->apiGameSearchService
        ->expects($this->once())
        ->method('searchGames')
        ->with($this->searchQuery)
        ->willReturn($this->searchResults);

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe($this->searchQuery)
        ->and($result->results)->toBe($this->searchResults)
        ->and($result->hasError)->toBeFalse()
        ->and($result->errorMessage)->toBe('');
});

it('handles form with validation errors', function () {
    // Given
    $errorMessage = $this->faker->sentence();
    $formError = new FormError($errorMessage);
    $errorIterator = new FormErrorIterator($this->form, [$formError]);

    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($this->searchQuery);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(false);

    $this->form
        ->expects($this->once())
        ->method('getErrors')
        ->with(true)
        ->willReturn($errorIterator);

    $this->apiGameSearchService
        ->expects($this->never())
        ->method('searchGames');

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe($this->searchQuery)
        ->and($result->results)->toBe(['games' => [], 'total' => 0])
        ->and($result->hasError)->toBeTrue()
        ->and($result->errorMessage)->toBe($errorMessage);
});

it('handles search query that is too short', function () {
    // Given
    $shortQuery = 'ab'; // Less than MIN_SEARCH_LENGTH (3)
    $translatedMessage = 'Query must be at least 3 characters long';

    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($shortQuery);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(true);

    $this->translator
        ->expects($this->once())
        ->method('trans')
        ->with('form.errorMessage', ['minLength' => 3], 'search')
        ->willReturn($translatedMessage);

    $this->apiGameSearchService
        ->expects($this->never())
        ->method('searchGames');

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe($shortQuery)
        ->and($result->results)->toBe(['games' => [], 'total' => 0])
        ->and($result->hasError)->toBeTrue()
        ->and($result->errorMessage)->toBe($translatedMessage);
});

it('handles exception from ApiGameSearchService', function () {
    // Given
    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($this->searchQuery);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(true);

    $exception = new \Exception('API service failed');

    $this->apiGameSearchService
        ->expects($this->once())
        ->method('searchGames')
        ->with($this->searchQuery)
        ->willThrowException($exception);

    $query = new GetSearchResultsQuery($this->form);

    // When & Then
    expect(function () use ($query) {
        $this->handler->__invoke($query);
    })->toThrow(\Exception::class);
});

it('handles non-string game data from form', function () {
    // Given
    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn(null);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(true);

    $translatedMessage = 'Query must be at least 3 characters long';

    $this->translator
        ->expects($this->once())
        ->method('trans')
        ->with('form.errorMessage', ['minLength' => 3], 'search')
        ->willReturn($translatedMessage);

    $this->apiGameSearchService
        ->expects($this->never())
        ->method('searchGames');

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe('')
        ->and($result->results)->toBe(['games' => [], 'total' => 0])
        ->and($result->hasError)->toBeTrue()
        ->and($result->errorMessage)->toBe($translatedMessage);
});

it('trims whitespace from search query', function () {
    // Given
    $queryWithWhitespace = '  ' . $this->searchQuery . '  ';

    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($queryWithWhitespace);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(true);

    $this->apiGameSearchService
        ->expects($this->once())
        ->method('searchGames')
        ->with($this->searchQuery)
        ->willReturn($this->searchResults);

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe($this->searchQuery)
        ->and($result->results)->toBe($this->searchResults)
        ->and($result->hasError)->toBeFalse()
        ->and($result->errorMessage)->toBe('');
});

it('handles multiple form validation errors', function () {
    // Given
    $firstError = new FormError('First error');
    $secondError = new FormError('Second error');
    $errorIterator = new FormErrorIterator($this->form, [$firstError, $secondError]);

    $this->form
        ->expects($this->once())
        ->method('isSubmitted')
        ->willReturn(true);

    $this->form
        ->expects($this->once())
        ->method('get')
        ->with('game')
        ->willReturn($this->gameField);

    $this->gameField
        ->expects($this->once())
        ->method('getData')
        ->willReturn($this->searchQuery);

    $this->form
        ->expects($this->once())
        ->method('isValid')
        ->willReturn(false);

    $this->form
        ->expects($this->once())
        ->method('getErrors')
        ->with(true)
        ->willReturn($errorIterator);

    $this->apiGameSearchService
        ->expects($this->never())
        ->method('searchGames');

    $query = new GetSearchResultsQuery($this->form);

    // When
    $result = $this->handler->__invoke($query);

    // Then
    expect($result)->toBeInstanceOf(SearchResultsOutputDto::class)
        ->and($result->searchQuery)->toBe($this->searchQuery)
        ->and($result->results)->toBe(['games' => [], 'total' => 0])
        ->and($result->hasError)->toBeTrue()
        ->and($result->errorMessage)->toBe('First error Second error');
});
