<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Annotation\Handler\AbstractHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
abstract class AbstractRestHandler extends AbstractHandler
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Converts snake case to camel case.
     *
     * @param  string $name
     * @return string
     */
    protected function sanitizeFieldName($name)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $name))));
    }
}
