<?php

namespace TRMEngine\Exceptions;

/**
 * общий класс, от которого наследуются все исключения в TRMEngine
 */
class TRMException extends \Exception
{
    /**
     * 
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при выполнении в ядре TRMEngine! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMException


/**
 * выбрасывается конструктором, если объект не может быть создан
 */
class TRMObjectCreateException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Объект не может быть создан! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMObjectCreateException


/**
 * выбрасывается если не указан Controller
 */
class TRMNoControllerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Имя контроллера не указано! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoControllerException


/**
 * выбрасывается, если не указан Action
 */
class TRMNoActionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не указано имя Action! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoActionException


/**
 * выбрасывается при ошибке SQL-запроса
 */
class TRMSqlQueryException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка SQL-запроса! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

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
 * Класс исключения, которое должно выбрасываться, 
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
 * Класс исключения, которое должно выбрасываться,
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


/**
 * исключение выбрасывается, как правило кнтроллером, если должен быть запущен другой Action
 */
class TRMMustStartOtherActionException extends TRMException
{
    /**
     * @var string - имя функции-Action, которая должна быть запушена
     */
    protected $ActionName = "";

    public function __construct( $ActionName )
    {
        parent::__construct();
        $this->ActionName = $ActionName;
    }
    
    public function getActionName()
    {
        return $this->ActionName;
    }
} // TRMMustStartOtherActionException