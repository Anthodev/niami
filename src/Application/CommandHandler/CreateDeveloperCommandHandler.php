<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Command\CreateDeveloperCommand;
use App\Domain\Factory\Game\DeveloperFactory;
use App\Domain\Repository\Game\DeveloperRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateDeveloperCommandHandler
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateDeveloperCommand $command): void
    {
        $developer = $this->developerRepository->findByName($command->name);

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
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
