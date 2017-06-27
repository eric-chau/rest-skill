<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Controller;

use Jarvis\Skill\Helper\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
trait RestControllerTrait
{
    /**
     * Gets resource name to be displayed in response headers "Accept-Range".
     *
     * @return string The resource name
     */
    abstract protected static function getResourceName(): string;

    /**
     * Gets resource max limit. It defines the number of items that consumer can
     * get at once.
     *
     * @return int The max limit
     */
    protected static function getMaxLimit(): int
    {
        return 100;
    }

    /**
     * Transforms ResourceCollection into JsonResponse with expected response headers
     * and appropriated Http status code and returns it.
     *
     * @param  ResourceCollection $collection The collection to transform
     *
     * @return JsonResponse The associated JsonResponse for provided collection
     */
    protected static function getJsonResponseFromCollection(ResourceCollection $collection): JsonResponse
    {
        $count = count($collection);
        $start = $collection->getStart();
        $end = $start + $count - 1;
        $end = $start > $end ? $start : $end;

        return new JsonResponse(
            $collection->getCollection(),
            $count === $collection->getCountMax()
                ? Response::HTTP_OK
                : Response::HTTP_PARTIAL_CONTENT,
            [
                'Accept-Range'  => sprintf('%s %d', strtolower(static::getResourceName()), static::getMaxLimit()),
                'Content-Range' => $count
                    ? sprintf('%s-%s/%s', $start, $end, $collection->getCountMax())
                    : '-/0'
                ,
            ]
        );
    }
}
