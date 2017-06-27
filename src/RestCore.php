<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest;

use Jarvis\Jarvis;
use Jarvis\Skill\DependencyInjection\ContainerProviderInterface;
use Jarvis\Skill\EventBroadcaster\BroadcasterInterface;
use Jarvis\Skill\EventBroadcaster\ExceptionEvent;
use Jarvis\Skill\Rest\Annotation\CriteriaHandler;
use Jarvis\Skill\Rest\Annotation\PaginationHandler;
use Jarvis\Skill\Rest\Annotation\SortHandler;
use Jarvis\Skill\Rest\Exception\RestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Eric Chau <eric.chau@gmail.com>
 */
class RestCore implements ContainerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate(Jarvis $app)
    {
        $request = $app['request'];
        $app['annotation.handler.rest.pagination'] = function () use ($request): PaginationHandler {
            return new PaginationHandler($request);
        };

        $app['annotation.handler.rest.sort'] = function () use ($request): SortHandler {
            return new SortHandler($request);
        };

        $app['annotation.handler.rest.criteria'] = function () use ($request): CriteriaHandler {
            return new CriteriaHandler($request);
        };

        $app->on(BroadcasterInterface::EXCEPTION_EVENT, function (ExceptionEvent $event) {
            $exception = $event->exception();
            if (!($exception instanceof RestHttpException)) {
                return;
            }

            $event->setResponse(new JsonResponse(
                ['reason' => $exception->getMessage()],
                0 === $exception->getCode() ? Response::HTTP_INTERNAL_SERVER_ERROR : $exception->getCode()
            ));
        }, BroadcasterInterface::RECEIVER_HIGH_PRIORITY);
    }
}
