<?php

namespace TRMEngine\Exceptions;

/**
 * выбрасывается конструктором, если объект не может быть создан
 */
class TRMObjectCreateException extends TRMException
{
  public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
  {
    $message .= PHP_EOL . " Объект не может быть создан! " . PHP_EOL;
    parent::__construct($message, $code, $previous);
  }
}
