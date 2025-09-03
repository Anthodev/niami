<?php

declare(strict_types=1);

namespace App\Tests\Functional\Presentation\Controller;

use App\DataFixtures\GameFixtures;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Domain\Repository\Report\ReportRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    /** @var ReportRepositoryInterface */
    $this->reportRepository = static::$client->getContainer()->get(ReportRepositoryInterface::class);
    /** @var GameRepositoryInterface */
    $this->gameRepository = static::$client->getContainer()->get(GameRepositoryInterface::class);
});

it('can increase upvote count successfully', function () {
    // Given
    $game = $this->gameRepository->getOneBySlugEnabledGame(GameFixtures::FIRST_GAME_SLUG);
    $report = $game->getReports()->first();

    // When
    static::$client->request(
        Request::METHOD_PATCH,
        "/reports/{$report->getId()}/increase_upvote"
    );

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent(), true);
    expect($content)->toBe(['message' => 'Upvote count increased']);
});

it('handles turbo stream format correctly', function () {
    // Given
    $game = $this->gameRepository->getOneBySlugEnabledGame(GameFixtures::FIRST_GAME_SLUG);
    $report = $game->getReports()->first();

    // When
    static::$client->request(
        Request::METHOD_PATCH,
        "/reports/{$report->getId()}/increase_upvote",
        [],
        [],
        [
            'HTTP_ACCEPT' => 'text/vnd.turbo-stream.html',
        ]
    );

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())
        ->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toContain('turbo-stream');
});

it('returns 404 for invalid uuid', function () {
    // Given
    $invalidReportId = 'invalid-uuid';

    // When
    static::$client->request(
        Request::METHOD_PATCH,
        "/reports/{$invalidReportId}/increase_upvote"
    );

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);
});

it('returns 405 for wrong http method', function () {
    // Given
    $reportId = '123e4567-e89b-12d3-a456-426614174000';

    // When
    static::$client->request(
        Request::METHOD_GET,
        "/reports/{$reportId}/increase_upvote"
    );

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_METHOD_NOT_ALLOWED);
});
