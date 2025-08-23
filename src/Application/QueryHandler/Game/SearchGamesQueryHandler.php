<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\ApiGameRepositoryInterface;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SearchGamesQueryHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly ApiGameRepositoryInterface $apiGameRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{local: Game[], api: ApiGame[], total: int}
     */
    public function __invoke(SearchGamesQuery $query): array
    {
        if (
            empty($query->query)
            || strlen($query->query) < 2
        ) {
            return [
                'local' => [],
                'api' => [],
                'total' => 0,
            ];
        }

        $localGames = $this->searchLocalGames($query->query, $query->limit);

        $computedApiGames = [];
        if ($query->includeApi) {
            $remainingLimit = $query->limit - count($localGames);

            $apiGames = $this->searchApiGames(
                $query->query,
                $remainingLimit,
            );

            foreach ($apiGames as $apiGame) {
                if (
                    !empty($localGames)
                    && !in_array(
                        $apiGame->getSlug(),
                        array_map(
                            /** @phpstan-ignore-next-line */
                            static fn (Game $game): string => $game->getSlug(),
                            $localGames,
                        ),
                        true)
                ) {
                    $computedApiGames[] = $apiGame;
                } elseif (empty($localGames)) {
                    $computedApiGames = $apiGames;
                }
            }
        }

        return [
            'local' => $localGames,
            'api' => $computedApiGames,
            'total' => count($localGames) + count($computedApiGames),
        ];
    }

    /**
     * @return Game[]
     */
    private function searchLocalGames(string $query, int $limit): array
    {
        try {
            return $this->gameRepository->findGamesByNameOrSlug($query, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche locale de jeux', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return ApiGame[]
     */
    private function searchApiGames(
        string $query,
        int $limit,
    ): array {
        try {
            return $this->apiGameRepository->searchGames($query, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche API de jeux', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
