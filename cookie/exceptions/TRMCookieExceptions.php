<?php

namespace TRMEngine\Cookies\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * должно выбрасываться при ошибке авторизации через Cookie
 */
class TRMAuthCookieException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка авторизации! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
