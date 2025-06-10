<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 200],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $exceptionCode = $exception->getCode();

        if ($exception instanceof HttpExceptionInterface) {
            $exceptionCode = $exception->getStatusCode();
        }

        if ($exceptionCode < 400) {
            $exceptionCode = 500;
        }

        $content = [
            'code' => $exceptionCode,
            'message' => $this->createMessage($exceptionCode),
        ];

        $event->setResponse(
            new JsonResponse($content, $content['code'])
        );
    }

    private function createMessage(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401, 403 => 'Unauthorized',
            404 => 'Not Found',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            default => 'An error occurred',
        };
    }
}
