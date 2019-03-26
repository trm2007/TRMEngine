<?php

namespace TRMEngine\EMail\Exceptions;


/**
 * общий класс исключений выбрасываемых объетами TRMEmail
 */
class TRMEMailExceptions extends \TRMEngine\Exceptions\TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с объектом почты! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться при ошбке отправки
 */
class TRMEMailSendingExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка отправки почты! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться если неверно указан получатель письма, или вовсе отсутсвует
 */
class TRMEMailWrongRecepientExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Неверно указан или отсутсвует получатель! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться если неверно указана тема письма, или вовсе отсутсвует
 */
class TRMEMailWrongThemeExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Неверно указана или отсутсвует тема письма! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться если не заполнено или недопустимое само содержимое письма
 */
class TRMEMailWrongBodyExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не заполнено или недопустимое содержимое письма! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
