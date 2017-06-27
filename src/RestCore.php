<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest;

use Jarvis\Jarvis;
use Jarvis\Skill\DependencyInjection\ContainerProviderInterface;
use Jarvis\Skill\EventBroadcaster\BroadcasterInterface;
use Jarvis\Skill\EventBroadcaster\ExceptionEvent;
use Jarvis\Skill\EventBroadcaster\RunEvent;
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
        $this
            ->mountRestServices($app)
            ->mountRestEventReceivers($app)
        ;
    }

    protected function mountRestServices(Jarvis $app)
    {
        $app['request_content_json_only'] = false;

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

        return $this;
    }

    protected function mountRestEventReceivers(Jarvis $app)
    {
        $app->on(BroadcasterInterface::RUN_EVENT, function (RunEvent $event) use ($app): void {
            $request = $event->request();
            $contentType = $request->getContentType();
            if (false === $app['request_content_json_only'] && 'json' !== $contentType) {
                return;
            }

            $request->request->replace();
            if (in_array($request->getMethod(), ['GET', 'HEAD']) || 'json' !== $contentType) {
                return;
            }

            $content = json_decode($request->getContent(), true);
            if (JSON_ERROR_NONE !== json_last_error() || !is_array($content)) {
                return;
            }

            $request->request->replace($content);
        });

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
