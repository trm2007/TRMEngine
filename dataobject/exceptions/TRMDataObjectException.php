<?php

namespace TRMEngine\DataObject\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * Description of TRMDataObjectException
 *
 * @author Sergey
 */
class TRMDataObjectContainerNoMainException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Неверный формат объекта данных! Отсутсвует ключ Main! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
