<?php

namespace TRMEngine\PathFinder\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * Класс исключения, которое должно выбрасываться, 
 * если не найден маршрут
 */
class TRMPathNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 404, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Обращение по неверному адресу! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMPathNotFoundedException

/**
 * Класс исключения, которое должно выбрасываться диспетчером путей, 
 * если не найден контроллер для текущего URI
 */
class TRMControllerNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 404, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не найден модуль (класс контроллера)! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMControllerNotFoundedException


/**
 * Класс исключения, которое должно выбрасываться диспетчером путей,
 * если не найден Action в контроллере для текущего URI
 */
class TRMActionNotFoundedException extends TRMException
{
    public function __construct( $ControllerName, $ActionName, $Param, $Code = 404 )
    {
        parent::__construct( "В модуле [" . $ControllerName . "] не найден метод [" 
                            . $ActionName
                            . "]<br>   для обработки [" 
                            . $Param .  "]<br>",
                $Code );
    }
} // TRMActionNotFoundedException
