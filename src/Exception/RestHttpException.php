<?php

namespace Jarvis\Skill\Rest\Exception;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class RestHttpException extends \Exception
{
    public function __construct($msg = '', $statusCode = 500, \Exception $previous = null)
    {
        parent::__construct(empty($msg) ? $msg : json_encode(['reason' => $msg]), $statusCode, $previous);
    }
}
