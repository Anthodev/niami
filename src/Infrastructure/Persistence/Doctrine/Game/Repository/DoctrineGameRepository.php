<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Game\Repository;

use App\Domain\Model\Game\Game;
use App\Domain\Repository\Game\GameRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Common\DoctrineBaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineGameRepository extends DoctrineBaseEntityRepository implements GameRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @return Game[]
     */
    public function getAllEnabledGames(): array
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.isActive = true')
            ->getQuery();

        /** @var Game[] */
        return $qb->getResult();
    }

    /**
     * @return Game[]
     */
    public function findGamesByNameOrSlug(
        string $query,
        int $limit = 10,
    ): array {
        $qb = $this->createQueryBuilder('g')
            ->where('g.name LIKE :query OR g.slug LIKE :query')
            ->andWhere('g.isActive = :active')
            ->setParameter('query', '%'.$query.'%')
            ->setParameter('active', true)
            ->orderBy('g.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery();

        /** @var Game[] */
        return $qb->getResult();
    }

    public function getOneByIdEnabledGame(string $gameId): ?Game
    {
        /** @var Game|null */
        return $this->findOneBy(['id' => $gameId, 'isActive' => true]);
    }
}
