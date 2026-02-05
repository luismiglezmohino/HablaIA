<?php

declare(strict_types=1);

use App\Infrastructure\Http\EventSubscriber\SecurityHeadersSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

function createResponseEvent(): ResponseEvent
{
    $kernel = new class implements HttpKernelInterface {
        public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
        {
            return new Response();
        }
    };

    return new ResponseEvent(
        $kernel,
        new Request(),
        HttpKernelInterface::MAIN_REQUEST,
        new Response()
    );
}

describe('SecurityHeadersSubscriber', function (): void {

    it('implements EventSubscriberInterface', function (): void {
        $subscriber = new SecurityHeadersSubscriber();

        expect($subscriber)->toBeInstanceOf(\Symfony\Component\EventDispatcher\EventSubscriberInterface::class);
    });

    it('subscribes to kernel.response event', function (): void {
        $events = SecurityHeadersSubscriber::getSubscribedEvents();

        expect($events)->toHaveKey(KernelEvents::RESPONSE);
    });

    it('adds X-Content-Type-Options header', function (): void {
        $subscriber = new SecurityHeadersSubscriber();
        $event = createResponseEvent();

        $subscriber->onKernelResponse($event);

        expect($event->getResponse()->headers->get('X-Content-Type-Options'))->toBe('nosniff');
    });

    it('adds X-Frame-Options header', function (): void {
        $subscriber = new SecurityHeadersSubscriber();
        $event = createResponseEvent();

        $subscriber->onKernelResponse($event);

        expect($event->getResponse()->headers->get('X-Frame-Options'))->toBe('DENY');
    });

    it('adds Referrer-Policy header', function (): void {
        $subscriber = new SecurityHeadersSubscriber();
        $event = createResponseEvent();

        $subscriber->onKernelResponse($event);

        expect($event->getResponse()->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
    });

    it('adds X-XSS-Protection header', function (): void {
        $subscriber = new SecurityHeadersSubscriber();
        $event = createResponseEvent();

        $subscriber->onKernelResponse($event);

        expect($event->getResponse()->headers->get('X-XSS-Protection'))->toBe('0');
    });

    it('does not overwrite existing headers', function (): void {
        $subscriber = new SecurityHeadersSubscriber();
        $event = createResponseEvent();
        $event->getResponse()->headers->set('X-Frame-Options', 'SAMEORIGIN');

        $subscriber->onKernelResponse($event);

        expect($event->getResponse()->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN');
    });

});
