<?php

declare(strict_types=1);

namespace App\Application\Service\Game;

use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Game\SearchGamesQuery;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Service\Game\GameApiServiceInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ApiGameSearchService implements GameApiServiceInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusHelper $messageBusHelper,
    ) {
    }

    /**
     * @return array{games: array<ApiGame|Game>, total: int}
     *
     * @throws ExceptionInterface
     */
    public function searchGames(string $query = '', int $limit = 25): array
    {
        $envelope = $this->messageBus->dispatch(new SearchGamesQuery($query, $limit));
        $games = $this->messageBusHelper->getContentFromEnvelope(
            envelope: $envelope,
            logErrorMessage: 'Error during game search',
            type: 'array',
        );

        if (empty($games)) {
            return [
                'games' => [],
                'total' => 0,
            ];
        }

        /** @var array{local: array<ApiGame|Game>, api: array<ApiGame|Game>, total: int} $games */
        return $this->formatGamesOutput($games);
    }

    public function getCoverForGame(string $slug): string
    {
        return '';
    }

    public function getGameBySlug(string $slug): ?ApiGame
    {
        return null;
    }

    /**
     * @param array{local: array<ApiGame|Game>, api: array<ApiGame|Game>, total: int} $games
     *
     * @return array{games: array<ApiGame|Game>, total: int}
     */
    private function formatGamesOutput(array $games): array
    {
        if (!isset($games['local']) || !isset($games['api'])) {
            return [
                'games' => [],
                'total' => 0,
            ];
        }

        $total = $games['total'];

        $games = array_merge($games['local'], $games['api']);

        $games = $this->orderGamesByDate($games);

        return [
            'games' => $games,
            'total' => $total,
        ];
    }

    /**
     * @param array<int, ApiGame|Game> $games
     *
     * @return array<int, ApiGame|Game>
     */
    private function orderGamesByDate(array $games): array
    {
        usort($games, static function (ApiGame|Game $a, ApiGame|Game $b): int {
            return $b->getReleaseDate() <=> $a->getReleaseDate();
        });

        return $games;
    }
}
