<?php

declare(strict_types=1);

namespace App\Application\CommandHandler\Game;

use App\Application\Command\Game\CreateDeveloperCommand;
use App\Application\Fetcher\Game\DeveloperFetcher;
use App\Domain\Factory\Game\DeveloperFactory;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateDeveloperCommandHandler
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly DeveloperFetcher $developerFetcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateDeveloperCommand $command): void
    {
        $developer = $this->developerFetcher->findOneByName($command->name);

        if (null !== $developer) {
            return;
        }

        $developer = DeveloperFactory::create(
            name: $command->name,
            apiId: $command->apiId,
            website: $command->website,
        );

        try {
            $this->developerRepository->save($developer);

            /** @var string $developerName */
            $developerName = $developer->getName();
            $this->developerFetcher->deleteCacheName($developerName);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
