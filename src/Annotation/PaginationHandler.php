<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Rest\Exception\RestHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class PaginationHandler extends AbstractRestHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($annotation): void
    {
        parent::handle($annotation);

        $start = $annotation->start;
        $limit = $annotation->limit;
        $maxLimit = $annotation->maxLimit;
        $range = $this->request->query->get('range', '');
        if (1 === preg_match('#^([0-9]+)-([0-9]+)$#', $range, $matches)) {
            [$range, $start, $limit] = $matches;
            $start = (int) $start;
            $limit = (int) $limit;
            if ($limit < $start) {
                throw new RestHttpException(
                    sprintf('Range start cannot be greater than limit (%s).', $range),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $limit = $limit - $start + 1;
            if ($maxLimit < $limit) {
                throw new RestHttpException(
                    sprintf('Range limit cannot exceed %d (%s).', $maxLimit, $range),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $this->request->attributes->add([
            'start'    => (int) $start,
            'limit'    => (int) $limit,
            'maxLimit' => (int) $maxLimit,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Pagination;
    }
}
