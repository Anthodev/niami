<?php

declare(strict_types=1);

namespace App\Domain\Repository\Game;

use App\Domain\Model\Game\Game;

/**
 * @method ?Game  find(string $id)
 * @method Game[] findAll()
 * @method Game[] findBy(array<string, mixed> $criteria, array<string, mixed> $orderBy = null, $limit = null, $offset = null)
 * @method ?Game  findOneBy(array<string, mixed> $criteria)
 * @method void   save(Game $game)
 * @method void   update(Game $game)
 * @method void   delete(Game $game)
 * @method void   refresh(Game $game)
 * @method void   rollback()
 */
interface GameRepositoryInterface
{
    /**
     * @return Game[]
     */
    public function getAllEnabledGames(): array;

    public function getOneByIdEnabledGame(string $gameId): ?Game;
}
