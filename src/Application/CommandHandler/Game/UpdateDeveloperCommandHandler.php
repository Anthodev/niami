<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\UpdateDeveloperCommand;
use App\Application\Fetcher\Game\DeveloperFetcher;
use App\Domain\Model\Game\Developer;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateDeveloperCommandHandler
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly DeveloperFetcher $developerFetcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdateDeveloperCommand $command): void
    {
        /** @var Developer $developer */
        $developer = $this->developerFetcher->findOneByApiId(
            $command->developerApiId,
            $command->name,
        );

        if (null !== $developer) {
            $developer->setName($command->name);
            $developer->setWebsite($command->website);
        }

        try {
            $this->developerRepository->update($developer);
            $this->developerFetcher->deleteCacheName($command->name);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
