<?php

namespace TRMEngine\PipeLine\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * это исключение должно выбрасываться в случае,
 * если посредник вернет объект отличный от Response
 */
class TRMMiddlewareBadResponseException extends TRMException
{
    /**
     * {@inheritDoc}
     */
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Посредник возвращает объект отличный от Response " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMMiddlewareBadResponseException
