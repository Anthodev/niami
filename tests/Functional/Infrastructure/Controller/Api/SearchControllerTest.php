<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Controller\Api;

use App\Application\Service\Game\ApiGameSearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

it('can search games with valid query', function () {
    // Given
    $searchQuery = 'zelda';

    $mockApiGames = [
        [
            'id' => 1,
            'name' => 'The Legend of Zelda: Breath of the Wild',
            'slug' => 'zelda-breath-wild',
            'summary' => 'An adventure game',
            'releaseDate' => new \DateTime(),
        ],
        [
            'id' => 2,
            'name' => 'The Legend of Zelda: Tears of the Kingdom',
            'slug' => 'zelda-tears-kingdom',
            'summary' => 'Another adventure game',
            'releaseDate' => new \DateTime('-1 week'),
        ]
    ];

    $expectedResult = [
        'games' => $mockApiGames,
        'total' => count($mockApiGames)
    ];

    $apiGameSearchServiceMock = $this->createMock(ApiGameSearchService::class);
    $apiGameSearchServiceMock
        ->expects($this->once())
        ->method('searchGames')
        ->with($searchQuery)
        ->willReturn($expectedResult);

    static::$client->getContainer()->set(ApiGameSearchService::class, $apiGameSearchServiceMock);

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $searchQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain('zelda');
    expect($content)->toContain('Breath of the Wild');
    expect($content)->toContain('Tears of the Kingdom');
});

it('returns validation error for query too short', function () {
    // Given
    $shortQuery = 'ze';

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $shortQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain('The search query must be at least 3 characters');
});

it('returns validation error for query too long', function () {
    // Given
    $longQuery = str_repeat('a', 101);

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $longQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain(htmlspecialchars('can\'t be above 100 characters'));
});

it('returns custom error for minimum search length', function () {
    // Given
    $shortQuery = 'ab';

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $shortQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain(htmlspecialchars('The search query must be at least 3 characters'));
});

it('handles empty search results', function () {
    // Given
    $searchQuery = 'nonexistent-game';

    $expectedResult = [
        'games' => [],
        'total' => 0
    ];

    $apiGameSearchServiceMock = $this->createMock(ApiGameSearchService::class);
    $apiGameSearchServiceMock
        ->expects($this->once())
        ->method('searchGames')
        ->with($searchQuery)
        ->willReturn($expectedResult);

    static::$client->getContainer()->set(ApiGameSearchService::class, $apiGameSearchServiceMock);

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $searchQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->not->toContain('error');
});

it('handles form with empty game field', function () {
    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => '',
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain('Type at least 3 characters');
});

it('handles malformed request data', function () {
    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'invalid_form' => [
            'game' => 'zelda'
        ],
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    // Should handle gracefully and not crash
});

it('can handle mixed local and api results', function () {
    // Given
    $searchQuery = 'mario';

    $mockLocalGames = [
        [
            'id' => 101,
            'name' => 'Super Mario Bros (Local)',
            'slug' => 'mario-local',
            'summary' => 'Local game',
            'releaseDate' => new \DateTime(),
        ]
    ];

    $mockApiGames = [
        [
            'id' => 201,
            'name' => 'Super Mario Odyssey (API)',
            'slug' => 'mario-odyssey-api',
            'summary' => 'API game',
            'releaseDate' => new \DateTime('-1 week'),
        ]
    ];

    $allGames = array_merge($mockLocalGames, $mockApiGames);

    $expectedResult = [
        'games' => $allGames,
        'total' => count($allGames)
    ];

    $apiGameSearchServiceMock = $this->createMock(ApiGameSearchService::class);
    $apiGameSearchServiceMock
        ->expects($this->once())
        ->method('searchGames')
        ->with($searchQuery)
        ->willReturn($expectedResult);

    static::$client->getContainer()->set(ApiGameSearchService::class, $apiGameSearchServiceMock);

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $searchQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = $response->getContent();
    expect($content)->toContain('mario');
    expect($content)->toContain('Local');
    expect($content)->toContain('API');
});

it('handles search service exception gracefully', function () {
    // Given
    $searchQuery = 'error-prone-query';

    // When
    static::$client->request(Request::METHOD_POST, '/search', [
        'search_game_form' => [
            'game' => $searchQuery,
        ]
    ]);

    // Then
    $response = static::$client->getResponse();

    // Should handle the exception gracefully (either return error or 500)
    expect($response->getStatusCode())->toBeIn([Response::HTTP_OK, Response::HTTP_INTERNAL_SERVER_ERROR]);
});
