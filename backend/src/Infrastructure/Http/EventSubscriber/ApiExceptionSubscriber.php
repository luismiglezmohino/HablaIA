<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $message = match (true) {
            $status === 400 => 'Bad Request',
            $status === 404 => 'Not Found',
            $status === 405 => 'Method Not Allowed',
            $status === 429 => 'Too Many Requests',
            $status >= 500 => 'Internal Server Error',
            default => 'An error occurred',
        };

        $event->setResponse(new JsonResponse(
            ['error' => $message, 'status' => $status],
            $status,
        ));
    }
}
