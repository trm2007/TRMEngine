<?php

namespace TRMEngine\PipeLine\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ��� ���������� ������ ������������� � ������,
 * ���� ��������� ������ ������ �������� �� Response
 */
class TRMMiddlewareBadResponseException extends TRMException
{
    /**
     * {@inheritDoc}
     */
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ��������� ���������� ������ �������� �� Response " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMMiddlewareBadResponseException
