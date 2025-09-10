<?php

declare(strict_types=1);

namespace App\Application\Command\Game;

use App\Domain\Model\Game\ApiGame;
use App\Domain\Model\Game\Game;

readonly class UpdateGameFromApiCommand
{
    public function __construct(
        public Game $game,
        public ApiGame $apiGame,
    ) {
    }
}
