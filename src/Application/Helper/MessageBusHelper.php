<?php

declare(strict_types=1);

namespace App\Application\Helper;

use App\Application\Exception\ClassAndTypeCannotBeNullTogetherException;
use App\Application\Exception\WrongClassInMessageBusEnvelopeException;
use App\Application\Exception\WrongTypeInMessageBusEnvelopeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessageBusHelper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getContentFromEnvelope(
        Envelope $envelope,
        string $logErrorMessage,
        ?string $class = null,
        ?string $type = null,
    ): mixed {
        if (
            null === $class
            && (null === $type || 'array' !== $type)
        ) {
            $this->logger->error('Get data from envelope failed: class and type cannot be null together');
            throw new ClassAndTypeCannotBeNullTogetherException('Get data from envelope failed: class and type cannot be null together');
        }

        $stamp = $envelope->last(HandledStamp::class);

        if (!$stamp) {
            $this->logger->error($logErrorMessage);

            return null;
        }

        $result = $stamp->getResult();

        if (null === $result) {
            return null;
        }

        if (
            null !== $type
            && gettype($result) !== $type
        ) {
            $this->logger->error($logErrorMessage);
            throw new WrongTypeInMessageBusEnvelopeException(message: sprintf('Expected "%s", returned "%s"', $type, gettype($result)));
        }

        if ('array' === $type) {
            /** @phpstan-ignore-next-line */
            $firstKey = array_key_first($result);

            if (
                !empty($result)
                && null !== $class
                /** @phpstan-ignore-next-line */
                && $result[$firstKey] instanceof $class
            ) {
                return $result;
            }

            if (
                !empty($result)
                && null !== $class
            ) {
                $this->logger->error($logErrorMessage);

                /** @var object $result */
                throw new WrongClassInMessageBusEnvelopeException(message: sprintf('Expected "%s:", returned "%s"', $class.'[]', get_class($result).'[]'));
            }
        }

        if (
            null !== $class
            && !$result instanceof $class
        ) {
            $this->logger->error($logErrorMessage);

            /** @var object $result */
            throw new WrongClassInMessageBusEnvelopeException(message: sprintf('Expected "%s:", returned "%s"', $class, get_class($result)));
        }

        return $result;
    }
}
