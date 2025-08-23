<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Helper;

use App\Application\Exception\ClassAndTypeCannotBeNullTogetherException;
use App\Application\Exception\WrongClassInMessageBusEnvelopeException;
use App\Application\Exception\WrongTypeInMessageBusEnvelopeException;
use App\Application\Helper\MessageBusHelper;
use App\Domain\Model\Game\ApiGame;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->logger = $this->createMock(LoggerInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
});

describe('MessageBusHelper', function () {
    describe('getContentFromEnvelope', function () {
        it('throws exception when class and type are both null', function () {
            // Given
            $envelope = new Envelope(new stdClass());
            $logErrorMessage = 'Get data from envelope failed: class and type cannot be null together';
            $exception = new ClassAndTypeCannotBeNullTogetherException($logErrorMessage);

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                )
                ->willThrowException($exception);

            // When
            $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage);
        })->throws(ClassAndTypeCannotBeNullTogetherException::class);

        it('throws exception when class is null and type is not array', function () {
            // Given
            $envelope = new Envelope(new stdClass());
            $logErrorMessage = 'Get data from envelope failed: class and type cannot be null together';
            $exception = new ClassAndTypeCannotBeNullTogetherException($logErrorMessage);

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    null,
                    'string'
                )
                ->willThrowException($exception);

            // When & Then
            $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, null, 'string');
        })->throws(ClassAndTypeCannotBeNullTogetherException::class);

        it('allows class to be null when type is array', function () {
            // Given
            $result = ['item1', 'item2'];
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    null,
                    'array'
                )
                ->willReturn($result);

            // When
            $actualResult = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, null, 'array');

            // Then
            expect($actualResult)->toBe($result);
        });

        it('returns null when no HandledStamp is found', function () {
            // Given
            $envelope = new Envelope(new stdClass());
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    stdClass::class,
                )
                ->willReturn(null);

            // When
            $result = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, stdClass::class);

            // Then
            expect($result)->toBeNull();
        });

        it('returns null when HandledStamp result is null', function () {
            // Given
            $handledStamp = new HandledStamp(null, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    stdClass::class,
                )
                ->willReturn(null);

            // When
            $result = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, stdClass::class);

            // Then
            expect($result)->toBeNull();
        });

        it('throws exception when type does not match', function () {
            // Given
            $result = 'string_result';
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = 'Expected "int", returned "string"';
            $exception = new WrongTypeInMessageBusEnvelopeException($logErrorMessage);

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    null,
                    'int',
                )
                ->willThrowException($exception);

            // When & Then
            $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, null, 'int');
        })->throws(WrongTypeInMessageBusEnvelopeException::class);

        it('returns array when type is array and contains objects of correct class', function () {
            // Given
            $apiGame = $this->createMock(ApiGame::class);
            $result = [$apiGame];
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                    'array',
                )
                ->willReturn($result);

            // When
            $actualResult = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class, 'array');

            // Then
            expect($actualResult)->toBe($result);
        });

        it('throws exception when array contains objects of wrong class', function () {
            // Given
            $wrongObject = new stdClass();
            $result = [$wrongObject];
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = 'Expected "App\Domain\Model\Game\ApiGame[]:", returned "stdClass[]"';
            $exception = new WrongClassInMessageBusEnvelopeException($logErrorMessage);

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                    'array',
                )
                ->willThrowException($exception);

            // When & Then
            $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class, 'array');
        })->throws(WrongClassInMessageBusEnvelopeException::class);

        it('returns array when array is empty and class is specified', function () {
            // Given
            $result = [];
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                    'array',
                )
                ->willReturn($result);

            // When
            $actualResult = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class, 'array');

            // Then
            expect($actualResult)->toBe($result);
        });

        it('throws exception when class does not match single object', function () {
            // Given
            $wrongObject = new stdClass();
            $handledStamp = new HandledStamp($wrongObject, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = 'Expected "App\Domain\Model\Game\ApiGame:", returned "stdClass"';
            $exception = new WrongClassInMessageBusEnvelopeException($logErrorMessage);

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                )
                ->willThrowException($exception);

            // When & Then
            $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class);
        })->throws(WrongClassInMessageBusEnvelopeException::class);

        it('returns object when class matches', function () {
            // Given
            $apiGame = $this->createMock(ApiGame::class);
            $handledStamp = new HandledStamp($apiGame, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                )
                ->willReturn($apiGame);

            // When
            $result = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class);

            // Then
            expect($result)->toBe($apiGame);
        });

        it('returns result when only type is specified and matches', function () {
            // Given
            $result = 'string_result';
            $handledStamp = new HandledStamp($result, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    null,
                    'string',
                )
                ->willReturn($result);

            // When
            $actualResult = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, null, 'string');

            // Then
            expect($actualResult)->toBe($result);
        });

        it('returns result when both class and type match', function () {
            // Given
            $apiGame = $this->createMock(ApiGame::class);
            $handledStamp = new HandledStamp($apiGame, 'handler.name');
            $envelope = new Envelope(new stdClass(), [$handledStamp]);
            $logErrorMessage = '';

            $this->messageBusHelper
                ->expects($this->once())
                ->method('getContentFromEnvelope')
                ->with(
                    $envelope,
                    $logErrorMessage,
                    ApiGame::class,
                    'object',
                )
                ->willReturn($apiGame);

            // When
            $result = $this->messageBusHelper->getContentFromEnvelope($envelope, $logErrorMessage, ApiGame::class, 'object');

            // Then
            expect($result)->toBe($apiGame);
        });
    });
});
