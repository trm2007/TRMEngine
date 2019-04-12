<?php

namespace TRMEngine\DataObject\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасывается при ошибке в работе с объектами данных TRMDataObject и их наслдениками
 */
class TRMDataObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка объекта данных! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается при ошибке в работе с контейнером объектов данных TRMDataObjectsContainer и их наслдениками
 */
class TRMDataObjectContainerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с контейнером данных! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectContainerNoMainException extends TRMDataObjectContainerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Отсутсвует ключ Main! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsContainerWrongIndexException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка в коллекции объектов данных! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается при ошибке в работе с коллекцией объектов данных TRMDataObjectsCollection и их наслдениками
 */
class TRMDataObjectsCollectionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка в коллекции объектов данных! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsCollectionWrongIndexException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка в коллекции объектов данных! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsCollectionWrongTypeException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Неверный тип объекта! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
