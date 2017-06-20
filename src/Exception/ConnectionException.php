<?php

namespace Caster\Exception;

use Throwable;

class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($code)) {
            $code = self::EXCEPTION_CODE_COMMON_DATABASE_ERROR;
        }
        parent::__construct($message, $code, $previous);
    }
}