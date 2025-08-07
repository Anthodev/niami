<?php

declare(strict_types=1);

namespace App\Application\Service\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Service\Game\GameApiServiceInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class ApiGameSearchService implements GameApiServiceInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @return array{games: array<ApiGame|Game>, total: int}
     *
     * @throws ExceptionInterface
     */
    public function searchGames(string $query = '', int $limit = 10): array
    {
        $envelope = $this->messageBus->dispatch(new SearchGamesQuery($query, $limit));
        $games = $this->retrieveGamesFromEnvelope($envelope);

        if (empty($games)) {
            return [
                'games' => [],
                'total' => 0,
            ];
        }

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
     * @return ApiGame[]
     */
    private function retrieveGamesFromEnvelope(Envelope $envelope): array
    {
        $lastEnvelope = $envelope->last(HandledStamp::class);

        if (null === $lastEnvelope) {
            return [];
        }

        /** @var ApiGame[] */
        return $lastEnvelope->getResult();
    }

    /**
     * @param array<int, ApiGame|Game> $games
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

        return [
            'games' => $games,
            'total' => $total,
        ];
    }
}
