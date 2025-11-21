<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AccessDeniedExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Intercepter AccessDeniedException et AccessDeniedHttpException
        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            // Ne pas intercepter les routes du profiler
            $route = $event->getRequest()->attributes->get('_route', '');
            if (str_starts_with($route, '_')) {
                return;
            }

            try {
                $content = $this->twig->render('bundles/TwigBundle/Exception/error403.html.twig', [
                    'status_code' => 403,
                    'status_text' => 'Forbidden',
                    'exception' => $exception,
                ]);

                $response = new Response($content, 403);
                $response->headers->set('Content-Type', 'text/html');
                $event->setResponse($response);
                $event->stopPropagation();
            } catch (\Exception $e) {
                // Si le rendu échoue, laisser Symfony gérer l'erreur normalement
            }
        }
    }
}
