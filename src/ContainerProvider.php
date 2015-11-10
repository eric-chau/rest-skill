<?php

namespace Jarvis\Skill\Rest;

use Jarvis\Jarvis;
use Jarvis\Skill\DependencyInjection\ContainerProviderInterface;
use Jarvis\Skill\Rest\Annotation\PaginationHandler;
use Jarvis\Skill\Rest\Annotation\SortHandler;

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
    }
}
