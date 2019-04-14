<?php

namespace TRMEngine\DiContainer\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * Должно выбрасываться в контейнере,
 * если в приложении не найден запращиваемый класс
 */
class TRMDiClassNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Класс не найден, что бы создать объект в DI-контейнере! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiClassNotFoundedException


/**
 * Должно выбрасываться в контейнере,
 * если у объекта НЕ публичный конструктор и нет других доступных методов для создания
 */
class TRMDiNotPublicConstructorException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " У класса нет публичного конструктора, другими методами создать его не получилось!! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiNotPublicConstructorException


/**
 * Должно выбрасываться в контейнере,
 * если объект не удалось создать какими-либо способами
 */
class TRMDiCanNotCreateObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "У класса нет конструктора, другими методами и оператором new создать его не получилось! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException


/**
 * Должно выбрасываться в контейнере,
 * если в метод объекта (__constructor, hasInstance, или другой для создания)
 * должны переаваться какие-то аргументы, но нет значений по умолчанию
 */
class TRMDiNoDefaultArgsException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL ." В приложении не найден класс и значение по умолчанию для аргумента в методе объекта! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException

