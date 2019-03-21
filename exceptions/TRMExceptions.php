<?php

namespace TRMEngine\Exceptions;

/**
 * общий класс, от которого наследуются все исключения в TRMEngine
 */
class TRMException extends \Exception
{
    /**
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
