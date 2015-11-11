<?php

namespace Jarvis\Skill\Rest;

use Jarvis\Jarvis;
use Jarvis\Skill\DependencyInjection\ContainerProviderInterface;
use Jarvis\Skill\EventBroadcaster\ExceptionEvent;
use Jarvis\Skill\EventBroadcaster\JarvisEvents;
use Jarvis\Skill\Rest\Annotation\PaginationHandler;
use Jarvis\Skill\Rest\Annotation\SortHandler;
use Jarvis\Skill\Rest\Exception\RestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Eric Chau <eric.chau@gmail.com>
 */
class ContainerProvider implements ContainerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate(Jarvis $jarvis)
    {
        $jarvis['annotation.handler.rest.pagination'] = function ($jarvis) {
            return new PaginationHandler($jarvis->request);
        };

        $jarvis['annotation.handler.rest.sort'] = function ($jarvis) {
            return new SortHandler($jarvis->request);
        };

        $jarvis->addReceiver(JarvisEvents::EXCEPTION_EVENT, function (ExceptionEvent $event) {
            $exception = $event->getException();
            if (!($exception instanceof RestHttpException)) {
                return;
            }

            $event->setResponse(new JsonResponse(
                ['reason' => $exception->getMessage()],
                0 === $exception->getCode() ? Response::HTTP_INTERNAL_SERVER_ERROR : $exception->getCode()
            ));
        }, Jarvis::RECEIVER_HIGH_PRIORITY);
    }
}
