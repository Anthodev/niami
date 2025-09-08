<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Repository\Game;

use App\Domain\Model\Game\ApiGame;
use App\Infrastructure\Client\IgdbClient;
use App\Infrastructure\Repository\Game\IgdbApiRepository;
use App\Shared\Dto\Game\GameCompanyDataDto;
use Faker\Factory;
use Faker\Generator;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->igdbClient = $this->createMock(IgdbClient::class);
    $this->repository = new IgdbApiRepository($this->igdbClient);

    $this->publisherName = $this->faker->company();
    $this->publisherWebsite = $this->faker->url();
    $this->publisherApiId = $this->faker->randomNumber(5);

    $this->publisherDto = new GameCompanyDataDto(
        $this->publisherName,
        $this->publisherWebsite,
        $this->publisherApiId,
    );
});

it('delegates searchGames to IgdbClient', function () {
    // Given
    $query = 'zelda';
    $limit = 15;
    $expectedGames = [createTestApiGame($this->faker, $this->publisherDto)];

    $this->igdbClient
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, $limit)
        ->willReturn($expectedGames);

    // When
    $result = $this->repository->searchGames($query, $limit);

    // Then
    expect($result)->toBe($expectedGames);
});

it('delegates searchGames with default limit', function () {
    // Given
    $query = 'mario';
    $expectedGames = [];

    $this->igdbClient
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willReturn($expectedGames);

    // When
    $result = $this->repository->searchGames($query);

    // Then
    expect($result)->toBe($expectedGames);
});

it('delegates getGameBySlug to IgdbClient', function () {
    // Given
    $slug = 'test-game-slug';
    $expectedGame = createTestApiGame($this->faker, $this->publisherDto);

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willReturn($expectedGame);

    // When
    $result = $this->repository->getGameBySlug($slug);

    // Then
    expect($result)->toBe($expectedGame);
});

it('returns null when game not found by slug', function () {
    // Given
    $slug = 'nonexistent-slug';

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willReturn(null);

    // When
    $result = $this->repository->getGameBySlug($slug);

    // Then
    expect($result)->toBeNull();
});

it('gets cover for game using IgdbClient', function () {
    // Given
    $slug = 'game-with-cover';
    $coverUrl = 'https://images.igdb.com/igdb/image/upload/t_thumb/cover123.jpg';
    $game = createTestApiGame($this->faker, $this->publisherDto, imageCover: $coverUrl);

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willReturn($game);

    // When
    $result = $this->repository->getCoverForGame($slug);

    // Then
    expect($result)->toBe($coverUrl);
});

it('returns empty string when game has no cover', function () {
    // Given
    $slug = 'game-no-cover';
    $game = createTestApiGame($this->faker, $this->publisherDto, imageCover: '');

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willReturn($game);

    // When
    $result = $this->repository->getCoverForGame($slug);

    // Then
    expect($result)->toBe('');
});

it('returns empty string when game not found for cover', function () {
    // Given
    $slug = 'nonexistent-game';

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willReturn(null);

    // When
    $result = $this->repository->getCoverForGame($slug);

    // Then
    expect($result)->toBeNull();
});

it('handles exceptions from IgdbClient searchGames', function () {
    // Given
    $query = 'error-game';
    $exception = new \Exception('API Error');

    $this->igdbClient
        ->expects($this->once())
        ->method('searchGames')
        ->with($query, 10)
        ->willThrowException($exception);

    // When & Then
    expect(fn() => $this->repository->searchGames($query))
        ->toThrow(\Exception::class, 'API Error');
});

it('handles exceptions from IgdbClient getGameBySlug', function () {
    // Given
    $slug = 'error-slug';
    $exception = new \Exception('API Error');

    $this->igdbClient
        ->expects($this->once())
        ->method('getGameBySlug')
        ->with($slug)
        ->willThrowException($exception);

    // When & Then
    expect(fn() => $this->repository->getGameBySlug($slug))
        ->toThrow(\Exception::class, 'API Error');
});

function createTestApiGame(
    Generator $faker,
    GameCompanyDataDto $publisherDto,
    ?string $name = null,
    ?string $slug = null,
    ?string $description = null,
    ?string $imageCover = null,
    ?string $releaseDate = null
): ApiGame {
    return new ApiGame(
        name: $name ?? $faker->words(3, true),
        slug: $slug ?? $faker->slug(),
        description: $description ?? $faker->paragraph(),
        imageCover: $imageCover ?? 'https://images.igdb.com/igdb/image/upload/t_thumb/' . $faker->sha1() . '.jpg',
        releaseDate: $releaseDate ?? $faker->dateTime()->format(DATE_ATOM),
        updatedAt: new \DateTimeImmutable('now'),
        publisher: $publisherDto,
    );
}
