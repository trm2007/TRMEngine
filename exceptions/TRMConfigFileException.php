<?php

namespace TRMEngine\Exceptions;

/**
 * выбрасывается, если объект не может быть создан
 */
class TRMConfigFileException extends TRMException
{
  public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
  {
    $message .= PHP_EOL . " Ошибка при работе с файлом конфигурации! " . PHP_EOL;
    parent::__construct($message, $code, $previous);
  }
}
