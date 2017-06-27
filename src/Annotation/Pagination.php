<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Annotation;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class Pagination
{
    public $start = 0;
    public $limit = 25;
    public $maxLimit = 100;

    /**
     * Sets start.
     *
     * @param int $start The start value to set
     */
    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    /**
     * Sets limit.
     *
     * @param int $limit The limit value to set
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Sets max limit.
     *
     * @param int $maxLimit The max limit value to set
     */
    public function setMaxLimit(int $maxLimit): void
    {
        $this->maxLimit = $maxLimit;
    }
}
