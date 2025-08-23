<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGameCommand;
use App\Domain\Factory\Game\GameFactory;
use App\Domain\Repository\Game\GameRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGameCommandHandler
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateGameCommand $command): void
    {
        $game = GameFactory::create(
            name: $command->name,
            slug: $command->slug,
            description: $command->description ?? '',
            releaseDate: $command->releaseDate,
            imageCover: $command->imageCover,
        );

        try {
            $this->gameRepository->save($game);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
