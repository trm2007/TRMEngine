<?php

namespace TRMEngine\DiContainer\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ƒолжно выбрасыватьс€ в контейнере,
 * если в приложении не найден запращиваемый класс
 */
class TRMDiClassNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  ласс не найден, что бы создать объект в DI-контейнере! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiClassNotFoundedException


/**
 * ƒолжно выбрасыватьс€ в контейнере,
 * если у объекта Ќ≈ публичный конструктор и нет других доступных методов дл€ создани€
 */
class TRMDiNotPublicConstructorException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ” класса нет публичного конструктора, другими методами создать его не получилось!! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiNotPublicConstructorException


/**
 * ƒолжно выбрасыватьс€ в контейнере,
 * если объект не удалось создать какими-либо способами
 */
class TRMDiCanNotCreateObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "” класса нет конструктора, другими методами и оператором new создать его не получилось! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException


/**
 * ƒолжно выбрасыватьс€ в контейнере,
 * если в метод объекта (__constructor, hasInstance, или другой дл€ создани€)
 * должны переаватьс€ какие-то аргументы, но нет значений по умолчанию
 */
class TRMDiNoDefaultArgsException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL ." ¬ приложении не найден класс и значение по умолчанию дл€ аргумента в методе объекта! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException

