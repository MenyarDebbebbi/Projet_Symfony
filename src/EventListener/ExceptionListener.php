<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -1],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        // Ne pas intercepter les routes système
        $route = $event->getRequest()->attributes->get('_route', '');
        if (str_starts_with($route, '_')) {
            return;
        }

        // Si c'est une exception HTTP, utiliser nos templates personnalisés
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $template = $this->getTemplateForStatusCode($statusCode);

            if ($template) {
                try {
                    $content = $this->twig->render($template, [
                        'status_code' => $statusCode,
                        'status_text' => Response::$statusTexts[$statusCode] ?? 'Error',
                        'exception' => $exception,
                    ]);

                    $response = new Response($content, $statusCode);
                    $response->headers->set('Content-Type', 'text/html');
                    $event->setResponse($response);
                } catch (\Exception $e) {
                    // Si le rendu échoue, laisser Symfony gérer l'erreur normalement
                }
            }
        }
    }

    private function getTemplateForStatusCode(int $statusCode): ?string
    {
        return match ($statusCode) {
            401 => 'bundles/TwigBundle/Exception/error401.html.twig',
            403 => 'bundles/TwigBundle/Exception/error403.html.twig',
            404 => 'bundles/TwigBundle/Exception/error404.html.twig',
            500 => 'bundles/TwigBundle/Exception/error500.html.twig',
            default => null,
        };
    }
}

