<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Annotation;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class Sort
{
    public $accepted = false;
    public $sort = [];
    public $desc = [];
    public $sanitize = true;
}
