<?php

namespace TRMEngine\Cookies\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ������ ������������� ��� ������ ����������� ����� Cookie
 */
class TRMAuthCookieException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ �����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
