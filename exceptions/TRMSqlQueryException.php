<?php

namespace TRMEngine\Exceptions;

/**
 * выбрасывается при ошибке SQL-запроса
 */
class TRMSqlQueryException extends TRMException
{
  public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
  {
    $message .= PHP_EOL . " Ошибка SQL-запроса! " . PHP_EOL;
    parent::__construct($message, $code, $previous);
  }
}
