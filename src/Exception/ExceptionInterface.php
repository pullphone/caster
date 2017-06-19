<?php

namespace Caster\Exception;

interface ExceptionInterface
{
    const EXCEPTION_CODE_UNKNOWN = '1001000';
    const EXCEPTION_CODE_FILE_NOT_FOUND = '1001001';
    const EXCEPTION_CODE_INVALID_CONFIGURATION = '1001002';

    const EXCEPTION_CODE_COMMON_DATABASE_ERROR = '1002000';
}