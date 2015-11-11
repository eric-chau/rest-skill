<?php

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Rest\Exception\RestHttpException;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class PaginationHandler extends AbstractRestHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($annotation)
    {
        parent::handle($annotation);

        $maxLimit = $annotation->maxLimit;
        $limit = $annotation->limit;
        $offset = $annotation->offset;
        $range = $this->req->query->get('range');
        if (1 === preg_match('#^([0-9]+)-([0-9]+)$#', $range, $matches)) {
            list($range, $offset, $limit) = $matches;
            if ($limit < $offset) {
                throw new RestHttpException("Range offset cannot be greater than limit ($range).", 400);
            }

            $limit = $limit - $offset + 1;
            if ($maxLimit < $limit) {
                throw new RestHttpException("Range limit cannot exceed $maxLimit ($range).", 400);
            }
        }

        $this->req->attributes->add([
            'limit'    => (int) $limit,
            'offset'   => (int) $offset,
            'maxLimit' => (int) $maxLimit,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation)
    {
        return $annotation instanceof Pagination;
    }
}
