<?php

namespace TRMEngine\Exceptions;

/**
 * выбрасывается, если ошибка при работе с массивом конфигурации
 */
class TRMConfigArrayException extends TRMException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с массивом настроек! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
