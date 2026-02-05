<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Interceptor que añade cabeceras de seguridad OWASP a todas las respuestas HTTP.
 *
 * Se engancha al evento kernel.response de Symfony, por lo que se aplica
 * automáticamente a todas las respuestas sin modificar los controllers.
 */
final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    // Cabeceras de seguridad recomendadas por OWASP para APIs REST
    private const array SECURITY_HEADERS = [
        // Evita que el navegador intente adivinar el MIME type (previene MIME sniffing)
        'X-Content-Type-Options' => 'nosniff',

        // Impide cargar la API en un iframe (previene clickjacking)
        'X-Frame-Options' => 'DENY',

        // Controla qué información del referrer se envía a otros orígenes
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Desactiva el filtro XSS legacy del navegador (deprecated, pero valor 0 es el safe default)
        'X-XSS-Protection' => '0',

        // Restringe los orígenes de contenido permitidos (API pura: ninguno)
        'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none'",

        // Deshabilita APIs del navegador innecesarias para una API REST
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        foreach (self::SECURITY_HEADERS as $header => $value) {
            // No sobreescribir cabeceras ya definidas por el controller
            if (!$response->headers->has($header)) {
                $response->headers->set($header, $value);
            }
        }
    }
}
