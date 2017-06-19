<?php

namespace Caster\Exception;

use Throwable;

class QueryException extends \RuntimeException implements ExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($code)) {
            $code = self::EXCEPTION_CODE_UNKNOWN;
        }
        parent::__construct($message, $code, $previous);
    }
}