<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Game;

use App\Application\Query\Game\SearchGamesQuery;
use App\Application\UseCase\Game\UpdateGameFromApiUseCase;
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
        private readonly UpdateGameFromApiUseCase $updateGameFromApiUseCase,
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

            $localGamesBySlug = [];
            foreach ($localGames as $localGame) {
                $slug = $localGame->getSlug();
                if (null !== $slug) {
                    $localGamesBySlug[$slug] = $localGame;
                }
            }

            foreach ($apiGames as $apiGame) {
                $apiSlug = $apiGame->getSlug();

                if (isset($localGamesBySlug[$apiSlug])) {
                    $localGame = $localGamesBySlug[$apiSlug];

                    if ($localGame->getUpdatedAt() < $apiGame->getUpdatedAt()) {
                        $this->updateGameFromApiUseCase->execute(
                            game: $localGame,
                            apiGame: $apiGame,
                        );
                    }
                }

                if (
                    !empty($localGamesBySlug)
                    && !isset($localGamesBySlug[$apiSlug])
                ) {
                    $computedApiGames[] = $apiGame;
                } elseif (empty($localGamesBySlug)) {
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
